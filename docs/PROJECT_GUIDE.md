# دليل مشروع Connextion الشامل (التوثيق الكامل)

> منصّة توظيف (Job Board) تربط شركات التوظيف الأمريكية بالباحثين عن عمل المحترفين حول العالم.
> هذا المستند هو المرجع الكامل للمشروع: المعمارية، قاعدة البيانات، الـ Backend، الـ Frontend،
> الأمان، الفوترة، التحقّق، اللحظية (Realtime)، الاختبارات، والتشغيل.

المستندات المتخصّصة الأخرى تكمّل هذا الدليل:
- [`ARCHITECTURE.md`](ARCHITECTURE.md) — نظرة معمارية مختصرة.
- [`API.md`](API.md) — مرجع نقاط النهاية (Endpoints).
- [`MODERATION.md`](MODERATION.md) — طبقة الإشراف والإبلاغ.
- [`SEARCH.md`](SEARCH.md) — البحث الكامل (Full-text) المرتّب بالصلة.
- [`REALTIME.md`](REALTIME.md) — الرسائل والإشعارات اللحظية.
- [`GDPR.md`](GDPR.md) — الحق في النسيان (محو البيانات).
- [`TESTING.md`](TESTING.md) — استراتيجية الاختبارات.

---

## 1. نظرة عامة (What & Why)

**Connextion** (يُشار إليها أحياناً باسم RemoteArena) هي منصّة وظائف عن بُعد (remote-only)
تجمع بين ثلاثة أطراف:

| الدور (Role) | الوصف |
| ------------ | ----- |
| `admin` | إدارة كاملة للمنصّة: المستخدمون، الوظائف، البلاغات، التحليلات، الإشراف. |
| `employer` | شركة/مُوظِّف: ينشر الوظائف، يدير الطلبات، يجري المقابلات، يراسل المرشّحين. |
| `job_seeker` | باحث عن عمل: يكمل ملفه، يتقدّم للوظائف، يحفظها، يراسل أصحاب العمل. |

**فكرة العمل (Business Logic) الجوهرية:**
- نشر الوظيفة مشروط بـ **رصيد (credits)** أو **اشتراك (subscription)** + **تحقّق من هوية المُوظِّف**.
- التقديم على الوظائف محميّ من التكرار ومن التقديم على وظائف مغلقة.
- خطّ أنابيب حالة الطلب (Application Pipeline): من `submitted` حتى `hired` أو `rejected`.
- نظام مراسلة + جدولة مقابلات + إشعارات لحظية لكل الأطراف.
- نظام إشراف مجتمعي (Reports) مع تصعيد تلقائي وسجلّ تدقيق (Audit log).

### المنظومة التقنية (Tech Stack)

| الطبقة | التقنية |
| ------ | ------- |
| Backend | Laravel 11 (REST API) |
| Frontend | Vue 3 + Vite + Pinia + Vue Router |
| قاعدة البيانات | MySQL 8 (الإنتاج) / SQLite in-memory (الاختبارات) |
| المصادقة | Laravel Sanctum (Tokens مدركة للدور) |
| التصميم | TailwindCSS 3 + Headless UI + Heroicons |
| اللحظية | Laravel Reverb / Echo + Pusher protocol (مع polling كاحتياطي) |
| المراقبة | Laravel Telescope |
| الفوترة | Stripe (مع Mock gateway افتراضي) |

---

## 2. البنية العامة للمستودع (Repository Layout)

```
connextion-platform/
├── backend/                      # Laravel 11 API
│   ├── app/
│   │   ├── Console/Commands/     # أوامر artisan مخصّصة (admin:create, user:forget)
│   │   ├── Http/
│   │   │   ├── Controllers/Api/  # المتحكّمات مقسّمة حسب الدور
│   │   │   │   ├── Admin/
│   │   │   │   ├── Employer/
│   │   │   │   └── JobSeeker/
│   │   │   ├── Middleware/       # EnsureRole, EnsureActive, EnsureEmployerVerified
│   │   │   └── Requests/         # Form Requests (التحقّق من المدخلات)
│   │   ├── Models/               # نماذج Eloquent
│   │   ├── Services/             # منطق العمل (Business logic)
│   │   ├── Notifications/        # إشعارات قاعدة البيانات/البريد
│   │   └── Policies/             # سياسات التفويض (Authorization)
│   ├── config/                   # billing, verification, cors, broadcasting
│   ├── database/
│   │   ├── migrations/           # مخطّط قاعدة البيانات
│   │   └── seeders/              # بيانات تجريبية + الأدمن
│   ├── routes/api.php            # كل نقاط النهاية
│   └── tests/                    # Feature + Unit (PHPUnit)
│
└── frontend/                     # Vue 3 SPA
    └── src/
        ├── api/                  # وحدات Axios لكل مورد
        ├── components/           # مكوّنات UI قابلة لإعادة الاستخدام
        ├── layouts/              # قوالب الصفحات (Public/Auth/App)
        ├── pages/                # الصفحات حسب الدور
        ├── realtime/             # إعداد Laravel Echo
        ├── router/               # تعريف المسارات + حُرّاس التنقّل
        ├── stores/               # Pinia stores (الحالة العامّة)
        └── i18n/                 # الترجمة (تعدّد اللغات)
```

