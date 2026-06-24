# Architecture

Connextion / RemoteArena is a remote-only job platform pairing US-based hiring
companies with skilled international job seekers. It is a two-tier application:
a **Laravel 11 REST API** (`backend/`) and a **Vue 3 SPA** (`frontend/`).

## High-level diagram

```
┌──────────────────────────┐        Bearer token (Sanctum)        ┌──────────────────────────┐
│      Vue 3 SPA           │  ───────────────────────────────────▶ │     Laravel 11 API        │
│  (Vite, Pinia, Tailwind) │                                       │  (REST, /api/*)           │
│                          │  ◀───────────────────────────────────│                           │
│  stores/ ── api/ ──────▶ │            JSON responses             │  Controllers → Services   │
└──────────────────────────┘                                       │  → Models → MySQL         │
                                                                    │  Notifications (DB+mail)  │
                                                                    │  Billing (mock / Stripe)  │
                                                                    └──────────────────────────┘
```

## Roles

| Role         | Capabilities                                                            |
| ------------ | ----------------------------------------------------------------------- |
| `admin`      | User/job moderation, reports, analytics, full visibility                |
| `employer`   | Post jobs (credit/subscription gated), manage applications, interviews, messaging, billing |
| `job_seeker` | Browse & apply to jobs, manage profile, saved jobs, interviews, messaging |

## Backend layering

```
routes/api.php
   └── Controllers (Api/, Api/Employer, Api/JobSeeker, Api/Admin)
         └── Form Requests (validation)         ← input boundary
         └── Policies (authorization)           ← JobPolicy, JobApplicationPolicy, ConversationPolicy
         └── Services (business logic)           ← Auth, Job, Application, Interview, Message, Billing
               └── Eloquent Models               ← data + invariants (credits, slugs, completion)
                     └── MySQL
```

Key cross-cutting pieces:

- **Auth**: Laravel Sanctum personal-access tokens (`Authorization: Bearer …`).
  Login rotates tokens; logout / password reset revoke them.
- **Middleware**: `auth:sanctum` → `active` (blocks suspended accounts) →
  `role:*` (role gate) → `throttle:*` (rate limiting). Registered in
  `bootstrap/app.php`.
- **Concurrency safety**: credit spend (`JobService::createJob`), application
  creation and withdrawal (`ApplicationService`) all run inside
  `DB::transaction` with `lockForUpdate()` to prevent TOCTOU races.
- **Notifications**: database + mail channels. In-app feed is served from the
  `notifications` table via `NotificationController`.
- **Billing**: `BillingService` resolves a `PaymentGateway` driver
  (`MockGateway` for local/test, `StripeGateway` for production checkout).
  Falls back to mock automatically when no Stripe secret is configured.

## Frontend layering

```
main.js → router/ (guards: auth.init, role checks)
   └── layouts/ (PublicLayout, AuthLayout, AppLayout)
         └── pages/ (route views)
               └── components/ (JobCard, ApplyModal, NotificationBell, ReportModal, …)
         └── stores/ (Pinia: auth, jobs, messages, notifications, professionals, language)
               └── api/ (axios modules: auth, jobs, applications, messages,
                         notifications, reports, interviews, billing, professionals)
                     └── api/client.js (axios instance + 401 interceptor)
```

- **Polling** drives near-real-time UX for messaging (15 s) and the notification
  badge (30 s) — both well within the per-route throttle budgets.
- **Optimistic updates** are used for sending messages and marking notifications
  read, with reconciliation on the next poll.

## Data model (core tables)

```
users ─1:1─ employer_profiles ─1:n─ jobs ─1:n─ job_applications ─1:n─ interview_schedules
  │                  │                              │
  └─1:1─ job_seeker_profiles ─1:n────────────────────┘ (applicant)
              │
              ├─ n:m ─ skills (job_seeker_skills / job_skills pivots)
              └─1:n─ saved_jobs

conversations ─1:n─ messages          (employer ⇄ job_seeker, optionally job-scoped)
subscription_plans ─1:n─ employer_subscriptions
reports (polymorphic → jobs | users)
notifications (polymorphic → users)
```

See `backend/database/migrations/` for the authoritative schema and
`docs/API.md` for the full endpoint reference.
