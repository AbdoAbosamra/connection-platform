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

        if (!empty($filters['experience_level'])) {
            $query->where('experience_level', $filters['experience_level']);
        }

        if (!empty($filters['availability'])) {
            $query->where('availability', $filters['availability']);
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

        return $query->paginate($perPage)->withQueryString();
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
