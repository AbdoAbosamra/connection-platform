# RemoteArena — Deep Feature Audit & Roadmap

A feature-by-feature analysis of the platform's current state, the improvements
applied in this pass, and the prioritised roadmap of what to build next.

> Baseline at time of audit: **143 backend tests / 378 assertions** and **14
> frontend tests** green; Pint + ESLint clean; production build passing.

---

## 1. Feature-by-feature analysis

Legend: 🟢 solid · 🟡 works, has improvement headroom · 🔴 gap.

### Authentication & sessions — 🟢
- Sanctum bearer tokens, token rotation on login, full revocation on logout & password reset, suspended-account block at login **and** mid-session (`active` middleware).
- Password reset is anti-enumeration; tokens invalidated on reset.
- *Headroom:* email verification exists as endpoints but isn't enforced; 2FA absent (roadmap #3).

### Employer verification (anti-fraud) — 🟢
- Corporate-domain auto-verify, disposable-email rejection, posting gated until verified, LinkedIn OAuth + Stripe payment paths with graceful fallback.
- *Headroom:* domain MX check is opt-in; no admin manual-verify override (roadmap #11).

### Jobs (post / search / lifecycle) — 🟡
- Credit/subscription-gated posting under row locks (no TOCTOU), soft-delete + restore, featured ordering, status lifecycle.
- **Improvement applied:** public read endpoints are now rate-limited (see §2).
- *Headroom:* search is `LIKE '%…%'` (full-table scan) — needs a fulltext / search engine at scale (roadmap #1). View counter increments on every GET (botable).

### Applications — 🟢
- Apply with duplicate/closed guards under lock, withdraw with counter integrity, status pipeline, employer notes, resume snapshot at apply-time.
- *Headroom:* no bulk status actions for employers (roadmap #8).

### Messaging — 🟢
- Employer⇄seeker threads, attachment-or-text (fixed), unread counts via correlated subquery (no N+1), polling, optimistic send with rollback, policy isolation.
- *Headroom:* polling not realtime — websockets/SSE would cut latency & load (roadmap #2).

### Interviews — 🟢
- Schedule/reschedule/cancel, seeker confirm/decline, pipeline transition, both-party notifications, format-aware validation.
- *Headroom:* no calendar (.ics) invite or reminders (roadmap #6).

### Notifications — 🟡
- DB-channel feed + bell + unread polling, mark/clear.
- *Headroom:* no user preferences/digest, email channel inconsistent across types (roadmap #5).

### Billing & subscriptions — 🟡
- Plan listing, subscribe/cancel, mock + Stripe gateways behind one service, webhook activation.
- *Headroom:* no invoices/receipts, proration, dunning, or billing history (roadmap #4).

### Moderation (flags) — 🟢
- Polymorphic flags, reason taxonomy aligned to Community Guidelines, auto-priority + repeat-offender escalation, admin actions with `moderation_logs` audit trail, index-optimised "most-flagged" query.
- *Headroom:* no reporter feedback loop / appeal flow (roadmap #12).

### GDPR erasure — 🟢
- Transactional pseudonymisation across all tables, file deletion, session revocation, admin + CLI + SQL paths, preserves counterparties' data.
- *Headroom:* no self-service data export (Art. 20 portability) (roadmap #7).

### Admin & analytics — 🟡
- Dashboard stats, user/job moderation, growth chart, reports queue.
- *Headroom:* analytics are basic counts; no funnel/cohort, no CSV export (roadmap #9).

### Cross-cutting — 🟡
- **Strengths:** consistent JSON errors, role/active middleware, soft deletes, DB-portable migrations, full test + CI pipeline, i18n scaffolding (ar/en), RTL-capable.
- *Headroom:* responses return raw models (no API Resource layer) — fine today (`$hidden` protects secrets) but a Resource layer would future-proof field control (roadmap #15). No observability/error tracking (roadmap #18).

---

## 2. Improvements applied in this pass

1. **Rate-limited all public read endpoints** (`/jobs`, `/jobs/{slug}`,
   `/professionals`, `/professionals/{id}`, `/skills`, `/subscription-plans`) at
   90 req/min/IP. Previously unthrottled → open to scraping and cheap DoS. The
   Stripe webhook stays unthrottled by design so event bursts aren't dropped.

*(Earlier passes in this engagement also fixed: attachment-only messaging,
PostJob verification-block handling, the lazy employer-profile create bug, three
SQLite-portability migration bugs, and the stale seeder columns.)*

---

## 3. Top 20 features to build next (prioritised)

### Tier 1 — high impact, build first
1. ~~**Full-text / engine-backed search**~~ ✅ **DONE** — MySQL FULLTEXT (boolean mode, relevance-ranked) with LIKE fallback + facet counts. See [`docs/SEARCH.md`](SEARCH.md).
2. ~~**Real-time messaging & notifications**~~ ✅ **DONE** — Laravel Reverb broadcasting (`MessageSent` + notification `broadcast` channel) with Echo client and polling fallback. See [`docs/REALTIME.md`](REALTIME.md). *(Future: typing/online presence.)*
3. **Two-factor authentication (2FA)** + login alerts — TOTP/email, especially for employers and admins.
4. **Billing history & invoices** — receipts, downloadable invoices, proration, dunning emails on failed charges, plan upgrade/downgrade.
5. **Notification preferences & digests** — per-type email/in-app toggles, daily/weekly job-match digest.

### Tier 2 — growth & engagement
6. **Interview calendar integration** — `.ics` invites, Google/Outlook sync, automated reminders, timezone-aware slots.
7. **GDPR data export (portability, Art. 20)** — self-service "download my data" (JSON/zip), complementing the existing erasure.
8. **Employer applicant pipeline (Kanban) + bulk actions** — drag between stages, bulk shortlist/reject, saved candidate lists.
9. **Advanced analytics & CSV export** — application funnels, time-to-hire, source breakdown, cohort retention, exportable reports.
10. **Saved searches & job alerts** — seekers save filters and get notified of new matches (powers #1 and #5).

### Tier 3 — trust, quality, scale
11. **Admin manual verification & trust tiers** — override/grant verified badges, company verification document review.
12. **Appeals & reporter feedback loop** — users see report outcomes; suspended users can appeal; reduces moderation disputes.
13. **AI-assisted matching & screening** — embeddings-based job↔candidate matching (upgrade `JobMatchingService`), résumé parsing, scam-text detection to auto-raise flags.
14. **Company pages & reviews** — public employer profiles with verified-employee reviews and ratings.
15. **API resource layer + public API & webhooks** — versioned responses, partner/ATS integrations, outbound webhooks for employers.

### Tier 4 — polish & platform
16. **Onboarding wizards & profile completeness nudges** — guided setup, completeness meter, gamified prompts to raise `profile_complete`.
17. **Multi-language content & full localisation** — translate UI strings (extend `i18n`), localised emails, currency/locale formatting.
18. **Observability & error tracking** — Sentry/Flare, structured logging, health/metrics endpoints, queue monitoring.
19. **Background queue hardening** — move notifications/emails to a real queue (Redis), retries, failed-job dashboard, scheduled cleanups (expire jobs, prune tokens).
20. **Accessibility & PWA** — full WCAG pass, keyboard nav audit, offline-capable PWA with push notifications for mobile users.

---

*Each roadmap item is independently shippable; Tier 1 delivers the most user-visible value per unit of effort.*
