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
| Role | Description |
|------|-------------|
| `admin` | Full platform management |
| `employer` | Post jobs, manage applications |
| `job_seeker` | Apply to jobs, manage profile |

## Key API Groups
| Prefix | Audience |
|--------|----------|
| `/api/auth/*` | Public (login, register) |
| `/api/employer/*` | Authenticated employers |
| `/api/job-seeker/*` | Authenticated job seekers |
| `/api/admin/*` | Admins only |
| `/api/jobs` | Public job listings |

---

## Deployment (Production)

See `DEPLOYMENT.md` for full Nginx + MySQL + Supervisor config.

Environment highlights:
- Set `APP_ENV=production`, `APP_DEBUG=false`
- Use `QUEUE_CONNECTION=redis` for emails & notifications
- Run `php artisan config:cache && php artisan route:cache`
- Use S3 or similar for file storage in production
