# API Reference

Base URL: `/api`. All responses are JSON. Authenticated endpoints require an
`Authorization: Bearer {token}` header (Laravel Sanctum). Suspended accounts are
rejected by the `active` middleware (403). Rate limits are noted where they
deviate from the default 60 req/min.

> Pagination: list endpoints return Laravel's paginator shape
> (`{ data, current_page, last_page, total, … }`).

## Authentication (public)

| Method | Endpoint | Notes |
| --- | --- | --- |
| POST | `/auth/register` | `{name,email,password,password_confirmation,role,company_name?}`; role ∈ employer\|job_seeker. Returns `{user,token}`. Throttle 10/min. |
| POST | `/auth/login` | `{email,password}` → `{user,token}`. Rotates tokens. Throttle 10/min. |
| POST | `/auth/forgot-password` | `{email}` → generic 200 (no user enumeration). Throttle 10/min. |
| POST | `/auth/reset-password` | `{token,email,password,password_confirmation}`. Revokes tokens. Throttle 10/min. |
| GET  | `/auth/me` | Current user with profile + skills. *(auth)* |
| POST | `/auth/logout` | Revokes current token. *(auth)* |

## Public discovery

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/jobs` | Active jobs, relevance-ranked full-text `q` search. Filters: `category, experience_level, employment_type, salary_min, salary_max, skills`. Response includes `facets` counts (see [SEARCH.md](SEARCH.md)). |
| GET | `/jobs/{slug}` | Job detail (increments view count). |
| GET | `/professionals` | Completed job-seeker profiles. Filters: `q, experience_level, availability, skills, per_page`. |
| GET | `/professionals/{profile}` | Public profile (404 if incomplete). |
| GET | `/skills` | Skill lookup; `search` is LIKE-wildcard-escaped. |
| GET | `/subscription-plans` | Active pricing tiers. |
| POST | `/billing/webhook` | Stripe webhook (verified by signing secret). |

## Shared (any authenticated role)

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/notifications` | Paginated in-app feed. Throttle 120/min. |
| GET | `/notifications/unread-count` | `{unread}`. Throttle 120/min. |
| POST | `/notifications/{id}/read` | Mark one read. |
| POST | `/notifications/read-all` | Mark all read. |
| DELETE | `/notifications/{id}` | Delete one. |
| POST | `/reports` | `{type: job\|user, id, reason, details?}`. Blocks self-report & duplicates; notifies admins. Throttle 20/min. |

## Employer (`role:employer`)

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/employer/stats` | Dashboard stats (scoped to this employer). |
| GET/PUT | `/employer/profile` | Show / update company profile (lazy-created). |
| GET | `/employer/jobs` | Own jobs (paginated, with application counts). |
| POST | `/employer/jobs` | Create job — consumes a credit unless on a paid tier. |
| GET/PUT/DELETE | `/employer/jobs/{job}` | Show / update / soft-delete (policy-guarded). |
| PATCH | `/employer/jobs/{job}/toggle-status` | active ⇄ paused. |
| GET | `/employer/applications` | Applications to own jobs. Filters: `job_id, status`. |
| GET | `/employer/applications/{application}` | Detail (marks viewed). |
| PATCH | `/employer/applications/{application}/status` | `{status, notes?}`; notifies seeker. |
| POST | `/employer/applications/{application}/interviews` | Schedule interview; notifies seeker. |
| PATCH/DELETE | `/employer/interviews/{interview}` | Reschedule / cancel. |
| GET | `/employer/verification` | Verification status + available methods. |
| POST | `/employer/verification/payment` | Verify via payment authorization (mock instant / Stripe checkout URL). |
| GET | `/employer/verification/linkedin/redirect` | LinkedIn OAuth URL (503 if not configured). |
| GET/POST/DELETE | `/employer/subscription` | Current / subscribe `{plan_id,billing_period}` / cancel. |

> **Posting gate**: `POST /employer/jobs` requires a verified employer. Corporate-email
> employers are auto-verified at registration; personal-email employers get a 403 with
> `verification_required: true` until they verify via LinkedIn or payment. Disposable-email
> employer registrations are rejected outright.
| GET/POST | `/employer/conversations` (+ `/{id}/messages`, `/unread-count`, `/{id}/read`) | Messaging. Poll endpoints 120/min, send 30/min. |

## Job seeker (`role:job_seeker`)

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/job-seeker/stats` | Dashboard stats. |
| GET/PUT | `/job-seeker/profile` | Show / update profile (+ skills sync). |
| GET | `/job-seeker/profile/resume` | Resume URL (404 if none). |
| GET | `/job-seeker/jobs/recommended` | Skill/level/salary-scored recommendations. |
| GET | `/job-seeker/jobs/saved` | Saved jobs. |
| POST/DELETE | `/job-seeker/jobs/{job}/save` | Save / unsave (idempotent). |
| GET | `/job-seeker/applications` | Own applications. |
| POST | `/job-seeker/jobs/{job}/apply` | `{cover_letter?, resume?, expected_salary?}`. Blocks duplicate/closed. |
| GET | `/job-seeker/applications/{application}` | Detail. |
| PATCH | `/job-seeker/applications/{application}/withdraw` | Withdraw (decrements count). |
| GET | `/job-seeker/interviews` | Upcoming interviews. |
| PATCH | `/job-seeker/interviews/{interview}/confirm` \| `/cancel` | Confirm / decline; notifies employer. |
| messaging | `/job-seeker/conversations…` | Same shape as employer. |

## Admin (`role:admin`)

| Method | Endpoint | Notes |
| --- | --- | --- |
| GET | `/admin/dashboard`, `/admin/dashboard/growth` | Platform stats & user-growth series. |
| GET | `/admin/users`, `/admin/users/{user}` | List (filters: `role, search`) / detail. |
| PATCH | `/admin/users/{user}/toggle-active` | Suspend / reactivate. |
| DELETE | `/admin/users/{user}` | Soft-delete (cannot delete self or other admins). |
| DELETE | `/admin/users/{user}/forget` | GDPR erasure — irreversibly anonymise the user's data (see [GDPR.md](GDPR.md)). |
| GET | `/admin/jobs` | All jobs incl. soft-deleted. |
| PATCH | `/admin/jobs/{job}/feature` | Toggle featured. |
| DELETE | `/admin/jobs/{job}` | Soft-delete. |
| POST | `/admin/jobs/{id}/restore` | Restore soft-deleted job. |
| GET | `/admin/reports` | List flags (filters: `status`, `priority`; ordered critical→low). |
| PATCH | `/admin/reports/{report}/resolve` | `{status: resolved\|dismissed, resolution_note?}` (idempotent). |
| POST | `/admin/reports/{report}/action` | Moderation action `{action, notes?}` — warn/suspend/dismiss/remove/reinstate/note. |
| GET | `/admin/moderation/most-flagged` | Most-flagged leaderboard (`type=job\|user`). |
| GET | `/admin/moderation/logs` | Admin action audit trail (filters: `action`, `user_id`). |

See [MODERATION.md](MODERATION.md) for the schema, the most-flagged query, and auto-escalation.

## Error shape

```json
{ "message": "Validation failed.", "errors": { "field": ["reason"] } }
```

- `401` unauthenticated · `403` wrong role / suspended / policy denial ·
  `404` not found · `422` validation · `429` throttled.