---

## 3. المعمارية (Architecture)

التطبيق ثنائي الطبقة (two-tier) منفصل تماماً:

```
┌─────────────────┐      HTTPS / JSON       ┌──────────────────┐
│   Vue 3 SPA     │ ──────────────────────▶ │  Laravel 11 API  │
│  (frontend/)    │  Authorization: Bearer  │   (backend/)     │
│                 │ ◀────────────────────── │                  │
│  Pinia Stores   │      WebSocket (Echo)   │  Sanctum + RBAC  │
└─────────────────┘ ◀────────────────────── └────────┬─────────┘
                                                      │
                                            ┌─────────┴─────────┐
                                            │   MySQL 8         │
                                            │   Reverb (WS)     │
                                            │   Stripe / Mock   │
                                            └───────────────────┘
```

**التدفّق النمطي للطلب (Request lifecycle) في الـ Backend:**

```
Route (api.php)
  → Middleware (auth:sanctum → active → role:* → throttle → verified.employer?)
    → Form Request (التحقّق من المدخلات + التفويض)
      → Controller (تنسيق فقط — رفيع جدّاً)
        → Service (منطق العمل الفعلي + المعاملات DB transactions)
          → Model / Eloquent (الوصول للبيانات)
        ← Notification (إطلاق إشعارات لحظية)
      ← JSON Response
```

**المبادئ المعمارية المتّبعة:**
1. **متحكّمات رفيعة، خدمات سمينة (Thin controllers, fat services):** كل منطق العمل في `app/Services`.
2. **التحقّق في Form Requests** وليس في المتحكّمات.
3. **التفويض في Policies** (`JobPolicy`, `JobApplicationPolicy`, `ConversationPolicy`).
4. **فصل الأدوار على مستوى المسار** عبر بادئات (`/employer`, `/job-seeker`, `/admin`) و middleware.
5. **Driver pattern للفوترة والتحقّق** ليمكن استبدال المزوّد دون لمس المتحكّمات.

---

## 4. نموذج البيانات (Database Schema)

قاعدة البيانات معرّفة في `backend/database/migrations`. فيما يلي الجداول الأساسية وعلاقاتها.

### مخطّط العلاقات (ERD مبسّط)

```
users (1)──(1) employer_profiles ──(N) jobs ──(N) job_applications ──(N) interview_schedules
  │                   │                            │
  │                   └──(N) employer_subscriptions│
  │                                                │
  └──(1)──(1) job_seeker_profiles ────────────────┘
                   │  └──(N) saved_jobs
                   │
conversations (employer_profile_id + job_seeker_profile_id + job_id) ──(N) messages
reports (polymorphic → user|job) ──(N) moderation_logs
skills ──(N:N via job_skills) jobs
skills ──(N:N via job_seeker_skills) job_seeker_profiles
notifications (Laravel database channel)
```

### 4.1 `users` — حساب أساسي لكل الأدوار
الحقول: `id, name, email (unique), email_verified_at, password (hashed), role (enum: admin|employer|job_seeker), is_active (bool), avatar, phone, country, timezone, last_seen_at, remember_token, timestamps, deleted_at (SoftDeletes)`.
- فهرس مركّب على `(role, is_active)`.
- جدول `password_reset_tokens` (المفتاح = البريد) يُنشأ في نفس الهجرة.
- النموذج `User` يستخدم: `HasApiTokens` (Sanctum), `Notifiable`, `SoftDeletes`, `HasFactory`.
- دوال مساعدة: `isAdmin()`, `isEmployer()`, `isJobSeeker()`, `profile()` (تُرجع الملف حسب الدور).
- `sendPasswordResetNotification()` يبني رابط الـ SPA (وليس الـ API) من `FRONTEND_URL`.

