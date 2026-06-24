-- ============================================================================
--  RemoteArena — GDPR "Right to be Forgotten" erasure (raw MySQL fallback)
--
--  Prefer the application paths, which keep everything in sync and audited:
--      DELETE /api/admin/users/{id}/forget        (admin UI button)
--      php artisan gdpr:forget {id}               (server terminal)
--
--  Use this script only for a direct DBA erasure in phpMyAdmin / Hostinger.
--  It mirrors App\Services\UserErasureService exactly: pseudonymise the users
--  row (tombstone), scrub everything the person authored, and PRESERVE rows that
--  belong to innocent counterparts. FOREIGN_KEY_CHECKS stay ON — ordering is safe.
--  The role-specific statements that don't apply simply match 0 rows.
-- ============================================================================

-- The only input. Change 123 to the target user id.
SET @target_user_id := 123;

START TRANSACTION;

-- Resolve the user's profile ids once (NULL if they don't have that profile).
SET @emp_profile_id    := (SELECT id FROM employer_profiles  WHERE user_id = @target_user_id);
SET @seeker_profile_id := (SELECT id FROM job_seeker_profiles WHERE user_id = @target_user_id);

-- 1. Revoke all sessions and delete the user's private notification feed.
DELETE FROM personal_access_tokens
 WHERE tokenable_type = 'App\\Models\\User' AND tokenable_id = @target_user_id;

DELETE FROM notifications
 WHERE notifiable_type = 'App\\Models\\User' AND notifiable_id = @target_user_id;

-- 2. Anonymise the content of messages the user authored (keep the rows so the
--    other participant's conversation history survives).
UPDATE messages
   SET body = '[User Account Deleted]', attachment = NULL
 WHERE sender_id = @target_user_id;

-- 3. JOB SEEKER content (no-ops if @seeker_profile_id IS NULL).
UPDATE job_applications
   SET cover_letter = NULL, resume_snapshot = NULL
 WHERE job_seeker_profile_id = @seeker_profile_id;

DELETE FROM saved_jobs        WHERE job_seeker_profile_id = @seeker_profile_id;
DELETE FROM job_seeker_skills WHERE job_seeker_profile_id = @seeker_profile_id;

UPDATE job_seeker_profiles
   SET headline = NULL, bio = NULL, resume = NULL, portfolio_url = NULL,
       linkedin_url = NULL, github_url = NULL, current_city = NULL,
       current_country = NULL, nationality = NULL, current_job_title = NULL,
       desired_job_title = NULL, desired_salary_min = NULL, desired_salary_max = NULL,
       profile_complete = 0, is_featured = 0
 WHERE id = @seeker_profile_id;

-- 4. EMPLOYER content (no-ops if @emp_profile_id IS NULL).
--    Close listings (keep the rows — applicants reference them) and scrub PII.
UPDATE jobs
   SET status = 'closed', is_featured = 0
 WHERE employer_profile_id = @emp_profile_id;

UPDATE employer_profiles
   SET company_name = 'Deleted Company',
       company_slug = CONCAT('deleted-company-', id),
       description = NULL, industry = NULL, company_size = NULL, website = NULL,
       logo = NULL, headquarters_city = NULL, headquarters_state = NULL,
       linkedin_url = NULL, twitter_url = NULL, founded_year = NULL,
       is_verified = 0, verified_at = NULL, verification_method = NULL,
       linkedin_id = NULL, is_featured = 0
 WHERE id = @emp_profile_id;

-- 5. Pseudonymise the users row and soft-delete it (deleted_at), so it can never
--    be queried or logged into while every foreign key remains valid.
UPDATE users
   SET name = 'Deleted User',
       email = CONCAT('deleted_', id, '@anonymized.invalid'),
       phone = NULL, avatar = NULL, country = NULL, timezone = NULL,
       email_verified_at = NULL, is_active = 0,
       deleted_at = NOW()
 WHERE id = @target_user_id;

-- Review the result, then COMMIT. If anything looks wrong, run ROLLBACK; instead.
SELECT
    (SELECT name  FROM users WHERE id = @target_user_id)                                          AS user_name,
    (SELECT email FROM users WHERE id = @target_user_id)                                          AS user_email,
    (SELECT COUNT(*) FROM messages WHERE sender_id = @target_user_id
                                     AND body <> '[User Account Deleted]')                         AS unscrubbed_msgs,
    (SELECT COUNT(*) FROM saved_jobs WHERE job_seeker_profile_id = @seeker_profile_id)            AS saved_jobs_left;

COMMIT;
-- ROLLBACK;   -- <- run this instead of COMMIT to abort.

-- NB: physical files (avatar, logo, resume, attachments) live on the storage
-- disk, not in MySQL. The application paths delete them automatically; a pure
-- SQL erasure must remove the corresponding files from storage/app/public/* too.
