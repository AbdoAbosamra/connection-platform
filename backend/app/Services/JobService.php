<?php

namespace App\Services;

use App\Models\EmployerProfile;
use App\Models\Job;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobService
{
    /**
     * Columns the job full-text index covers. Must match the migration exactly.
     */
    private const FULLTEXT_COLUMNS = ['title', 'description', 'requirements'];

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Job::active()
            ->with(['employer:id,company_name,company_slug,logo,headquarters_city,headquarters_state', 'skills:id,name,slug']);

        $term = trim($filters['q'] ?? '');
        $searching = $term !== '';

        if ($searching) {
            $this->applyTextFilter($query, $term);

            // On MySQL, also compute a relevance score and rank by it. (Kept out
            // of the facet query, which aggregates and would break under
            // ONLY_FULL_GROUP_BY if non-grouped columns were selected.)
            if ($this->usesFullText()) {
                $columns = implode(',', self::FULLTEXT_COLUMNS);
                $query->selectRaw("jobs.*, MATCH({$columns}) AGAINST (? IN BOOLEAN MODE) AS relevance", [$this->toBooleanTerm($term)]);
            }
        }

        $this->applyFilters($query, $filters);

        // Featured listings first; then by relevance when searching (MySQL),
        // otherwise newest first.
        $query->orderByDesc('is_featured');
        if ($searching && $this->usesFullText()) {
            $query->orderByDesc('relevance');
        }
        $query->orderByDesc('created_at');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Facet counts for the current search term, ignoring the per-dimension
     * filters so the UI can show the full distribution for the query.
     *
     * @return array<string, array<string, int>>
     */
    public function facets(array $filters): array
    {
        $term = trim($filters['q'] ?? '');

        $countsBy = function (string $column) use ($term): array {
            $q = Job::active();
            if ($term !== '') {
                $this->applyTextFilter($q, $term);
            }

            return $q->reorder()
                ->groupBy($column)
                ->selectRaw("{$column} as value, COUNT(*) as total")
                ->pluck('total', 'value')
                ->map(fn ($n) => (int) $n)
                ->all();
        };

        return [
            'category' => $countsBy('category'),
            'employment_type' => $countsBy('employment_type'),
            'experience_level' => $countsBy('experience_level'),
        ];
    }

    private function usesFullText(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }

    /**
     * Apply the text-match WHERE only (no SELECT changes, so it is safe to use
     * inside aggregate facet queries). FULLTEXT boolean mode on MySQL; LIKE
     * fallback on other drivers (the SQLite test DB).
     */
    private function applyTextFilter($query, string $term): void
    {
        if ($this->usesFullText()) {
            $query->whereFullText(self::FULLTEXT_COLUMNS, $this->toBooleanTerm($term), ['mode' => 'boolean']);

            return;
        }

        $query->where(function ($q) use ($term) {
            $escaped = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';
            foreach (self::FULLTEXT_COLUMNS as $col) {
                $q->orWhere($col, 'like', $escaped);
            }
        });
    }

    /**
     * Turn a free-text query into a safe boolean-mode expression with prefix
     * wildcards, e.g. "senior php" → "+senior* +php*".
     */
    private function toBooleanTerm(string $term): string
    {
        $cleaned = preg_replace('/[+\-><()~*"@]+/', ' ', $term);
        $words = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);

        return collect($words)->map(fn ($w) => '+'.$w.'*')->implode(' ');
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['experience_level'])) {
            $query->where('experience_level', $filters['experience_level']);
        }
        if (!empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }
        if (isset($filters['salary_min'])) {
            $query->where('salary_max', '>=', (int) $filters['salary_min']);
        }
        if (isset($filters['salary_max'])) {
            $query->where('salary_min', '<=', (int) $filters['salary_max']);
        }
        if (!empty($filters['skills'])) {
            $skillIds = is_array($filters['skills']) ? $filters['skills'] : explode(',', $filters['skills']);
            $skillIds = array_filter(array_map('intval', $skillIds));
            if (!empty($skillIds)) {
                $query->whereHas('skills', fn ($q) => $q->whereIn('skills.id', $skillIds));
            }
        }
    }

    public function createJob(EmployerProfile $employer, array $data): Job
    {
        return DB::transaction(function () use ($employer, $data) {
            // Reload inside transaction so the credit check and decrement are atomic
            $lockedEmployer = EmployerProfile::lockForUpdate()->findOrFail($employer->id);

            if (!$lockedEmployer->hasCredits()) {
                throw ValidationException::withMessages([
                    'credits' => ['No job post credits remaining.'],
                ]);
            }

            // Platform is remote-only — enforce this regardless of what the frontend sends.
            $data['location_type'] = 'remote';

            $job = $lockedEmployer->jobs()->create($data);

            if (!empty($data['skills'])) {
                $skillData = collect($data['skills'])->mapWithKeys(fn ($s) => [
                    $s['id'] => ['is_required' => $s['is_required'] ?? true],
                ]);
                $job->skills()->sync($skillData);
            }

            $lockedEmployer->decrementCredits();

            return $job->load('skills');
        });
    }

    public function updateJob(Job $job, array $data): Job
    {
        return DB::transaction(function () use ($job, $data) {
            $data['location_type'] = 'remote'; // platform invariant
            $job->update($data);

            if (array_key_exists('skills', $data)) {
                $skillData = collect($data['skills'])->mapWithKeys(fn ($s) => [
                    $s['id'] => ['is_required' => $s['is_required'] ?? true],
                ]);
                $job->skills()->sync($skillData);
            }

            return $job->fresh('skills');
        });
    }

    public function getJobWithDetails(Job $job): Job
    {
        $job->incrementViews();

        return $job->load([
            'employer:id,company_name,company_slug,logo,description,website,headquarters_city,headquarters_state,headquarters_country,founded_year,company_size',
            'skills:id,name,slug,category',
        ]);
    }
}