### 4.2 `employer_profiles` — ملف الشركة
`user_id, company_name, company_slug (unique), description, industry, company_size, website, logo, headquarters_*, linkedin_url, twitter_url, founded_year, is_verified, is_featured, subscription_tier (enum: free|basic|pro|enterprise), job_post_credits (default 3)`.
- أعمدة التحقّق (هجرة 000018): `verified_at, verification_method (domain|linkedin|payment), linkedin_id`.

### 4.3 `job_seeker_profiles` — ملف الباحث عن عمل
`user_id, headline, bio, resume (مسار ملف), portfolio_url, linkedin_url, github_url, current_city/country, nationality, open_to_remote, willing_to_relocate, experience_level (entry|mid|senior|lead|executive), years_of_experience, current/desired_job_title, desired_salary_min/max, currency, availability (immediately|two_weeks|one_month|negotiable), profile_complete (bool), is_featured`.
- فقط الملفات بـ `profile_complete = true` تظهر في دليل المحترفين العام.

### 4.4 `jobs` — الوظائف
`employer_profile_id (FK cascade), title, slug (unique, يُولّد تلقائياً), description, requirements, benefits, category, employment_type (full_time|part_time|contract|freelance|internship), location_type (remote|hybrid|on_site), location_*, salary_min/max, currency, salary_period (hourly|monthly|annual), salary_visible, experience_level, visa_sponsorship, open_to_international, status (draft|active|paused|closed|expired), is_featured, views_count, applications_count, expires_at, SoftDeletes`.
- النموذج `Job`: `boot()` يولّد الـ slug تلقائياً (`Str::slug(title)-random6`).
- Scopes: `active()` (status=active وغير منتهية)، `search()`، `forExperienceLevel()`.
- علاقات: `employer`, `skills` (N:N + pivot `is_required`), `applications`, `savedBy`, `conversations`.
- جدول الربط `job_skills`: `(job_id, skill_id, is_required)` مفتاح مركّب.

### 4.5 `job_applications` — طلبات التوظيف
`job_id, job_seeker_profile_id, cover_letter, resume_snapshot (نسخة من السيرة وقت التقديم), expected_salary, currency, status, employer_notes, viewed_at`.
- **قيد فريد** `(job_id, job_seeker_profile_id)` يمنع التقديم المكرّر.
- خطّ أنابيب الحالة: `submitted → viewed → shortlisted → interview_scheduled → offer_extended → hired` (أو `rejected` / `withdrawn`).

### 4.6 `conversations` + `messages` — المراسلة
- `conversations`: `employer_profile_id, job_seeker_profile_id, job_id (nullable), last_message_at, last_message_id`. قيد فريد على الثلاثية لمنع تكرار المحادثة.
- `messages`: `conversation_id, sender_id (→ users), body, attachment, read_at`. فهرس `(conversation_id, created_at)`.

### 4.7 `interview_schedules` — المقابلات
`job_application_id, scheduled_by (→ users), scheduled_at, duration_minutes (60), format (video|phone|in_person), meeting_link, location, notes, status (pending|confirmed|cancelled|completed), confirmed_at`.

### 4.8 `subscription_plans` + `employer_subscriptions` — الاشتراكات
- الخطط: `name, slug, description, price_monthly/annual (بالسنت), job_posts_limit (0 = غير محدود), featured_listings, candidate_search, analytics, priority_support, is_active`.
- اشتراك المُوظِّف: `employer_profile_id, subscription_plan_id, stripe_subscription_id, stripe_customer_id, billing_period, status (trialing|active|past_due|cancelled|expired), trial_ends_at, current_period_start/end, cancelled_at`.

### 4.9 `reports` + `moderation_logs` — الإشراف
- `reports`: `reporter_id, reportable (polymorphic → user|job), reason, details, status (open|under_review|resolved|dismissed), priority (low|normal|high|critical), resolved_by, resolution_note, resolved_at`. فهرس `reports_target_status` على `(reportable_type, reportable_id, status)`.
- `moderation_logs`: سجلّ تدقيق غير قابل للتعديل (append-only): `moderator_id, user_id, report_id, action (warning|suspension|reinstatement|dismissal|content_removed|note), notes, metadata (JSON)`.

### 4.10 جداول مساعدة
- `skills`, `saved_jobs`, `notifications` (قناة قاعدة البيانات في Laravel), `cache`, `sessions`, `telescope_entries`.
- هجرات لاحقة: `000016 harden_messaging`, `000017 performance_indexes`, `000020 fulltext_search_indexes` (FULLTEXT على الوظائف والملفات لتسريع البحث — انظر [`SEARCH.md`](SEARCH.md)).

---

## 5. الـ Backend بالتفصيل

### 5.1 المسارات (Routes) — `backend/routes/api.php`

