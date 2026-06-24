<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Services\ProfessionalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function __construct(private readonly ProfessionalService $professionals) {}

    /**
     * GET /professionals
     *
     * Public paginated listing of completed job-seeker profiles.
     * Only exposes profiles where profile_complete = true.
     *
     * Accepted query params:
     *   q                – keyword (headline / bio / titles)
     *   experience_level – entry | mid | senior | lead | executive
     *   availability     – immediate | 2_weeks | 1_month
     *   skills           – comma-separated skill IDs, e.g. "3,7,12"
     *   per_page         – optional, max 50
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['q', 'experience_level', 'availability', 'skills']);
        $perPage = min((int) $request->input('per_page', 15), 50);

        $results = $this->professionals->search($filters, $perPage);

        // Transform to strip sensitive / internal fields from the public payload
        $results->getCollection()->transform(fn ($p) => $this->publicShape($p));

        return response()->json($results);
    }

    /**
     * GET /professionals/{profile}
     *
     * Single public profile view.
     * Returns 404 if profile_complete = false (privacy gate in service layer).
     */
    public function show(JobSeekerProfile $profile): JsonResponse
    {
        $profile = $this->professionals->getPublicProfile($profile);

        return response()->json(['professional' => $this->publicShapeDetailed($profile)]);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Shape for the listing card — minimal, performant payload.
     */
    private function publicShape(JobSeekerProfile $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->user->name ?? null,
            'headline' => $p->headline,
            'current_job_title' => $p->current_job_title,
            'experience_level' => $p->experience_level,
            'years_of_experience' => $p->years_of_experience,
            'current_city' => $p->current_city,
            'current_country' => $p->current_country,
            'availability' => $p->availability,
            'is_featured' => $p->is_featured,
            // completionPercentage() called explicitly — 'completion' was removed from
            // $appends to prevent a DB query on every model serialization.
            'completion' => $p->completionPercentage(),
            'skills' => $p->skills->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'category' => $s->category,
            ]),
        ];
    }

    /**
     * Shape for the detail page — full profile, still strips sensitive fields.
     * Excluded: nationality, resume (raw path), user_id, desired_salary_*, willing_to_relocate.
     */
    private function publicShapeDetailed(JobSeekerProfile $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->user->name ?? null,
            'headline' => $p->headline,
            'bio' => $p->bio,
            'current_job_title' => $p->current_job_title,
            'desired_job_title' => $p->desired_job_title,
            'experience_level' => $p->experience_level,
            'years_of_experience' => $p->years_of_experience,
            'current_city' => $p->current_city,
            'current_country' => $p->current_country,
            'availability' => $p->availability,
            'portfolio_url' => $p->portfolio_url,
            'linkedin_url' => $p->linkedin_url,
            'github_url' => $p->github_url,
            'is_featured' => $p->is_featured,
            'completion' => $p->completionPercentage(),
            'skills' => $p->skills->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'category' => $s->category,
                'proficiency' => $s->pivot->proficiency ?? null,
            ]),
        ];
    }
}
