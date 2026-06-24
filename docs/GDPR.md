# GDPR — Right to be Forgotten

RemoteArena implements **GDPR Article 17** erasure of a user's personal data.

## Approach: pseudonymisation, not hard deletion

`messages.sender_id` and several other foreign keys are defined `ON DELETE
CASCADE`, so physically deleting a `users` row would also destroy the **other
party's** chat history and hiring records. Instead the erasure keeps the row as
an anonymised **tombstone** — all PII scrubbed, the account soft-deleted so it can
never be queried or logged into — and anonymises everything the person authored,
while leaving rows owned by innocent counterparts intact and referentially valid.

Everything runs in a single DB transaction
([`UserErasureService`](../backend/app/Services/UserErasureService.php)).

## What gets erased

| Table | Action |
| --- | --- |
| `users` | Pseudonymised (`name`→"Deleted User", `email`→`deleted_{id}@anonymized.invalid`, phone/avatar/country/timezone nulled, password randomised), `is_active=false`, **soft-deleted**. |
| `personal_access_tokens` | Deleted — all sessions revoked. |
| `notifications` | The user's own feed deleted. |
| `job_seeker_profiles` | All PII nulled, skills detached, `profile_complete=false` (removed from the public directory). |
| `job_applications` (seeker) | `cover_letter` + `resume_snapshot` scrubbed — **row kept** for the employer's record. |
| `saved_jobs` | Deleted (the user's own data). |
| `employer_profiles` | Company PII anonymised → "Deleted Company". |
| `jobs` (employer) | Set to `closed` — **rows kept** so applicants' history survives. |
| `messages` | Rows authored by the user have `body`→"[User Account Deleted]" and attachment removed; **the other participant's messages are untouched**. |
| Uploaded files | Avatar, logo, resume, and message attachments deleted from the storage disk. |
| `reports`, `conversations`, `interview_schedules`, `employer_subscriptions` | Kept (legitimate-interest / legal-basis records); they reference the anonymised tombstone, so they expose no PII. |

## How to run it

**Admin UI** — User detail page → **"Erase Data (GDPR)"** (with a confirm step).

**API** — `DELETE /api/admin/users/{id}/forget` (admin only; cannot target
yourself or another admin).

**CLI** — on the server:

```bash
php artisan gdpr:forget 123          # prompts for confirmation
php artisan gdpr:forget 123 --force  # unattended
```

**Direct DBA fallback** — for a manual erasure straight in phpMyAdmin / Hostinger,
use [`docs/sql/gdpr_forget_user.sql`](sql/gdpr_forget_user.sql). It mirrors the
service and is transaction-wrapped; remember it cannot delete the storage files,
so remove those separately.

## Guarantees (covered by tests)

`tests/Feature/Admin/UserErasureTest.php` verifies: PII anonymisation, content
scrubbing, file deletion, session revocation, that the erased user can no longer
log in, that **other** users' messages/applications are preserved, and that an
admin cannot erase themselves or another admin.
