# Connextion Platform

A production-ready job board connecting US-based hiring companies with skilled international job seekers.

## Stack

- **Backend**: Laravel 11 (REST API)
- **Frontend**: Vue 3 + Vite + Pinia
- **Database**: MySQL 8
- **Auth**: Laravel Sanctum (token-based, role-aware)
- **Styling**: TailwindCSS 3

---

## Architecture Overview

```
connextion-platform/
├── backend/                  # Laravel 11 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   │   ├── Admin/
│   │   │   │   ├── Employer/
│   │   │   │   └── JobSeeker/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Notifications/
│   │   └── Policies/
│   ├── database/migrations/
│   └── routes/api.php
│
└── frontend/                 # Vue 3 SPA
    └── src/
        ├── api/              # Axios service modules
        ├── components/       # Reusable UI components
        ├── layouts/          # Page shell layouts
        ├── pages/            # Route-level views
        ├── router/
        └── stores/           # Pinia stores
```

---

## Quick Setup

### Prerequisites

- PHP 8.3+, Composer 2
- Node 20+, npm 10
- MySQL 8

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Edit .env with your DB credentials
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
cp .env.example .env
# Set VITE_API_BASE_URL=http://localhost:8000/api
npm run dev
```

---

## User Roles

| Role         | Description                    |
| ------------ | ------------------------------ |
| `admin`      | Full platform management       |
| `employer`   | Post jobs, manage applications |
| `job_seeker` | Apply to jobs, manage profile  |

## Key API Groups

| Prefix              | Audience                  |
| ------------------- | ------------------------- |
| `/api/auth/*`       | Public (login, register)  |
| `/api/employer/*`   | Authenticated employers   |
| `/api/job-seeker/*` | Authenticated job seekers |
| `/api/admin/*`      | Admins only               |
| `/api/jobs`         | Public job listings       |

Full endpoint reference: [`docs/API.md`](docs/API.md).

---

## Features

- **Auth**: register / login (token rotation), password reset, role-aware Sanctum tokens, account suspension.
- **Jobs**: credit- or subscription-gated posting, search/filter, soft-delete + admin restore, featured listings.
- **Applications**: apply (duplicate/closed guarded), status pipeline, withdraw, employer notes.
- **Messaging**: employer ⇄ job-seeker conversations with attachments, unread counts, polling.
- **Interviews**: employers schedule/reschedule/cancel; seekers confirm/decline; both sides notified.
- **Notifications**: in-app feed + bell (database channel) for applications, status changes, interviews, reports.
- **Reports**: users flag jobs/users for admin review (duplicate/self-report guarded).
- **Billing**: subscription plans + checkout via a pluggable `BillingService`
  (mock gateway out of the box, Stripe checkout when keys are configured).
- **Moderation**: community flagging with auto-prioritisation + repeat-offender
  escalation, admin actions (warn/suspend/remove), and a `moderation_logs` audit
  trail — see [`docs/MODERATION.md`](docs/MODERATION.md).
- **Admin**: user/job moderation, report resolution, analytics, growth charts.

## Testing & Quality

```bash
# Backend (SQLite in-memory — no DB server needed)
cd backend && php artisan test
./vendor/bin/pint --test        # code style

# Frontend
cd frontend && npm run test:run
npm run lint && npm run build
```

CI runs all of the above on every push/PR — see `.github/workflows/ci.yml`.
Details: [`docs/TESTING.md`](docs/TESTING.md) · architecture: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) · GDPR erasure: [`docs/GDPR.md`](docs/GDPR.md).

---

## Deployment (Production)

See `DEPLOYMENT.md` for full Nginx + MySQL + Supervisor config.

Billing: set `BILLING_DRIVER=stripe` plus `STRIPE_KEY`, `STRIPE_SECRET`,
`STRIPE_WEBHOOK_SECRET` to enable real Stripe checkout; otherwise the mock
gateway activates subscriptions instantly (good for staging/demos).

Environment highlights:

- Set `APP_ENV=production`, `APP_DEBUG=false`
- Use `QUEUE_CONNECTION=redis` for emails & notifications
- Run `php artisan config:cache && php artisan route:cache`
- Use S3 or similar for file storage in production
