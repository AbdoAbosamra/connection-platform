<?php

namespace App\Services;

use App\Models\JobSeekerProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProfessionalService
{
    /** Columns the seeker full-text index covers. Must match the migration. */
    private const FULLTEXT_COLUMNS = ['headline', 'bio', 'current_job_title', 'desired_job_title'];

    /**
     * Search publicly-visible professional profiles.
     *
     * Privacy gate: only surfaces profiles where profile_complete = true.
     * This ensures job seekers explicitly "opt in" by finishing their profile.
     *
     * Search: relevance-ranked FULLTEXT on MySQL; LIKE fallback elsewhere. All
     * LIKE terms are wildcard-escaped to prevent pattern injection.
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = JobSeekerProfile::query()
            ->with([
                'user:id,name',
                'skills:id,name,slug,category',
            ])
            ->where('profile_complete', true);

        $term = trim($filters['q'] ?? '');
        $searching = $term !== '';

        if ($searching) {
            $this->applyTextSearch($query, $term);
        }

        $query->orderByDesc('is_featured');
        if ($searching && DB::connection()->getDriverName() === 'mysql') {
            $query->orderByDesc('relevance');
        }
        $query->orderByDesc('updated_at');

        $this->applyBasicFilters($query, $filters);
        $this->applyAdvancedFilters($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Basic filters — always available to employers.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyBasicFilters($query, array $filters): void
    {
        if (!empty($filters['experience_level'])) {
            $query->where('experience_level', $filters['experience_level']);
        }

        if (!empty($filters['availability'])) {
            $query->where('availability', $filters['availability']);
        }

        if (!empty($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        if (!empty($filters['education_level'])) {
            $query->where('education_level', $filters['education_level']);
        }

        // Minimum years of remote experience
        if (isset($filters['remote_experience_min']) && $filters['remote_experience_min'] !== '') {
            $query->where('remote_experience_years', '>=', (int) $filters['remote_experience_min']);
        }

        // Salary — match candidates whose expectation overlaps the employer's budget.
        if (isset($filters['salary_max']) && $filters['salary_max'] !== '') {
            $query->where(fn ($q) => $q
                ->whereNull('desired_salary_min')
                ->orWhere('desired_salary_min', '<=', (int) $filters['salary_max']));
        }
        if (isset($filters['salary_min']) && $filters['salary_min'] !== '') {
            $query->where(fn ($q) => $q
                ->whereNull('desired_salary_max')
                ->orWhere('desired_salary_max', '>=', (int) $filters['salary_min']));
        }

        // Multi-skill filter — accepts comma-separated string or array of IDs
        if (!empty($filters['skills'])) {
            $skillIds = is_array($filters['skills'])
                ? $filters['skills']
                : explode(',', $filters['skills']);

            // Filter to valid integers to prevent SQL injection via array manipulation
            $skillIds = array_filter(array_map('intval', $skillIds));

            if (!empty($skillIds)) {
                $query->whereHas('skills', fn ($q) => $q->whereIn('skills.id', $skillIds));
            }
        }
    }

    /**
     * Advanced international filters — only meaningful for international remote
     * hiring. The employer UI hides these unless that mode is enabled, but we
     * still honour any that arrive.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyAdvancedFilters($query, array $filters): void
    {
        if (!empty($filters['country'])) {
            $escaped = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $filters['country']).'%';
            $query->where('current_country', 'like', $escaped);
        }

        if (!empty($filters['time_zone'])) {
            $query->where('time_zone', $filters['time_zone']);
        }

        // Languages — candidate must speak ALL requested languages.
        // Stored as a JSON array; a LIKE on the raw column keeps this portable
        // across MySQL and the SQLite test database.
        if (!empty($filters['languages'])) {
            $languages = is_array($filters['languages'])
                ? $filters['languages']
                : explode(',', $filters['languages']);

            foreach (array_filter(array_map('trim', $languages)) as $lang) {
                $escaped = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $lang).'%';
                $query->where('languages', 'like', $escaped);
            }
        }

        // Contract type the employer wants → candidates who prefer it or "either".
        if (!empty($filters['contract_type'])) {
            $wanted = $filters['contract_type'] === 'remote_employee' ? 'employee' : 'contractor';
            $query->whereIn('contract_preference', [$wanted, 'either']);
        }

        if (!empty($filters['has_portfolio'])) {
            $query->whereNotNull('portfolio_url')->where('portfolio_url', '!=', '');
        }

        if (!empty($filters['has_certifications'])) {
            $query->whereNotNull('certifications')->where('certifications', '!=', '');
        }

        if (!empty($filters['has_security_clearance'])) {
            $query->where('has_security_clearance', true);
        }
    }

    /**
     * Relevance search: FULLTEXT (boolean mode) on MySQL, LIKE fallback elsewhere.
     */
    private function applyTextSearch($query, string $term): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $columns = implode(',', self::FULLTEXT_COLUMNS);
            $cleaned = preg_replace('/[+\-><()~*"@]+/', ' ', $term);
            $words = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);
            $booleanTerm = collect($words)->map(fn ($w) => '+'.$w.'*')->implode(' ');

            $query->whereFullText(self::FULLTEXT_COLUMNS, $booleanTerm, ['mode' => 'boolean'])
                ->selectRaw("job_seeker_profiles.*, MATCH({$columns}) AGAINST (? IN BOOLEAN MODE) AS relevance", [$booleanTerm]);

            return;
        }

        $escaped = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';
        $query->where(function ($q) use ($escaped) {
            foreach (self::FULLTEXT_COLUMNS as $col) {
                $q->orWhere($col, 'like', $escaped);
            }
        });
    }

    /**
     * Load a single public professional profile with full detail.
     *
     * Aborts with 404 if the profile is incomplete (privacy gate).
     */
    public function getPublicProfile(JobSeekerProfile $profile): JobSeekerProfile
    {
        abort_if(!$profile->profile_complete, 404, 'Profile not publicly available.');

        return $profile->load([
            'user:id,name',
            'skills:id,name,slug,category',
        ]);
    }
}
