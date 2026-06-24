# Testing

The project has automated tests on both tiers, runnable with no external
services. CI (`.github/workflows/ci.yml`) runs all of them on every push/PR.

## Backend (PHPUnit)

- **Engine**: SQLite in-memory (`phpunit.xml` sets `DB_CONNECTION=sqlite`,
  `DB_DATABASE=:memory:`). No MySQL needed for the test suite.
- **Isolation**: every feature test uses `RefreshDatabase` (fresh migration per test).
- **Data**: model factories in `database/factories/` (one per model, with named
  states like `->employer()`, `->active()`, `->incomplete()`, `->noCredits()`).

```bash
cd backend
php artisan test                 # full suite (Unit + Feature)
php artisan test --filter=AuthTest
./vendor/bin/pint --test         # code-style check (CI gate)
./vendor/bin/pint                # auto-fix style
```

### Coverage map

| Area | File |
| --- | --- |
| Registration, login, token rotation, logout, suspended-login | `tests/Feature/Auth/AuthTest.php` |
| Password reset (anti-enumeration, token, revocation) | `tests/Feature/Auth/PasswordResetTest.php` |
| Employer jobs: credits, isolation, IDOR, toggle, soft-delete | `tests/Feature/Employer/EmployerJobTest.php` |
| Employer applications: status, notify, isolation | `tests/Feature/Employer/EmployerApplicationTest.php` |
| Job seeker apply/withdraw/duplicate/closed, saved jobs | `tests/Feature/JobSeeker/JobSeekerApplicationTest.php` |
| Public job listing, filters, search, slug, LIKE-escape | `tests/Feature/PublicJobTest.php` |
| Profiles (lazy-create, skills, resume), public professionals privacy | `tests/Feature/ProfileTest.php` |
| Admin: user/job moderation, delete guards, report resolve | `tests/Feature/Admin/AdminManagementTest.php` |
| Messaging: initiate, send, unread, policy isolation | `tests/Feature/MessagingTest.php` |
| Notifications feed | `tests/Feature/NotificationTest.php` |
| Reports (create, duplicate, self-report, admin notify) | `tests/Feature/ReportTest.php` |
| Interviews (schedule, confirm, validation, isolation) | `tests/Feature/InterviewTest.php` |
| Subscriptions/billing (subscribe, cancel, webhook) | `tests/Feature/SubscriptionTest.php` |
| Middleware (active/role/auth gates) | `tests/Feature/MiddlewareTest.php` |
| Services & model invariants | `tests/Unit/*` |

## Frontend (Vitest)

- **Environment**: jsdom (`vitest.config.js`), shims in `src/test/setup.js`.
- API modules and the router are mocked so stores/components test in isolation.

```bash
cd frontend
npm run test:run        # headless run (CI)
npm run test            # watch mode
npm run test:coverage   # with coverage
npm run lint            # ESLint (CI gate)
npm run build           # production build (CI gate)
```

| Suite | File |
| --- | --- |
| Auth store (roles, token persistence, 401-only logout) | `src/stores/auth.spec.js` |
| Notifications store (feed, unread counter, mark/remove) | `src/stores/notifications.spec.js` |
| ReportModal component (submit, confirmation, error) | `src/components/ReportModal.spec.js` |

## Writing new tests

- **Backend**: add a factory state if you need a new fixture shape; prefer
  asserting on DB rows (`assertDatabaseHas`) and JSON paths (`assertJsonPath`).
  For Sanctum, `Sanctum::actingAs($user)` for a single request; raw
  `Bearer` headers when you need to test token lifecycle across requests.
- **Frontend**: mock the relevant `@/api/*` module and (if used) `@/stores/auth`,
  then drive the store/component and assert on its observable state/DOM.