كل نقاط النهاية تحت بادئة `/api`. مُجمّعة حسب مستوى الوصول مع حدود معدّل (rate limits) مختلفة:

| المجموعة | الـ Middleware | المعدّل | أمثلة |
| -------- | ------------- | ------- | ----- |
| Public auth | `throttle:10,1` | 10/دقيقة | `/auth/register`, `/auth/login`, `/auth/forgot-password`, `/auth/reset-password` |
| Public reads | `throttle:90,1` | 90/دقيقة | `/jobs`, `/jobs/{slug}`, `/subscription-plans`, `/professionals`, `/skills` |
| Webhooks | — (موقّع) | بلا حد | `/billing/webhook` (Stripe), `/auth/linkedin/callback` |
| أي مستخدم مُصادَق | `auth:sanctum, active, throttle:60,1` | 60/دقيقة | `/auth/me`, `/auth/logout`, `/notifications/*`, `/reports`, `/broadcasting/auth` |
| Employer | `+ role:employer` (+ `verified.employer` للنشر) | 60/دقيقة | `/employer/*` |
| Job Seeker | `+ role:job_seeker` | 60/دقيقة | `/job-seeker/*` |
| Admin | `+ role:admin` | 60/دقيقة | `/admin/*` |

ملاحظات على المعدّل: نقاط الـ polling (الرسائل والإشعارات) تحصل على `throttle:120,1`، والإرسال على `throttle:30,1`، والإبلاغ على `throttle:20,1`. لمرجع كامل بكل المسارات انظر [`API.md`](API.md) و `routes/api.php`.

### 5.2 الـ Middleware — `app/Http/Middleware`

| الـ Middleware | الاسم المستعار | الوظيفة |
| -------------- | -------------- | ------- |
| `EnsureRole` | `role:*` | يتحقّق أن دور المستخدم يطابق الدور المطلوب وإلا 403. |
| `EnsureActive` | `active` | يرفض الحسابات المُعلّقة (`is_active = false`) بـ 403. |
| `EnsureEmployerVerified` | `verified.employer` | يمنع نشر الوظائف ما لم يكن المُوظِّف موثّقاً. |

(الترتيب: `auth:sanctum` ثم `active` ثم `role` ثم باقي القيود.)

### 5.3 المتحكّمات (Controllers) — `app/Http/Controllers/Api`

مُنظّمة حسب الدور (رفيعة، تفوّض المنطق للخدمات):

- **عامّة:** `AuthController`, `PasswordResetController`, `JobController` (تصفّح عام), `ProfessionalController`, `SubscriptionPlanController`, `ReportController`, `NotificationController`, `BillingWebhookController`, `LinkedInAuthController`.
- **`Employer/`:** `JobController`, `ApplicationController`, `InterviewController`, `MessageController`, `ProfileController`, `StatsController`, `SubscriptionController`, `VerificationController`.
- **`JobSeeker/`:** `JobController` (موصى به/محفوظ), `ApplicationController`, `InterviewController`, `MessageController`, `ProfileController`, `StatsController`.
- **`Admin/`:** `AdminController`, `DashboardController` (إحصائيات + نمو), `UserController`, `JobController`, `ReportController`, `ModerationController`.

### 5.4 الخدمات (Services) — `app/Services` (قلب منطق العمل)

| الخدمة | المسؤولية |
| ------ | --------- |
| `AuthService` | التسجيل/الدخول، إصدار وتدوير الـ tokens، إنشاء الملف حسب الدور. |
| `JobService` | إنشاء/تعديل/حذف الوظائف، خصم الرصيد/فحص الاشتراك، التفعيل والإيقاف. |
| `ApplicationService` | التقديم (حماية من التكرار/الوظيفة المغلقة)، تحديث الحالة، السحب، ملاحظات المُوظِّف. |
| `InterviewService` | جدولة/إعادة جدولة/إلغاء المقابلات، التأكيد/الرفض، الإشعارات. |
| `MessageService` | بدء المحادثات، الإرسال، المرفقات، عدّ غير المقروء، التحديد كمقروء. |
| `JobMatchingService` | خوارزمية التوصية (skill-overlap + experience + salary). |
| `ModerationService` | تصعيد البلاغات تلقائياً، إجراءات الأدمن، كتابة سجلّ التدقيق. |
| `ProfessionalService` | دليل المحترفين العام (فقط الملفات المكتملة). |
| `FileUploadService` | رفع الملفات (السير الذاتية، الشعارات، المرفقات). |
| `UserErasureService` | محو/تمويه بيانات المستخدم (GDPR). |
| `Billing/` | `BillingService` + بوّابات (`MockGateway`, `StripeGateway`, `AbstractGateway`, واجهة `PaymentGateway`). |
| `Verification/` | `VerificationService` + `EmailDomainClassifier` (تصنيف نطاق البريد للتحقّق التلقائي). |

#### خوارزمية التوصية (`JobMatchingService`)
نظام تسجيل بسيط (0–115 نقطة فعلياً، مرتّب تنازلياً) قابل للاستبدال لاحقاً بـ AI/embeddings:
1. **تطابق المهارات (0–60 + حتى 15):** نسبة المهارات المطلوبة المتطابقة × 60، + 5 لكل مهارة اختيارية (بحد 15).
2. **مستوى الخبرة (0–20):** `20 - فرق_المستوى × 8`.
3. **تداخل نطاق الراتب (0–20):** 20 نقطة عند وجود أي تداخل.

تحسين أداء: يُفلتر مبدئياً على مستوى قاعدة البيانات (`whereHas('skills')`) لتقليص الحمل قبل التسجيل في PHP، مع احتياطي بأحدث الوظائف عند غياب تطابق المهارات.

### 5.5 الفوترة (Billing) — نمط Driver

التهيئة في `config/billing.php` عبر `BILLING_DRIVER`:
- **`mock`** (الافتراضي): يُفعّل الاشتراك فوراً بلا استدعاء خارجي — مثالي للتطوير والاختبارات والعروض.
- **`stripe`**: يُنشئ Stripe Checkout session، ويُفعّل الاشتراك عبر الـ webhook بعد نجاح الدفع. يتطلّب `STRIPE_SECRET`.
- **أمان افتراضي:** إن اختير `stripe` دون مفاتيح، يرجع تلقائياً إلى `mock` فلا يتعطّل التطبيق أبداً.
- الـ webhook (`/billing/webhook`) يُتحقّق منه عبر التوقيع (`STRIPE_WEBHOOK_SECRET`) لا عبر المصادقة، وغير محدود المعدّل كي لا تُفقَد دفعات الأحداث.

### 5.6 التحقّق من المُوظِّف (Employer Verification) — `config/verification.php`

نشر الوظائف يتطلّب مُوظِّفاً موثّقاً. ثلاث طرق:
1. **`domain`** — بريد شركة بنطاق مؤسّسي → توثيق تلقائي عند التسجيل (`EmailDomainClassifier`، واختيارياً فحص سجلّات MX عبر `VERIFICATION_CHECK_MX`).
2. **`linkedin`** — OAuth (OpenID Connect). تُعطّل بترك `LINKEDIN_CLIENT_ID/SECRET` فارغين (تُرجع النقاط 503 ويخفي الـ UI الخيار).
3. **`payment`** — تفويض دفعة رمزية (افتراضياً 100 سنت عبر `VERIFICATION_PAYMENT_AMOUNT`) لإثبات وسيلة دفع حقيقية.

### 5.7 الإشعارات (Notifications) — `app/Notifications`
عبر قناة قاعدة البيانات (جدول `notifications`) + خلاصة داخل التطبيق وجرس (bell):
`ApplicationReceived`, `ApplicationStatusUpdated`, `InterviewScheduled`, `InterviewStatusChanged`, `ModerationActionNotification`, `NewReportSubmitted`, `ResetPasswordNotification` (بريد).

### 5.8 السياسات (Policies) — `app/Policies`
`JobPolicy` (صاحب الوظيفة فقط يعدّل/يحذف)، `JobApplicationPolicy` (المُوظِّف المالك أو المتقدّم)، `ConversationPolicy` (طرفا المحادثة فقط).

### 5.9 أوامر Artisan — `app/Console/Commands`
- `admin:create` (`CreateAdmin`) — إنشاء حساب أدمن (انظر مذكّرة "Admin & Telescope").
- `user:forget` (`ForgetUser`) — محو بيانات مستخدم يدوياً (GDPR، انظر [`GDPR.md`](GDPR.md)).

---

## 6. الـ Frontend بالتفصيل (Vue 3 SPA)

### 6.1 الإعداد العام
- **Vite** كأداة بناء، **Pinia** للحالة، **Vue Router** للتوجيه، **TailwindCSS** للتصميم.
- متغيّر البيئة `VITE_API_BASE_URL` يشير إلى `http://localhost:8000/api`.
- اعتماديات بارزة: `@headlessui/vue`, `@heroicons/vue`, `laravel-echo`, `pusher-js`.

### 6.2 طبقة الـ API — `src/api`
عميل Axios مركزي (`client.js`):
- يضيف `Authorization: Bearer {token}` تلقائياً من `localStorage` في كل طلب.
- معترِض الاستجابة: عند 401 يمسح الـ token ويعيد التوجيه الصلب (`window.location`) إلى `/login` لتنظيف حالة الـ SPA.
- وحدات لكل مورد: `auth, jobs, applications, interviews, messages, notifications, professionals, reports, billing, verification`.

### 6.3 المخازن (Pinia Stores) — `src/stores`
- **`auth.js`** (الأهم): يدير `user`, `token`, `initialized`. `isAuthenticated` يعتمد على **وجود الـ token والمستخدم معاً** (الـ token وحده ليس دليلاً على جلسة صالحة). يستخدم `_initPromise` مشترك كي لا يكرّر حُرّاس التوجيه استدعاء `fetchMe`. يوفّر `dashboardPath()` حسب الدور.
- **`jobs.js`**, **`messages.js`**, **`notifications.js`**, **`professionals.js`**, **`language.js`** (تبديل اللغة).
- توجد اختبارات وحدة للمخازن (`*.spec.js`).

### 6.4 التوجيه (Router) — `src/router/index.js`
أربع مجموعات بقوالب مختلفة + حارس تنقّل عام (`beforeEach`):
- **Public** (`PublicLayout`): الرئيسية، البحث عن وظائف، تفاصيل وظيفة، دليل المحترفين، الأسعار، الصفحات القانونية.
- **Auth** (`AuthLayout`, `meta.guest`): الدخول، التسجيل، استعادة/إعادة كلمة المرور.
- **Employer / Job Seeker / Admin** (`AppLayout`, `meta.auth + meta.role`): لوحات التحكّم والصفحات الخاصّة بكل دور.

**منطق الحارس:**
1. ينتظر `auth.init()` مرّة واحدة لكل تحميل.
2. المستخدم المُصادَق على صفحة ضيف → يُحوَّل للوحته.
3. غير المُصادَق على صفحة محمية → `/login` مع `redirect`.
4. الدور الخاطئ → لوحته (الأدمن يتجاوز فحص الدور ليتفقّد أي قسم).

### 6.5 الصفحات (Pages) — `src/pages`
- **عامّة:** `Home`, `Pricing`, `PrivacyPolicy`, `TermsAndConditions`, `CommunityGuidelines`, `NotFound`.
- **`auth/`:** `Login`, `Register`, `ForgotPassword`, `ResetPassword`.
- **`jobs/`:** `JobSearch`, `JobDetail`. **`professionals/`:** `ProfessionalSearch`, `ProfessionalDetail`.
- **`employer/`:** `Dashboard`, `Profile`, `Jobs`, `PostJob`, `Applications`, `ApplicationDetail`, `Billing`, `Verification`.
- **`jobseeker/`:** `Dashboard`, `Profile`, `Applications`, `SavedJobs`, `Interviews`.
- **`admin/`:** `Dashboard`, `Users`, `UserDetail`, `Jobs`, `Reports`, `Analytics`.
- **`shared/`:** `Messages`, `Notifications` (مشتركة بين الأدوار).

### 6.6 المكوّنات (Components) — `src/components`
`jobs/JobCard`, `jobs/ApplyModal`, `professionals/ProfessionalCard`, `LanguageSwitcher`, `NotificationBell`, `ReportModal`.

### 6.7 اللحظية (Realtime) — `src/realtime/echo.js`
إعداد Laravel Echo على بروتوكول Pusher يتّصل بـ Reverb. الرسائل والإشعارات تتحدّث **حيّاً** عبر WebSocket مع **polling كاحتياطي تلقائي** — إن لم يُهيّأ Reverb لا ينكسر شيء. التفاصيل في [`REALTIME.md`](REALTIME.md). عند تسجيل الخروج يُستدعى `disconnectEcho()`.

---

## 7. تدفّقات الأعمال الرئيسية (Key Flows)

### 7.1 التسجيل والمصادقة
```
POST /auth/register → AuthService ينشئ user + الملف حسب الدور + token
  → الـ SPA يخزّن الـ token ويُحدّد initialized=true (يتجنّب fetchMe زائد)
  → يُحوَّل للوحة الدور
```
المصادقة عبر Sanctum tokens. عند 401 في أي طلب يُمسح الـ token ويُعاد التوجيه للدخول.

### 7.2 نشر وظيفة (Employer)
```
POST /employer/jobs (verified.employer required)
  → JobService يتحقّق: مُوظِّف موثّق + (رصيد job_post_credits أو اشتراك فعّال)
  → يخصم رصيداً/يفحص حدّ الخطة → ينشئ الوظيفة (slug تلقائي) بحالة active
```

### 7.3 التقديم على وظيفة (Job Seeker)
```
POST /job-seeker/jobs/{job}/apply
  → ApplicationService: يمنع التكرار (قيد فريد) + يمنع التقديم على وظيفة مغلقة
  → يلتقط نسخة من السيرة (resume_snapshot) → ينشئ الطلب بحالة submitted
  → Notification: ApplicationReceived → المُوظِّف (لحظي)
```

### 7.4 خطّ أنابيب الطلب + المقابلة
```
المُوظِّف: PATCH .../status → submitted→viewed→shortlisted→...→hired/rejected
  → ApplicationStatusUpdated → الباحث
المُوظِّف: POST .../interviews → InterviewScheduled → الباحث
الباحث: confirm/cancel → InterviewStatusChanged → المُوظِّف
```

### 7.5 المراسلة
```
المُوظِّف يبدأ محادثة (POST /employer/conversations) → الطرفان يتبادلان الرسائل
  → polling كل 15s (أو WebSocket لحظي) + عدّ غير المقروء + تحديد كمقروء
```

### 7.6 الإبلاغ والإشراف
```
أي مستخدم: POST /reports (job|user) → ModerationService يضبط priority تلقائياً
  → تصعيد عند تعدّد البلاغات على نفس الهدف → NewReportSubmitted → الأدمن
الأدمن: POST /admin/reports/{report}/action (warning|suspension|...)
  → يُكتب moderation_log (تدقيق) → ModerationActionNotification → المستخدم
```

---

## 8. الأمان (Security)

- **المصادقة:** Sanctum bearer tokens، مدركة للدور، مع تدوير وإبطال (تعليق الحساب).
- **التفويض:** RBAC على ثلاث طبقات — middleware المسار (`role:*`) + Policies + فحوص الخدمة.
- **تحديد المعدّل (Rate limiting):** حدود مشدّدة على المصادقة/الإبلاغ/الإرسال، سخيّة على التصفّح والـ polling.
- **حماية تعداد الحسابات:** نقاط استعادة كلمة المرور مشدّدة المعدّل.
- **تعليق الحساب:** middleware `active` يرفض المُعلّقين فوراً (403).
- **حماية البحث:** تهريب أحرف `%` و`_` في استعلامات المهارات.
- **GDPR:** محو/تمويه البيانات عبر `UserErasureService` و`user:forget` (انظر [`GDPR.md`](GDPR.md)).
- **التحقّق من الـ webhook بالتوقيع** لا بالمصادقة.
- **المراقبة:** Laravel Telescope (مُقيّد الوصول — انظر مذكّرة Admin & Telescope).

> راجع `AUDIT_REPORT.md` في الجذر لتقرير التدقيق الأمني الكامل.

---

## 9. الاختبارات والجودة (Testing & Quality)

### Backend (PHPUnit على SQLite in-memory — بلا خادم DB)
```bash
cd backend
php artisan test
./vendor/bin/pint --test     # فحص نمط الكود (Laravel Pint)
```
تغطية الاختبارات (`tests/Feature` و`tests/Unit`): المصادقة، استعادة كلمة المرور، وظائف المُوظِّف، طلبات الباحث، المقابلات، المراسلة، الإشعارات، البلاغات، الاشتراكات، التحقّق، الإشراف، محو GDPR، الـ middleware، الـ broadcasting، واختبار دخان (Smoke).

### Frontend (Vitest)
```bash
cd frontend
npm run test:run     # اختبارات الوحدة (المخازن، echo)
npm run lint         # ESLint
npm run build        # بناء الإنتاج
```

### CI
`.github/workflows/ci.yml` يشغّل كل ما سبق على كل push/PR. التفاصيل في [`TESTING.md`](TESTING.md).

---

## 10. الإعداد والتشغيل (Setup & Run)

### المتطلّبات
PHP 8.3+ و Composer 2 · Node 20+ و npm 10 · MySQL 8 (أو SQLite للاختبارات).

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# عدّل بيانات قاعدة البيانات في .env
php artisan migrate --seed
php artisan storage:link
php artisan serve            # http://localhost:8000
```
بيانات تجريبية بعد الـ seed: أدمن (`admin@connextion.io` / `password`)، مُوظِّف (`employer@demo.com`)، ومهارات وخطط أساسية.

### Frontend
```bash
cd frontend
npm install
cp .env.example .env
# عيّن VITE_API_BASE_URL=http://localhost:8000/api
npm run dev                  # http://localhost:5173
```

---

## 11. النشر للإنتاج (Production Deployment)

راجع `DEPLOYMENT.md` لإعداد Nginx + MySQL + Supervisor الكامل. أبرز النقاط:
- `APP_ENV=production`, `APP_DEBUG=false`.
- `QUEUE_CONNECTION=redis` للبريد والإشعارات.
- `php artisan config:cache && php artisan route:cache`.
- استخدم S3 أو ما يماثله لتخزين الملفات.
- **الفوترة:** `BILLING_DRIVER=stripe` + `STRIPE_KEY/SECRET/WEBHOOK_SECRET` لتفعيل Stripe؛ وإلا تعمل البوّابة الوهمية فوراً (مناسبة للـ staging/العروض).
- **اللحظية:** هيّئ Reverb لتفعيل WebSocket؛ بدونه يعمل الـ polling تلقائياً.

---

## 12. متغيّرات البيئة المرجعية (Environment Variables)

| المتغيّر | الغرض |
| -------- | ----- |
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | إعداد Laravel الأساسي. |
| `FRONTEND_URL` | يُستخدم في روابط استعادة كلمة المرور وإعادة توجيه الفوترة (يقبل قائمة مفصولة بفواصل). |
| `DB_*` | اتصال قاعدة البيانات. |
| `QUEUE_CONNECTION` | `redis` للإنتاج. |
| `BILLING_DRIVER` | `mock` (افتراضي) أو `stripe`. |
| `STRIPE_KEY/SECRET/WEBHOOK_SECRET` | مفاتيح Stripe. |
| `BILLING_SUCCESS_URL`, `BILLING_CANCEL_URL` | إعادة توجيه Checkout. |
| `VERIFICATION_CHECK_MX` | فحص سجلّات MX لتصنيف النطاق (افتراضي false). |
| `VERIFICATION_PAYMENT_AMOUNT` | مبلغ تفويض التحقّق بالسنت (افتراضي 100). |
| `LINKEDIN_CLIENT_ID/SECRET/REDIRECT_URI` | تحقّق LinkedIn (اختياري). |
| `VITE_API_BASE_URL` | عنوان الـ API للـ frontend. |

---

## 13. مرجع سريع لخريطة المسارات (Route → Page → Controller)

| الواجهة (Vue) | المسار | الـ API | المتحكّم |
| ------------- | ------ | ------- | -------- |
| `Home`, `JobSearch` | `/`, `/jobs` | `GET /jobs` | `Api\JobController` |
| `JobDetail` | `/jobs/:slug` | `GET /jobs/{slug}` | `Api\JobController` |
| `Login`/`Register` | `/login`, `/register` | `POST /auth/*` | `AuthController` |
| `employer/PostJob` | `/employer/jobs/new` | `POST /employer/jobs` | `Employer\JobController` |
| `employer/Applications` | `/employer/applications` | `GET/PATCH /employer/applications/*` | `Employer\ApplicationController` |
| `employer/Billing` | `/employer/billing` | `/employer/subscription/*` | `Employer\SubscriptionController` |
| `jobseeker/Applications` | `/job-seeker/applications` | `/job-seeker/applications/*` | `JobSeeker\ApplicationController` |
| `jobseeker/Interviews` | `/job-seeker/interviews` | `/job-seeker/interviews/*` | `JobSeeker\InterviewController` |
| `shared/Messages` | `/*/messages` | `/*/conversations/*` | `*\MessageController` |
| `admin/Dashboard` | `/admin/dashboard` | `GET /admin/dashboard` | `Admin\DashboardController` |
| `admin/Reports` | `/admin/reports` | `/admin/reports/*` | `Admin\ReportController`, `ModerationController` |

---

## 14. أين أبدأ القراءة؟ (Onboarding سريع للمطوّرين الجدد)

1. **افهم الأدوار والمسارات:** `backend/routes/api.php` + `frontend/src/router/index.js`.
2. **افهم البيانات:** `backend/database/migrations` (بالترتيب) ثم `app/Models`.
3. **افهم منطق العمل:** `app/Services` (ابدأ بـ `JobService`, `ApplicationService`).
4. **افهم الحالة في الواجهة:** `frontend/src/stores/auth.js` ثم `src/api/client.js`.
5. **شغّل الاختبارات** لترى السلوك المتوقّع موثّقاً تنفيذياً (`tests/Feature`).
6. **راجع المستندات المتخصّصة** المذكورة في المقدّمة حسب الميزة التي تعمل عليها.

---

*آخر تحديث: 2026-06-24 — هذا المستند يصف الحالة الحالية للمشروع كما هي في الكود.*
