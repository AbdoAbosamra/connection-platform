# Moderation layer (community flagging)

RemoteArena's flagging system is the **`reports`** table (polymorphic target →
user or job). This document describes the moderation layer built on top of it:
auto-prioritisation, admin actions, and the `moderation_logs` accountability
trail. A separate `flags` table was deliberately **not** created — it would
duplicate `reports` and split moderation data across two places.

## Final schema (MySQL 8)

`reports` (the flags table — created by earlier migrations, extended by
`000019` with `priority` + the target index):

```sql
CREATE TABLE reports (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reporter_id      BIGINT UNSIGNED NOT NULL,                 -- who flagged
    reportable_type  VARCHAR(255)    NOT NULL,                 -- App\Models\User | App\Models\Job
    reportable_id    BIGINT UNSIGNED NOT NULL,                 -- the target id
    reason           VARCHAR(255)    NOT NULL,                 -- scam | harassment | phishing | duplicate | …
    details          TEXT            NULL,
    status           ENUM('open','under_review','resolved','dismissed') NOT NULL DEFAULT 'open',
    priority         ENUM('low','normal','high','critical')             NOT NULL DEFAULT 'normal',
    resolved_by      BIGINT UNSIGNED NULL,                     -- admin who closed it
    resolution_note  TEXT            NULL,
    resolved_at      TIMESTAMP       NULL,
    created_at       TIMESTAMP       NULL,
    updated_at       TIMESTAMP       NULL,
    PRIMARY KEY (id),
    KEY reports_reportable_type_reportable_id_index (reportable_type, reportable_id),
    KEY reports_status_created_at_index (status, created_at),
    KEY reports_target_status (reportable_type, reportable_id, status),
    CONSTRAINT reports_reporter_id_fk  FOREIGN KEY (reporter_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT reports_resolved_by_fk  FOREIGN KEY (resolved_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

`moderation_logs` (admin action audit trail — created by `000019`):

```sql
CREATE TABLE moderation_logs (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    moderator_id  BIGINT UNSIGNED NOT NULL,                    -- admin who acted
    user_id       BIGINT UNSIGNED NULL,                        -- affected user (if any)
    report_id     BIGINT UNSIGNED NULL,                        -- triggering flag (if any)
    action        ENUM('warning','suspension','reinstatement','dismissal','content_removed','note') NOT NULL,
    notes         TEXT  NULL,
    metadata      JSON  NULL,                                  -- {reason, target_type, target_id}
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY moderation_logs_user_id_created_at_index (user_id, created_at),
    KEY moderation_logs_action_index (action),
    KEY moderation_logs_moderator_id_index (moderator_id),
    CONSTRAINT moderation_logs_moderator_fk FOREIGN KEY (moderator_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT moderation_logs_user_fk      FOREIGN KEY (user_id)      REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT moderation_logs_report_fk    FOREIGN KEY (report_id)    REFERENCES reports (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## "Most frequently flagged job posts" — why it's efficient

```sql
SELECT reportable_id,
       COUNT(*)                  AS flags,
       SUM(status = 'open')      AS open_flags,
       MAX(created_at)           AS last_flagged
  FROM reports
 WHERE reportable_type = 'App\\Models\\Job'
   AND status = 'open'
 GROUP BY reportable_id
 ORDER BY flags DESC
 LIMIT 20;
```

The composite index **`reports_target_status (reportable_type, reportable_id,
status)`** is a *covering* index for this query: MySQL uses the leading
`reportable_type` + `status` columns to seek straight to the Job rows, and
because `reportable_id` is the next indexed column the `GROUP BY reportable_id`
is satisfied by an index scan — **no temporary table, no filesort, no row
look-ups into the base table**. It scales to millions of flags because the work
is proportional to the number of *open Job flags*, not the whole table.

Served by `GET /api/admin/moderation/most-flagged?type=job`
([`ModerationService::mostFlagged`](../backend/app/Services/ModerationService.php)).

## Auto-prioritisation (catching bad actors automatically)

On every new flag ([`ModerationService::prioritiseNewFlag`](../backend/app/Services/ModerationService.php)):

1. **Reason → priority**: high-harm reasons (`scam`, `phishing`, `harassment`,
   `hate_speech`, `threats`, `fake_listing`, `money_laundering`, `fake_profile`,
   `resume_fraud`) start at `high`.
2. **Repeat-offender escalation**: once a single target accumulates
   `AUTO_ESCALATE_THRESHOLD` (3) open flags, *all* its open flags jump to
   `critical`. The admin reports list is ordered `critical → high → normal → low`,
   so repeat offenders surface at the top automatically.

## Admin actions & accountability

`POST /api/admin/reports/{report}/action` with `{ action, notes }`:

| action | effect |
| --- | --- |
| `warning` | resolves the flag, emails + notifies the user |
| `suspension` | deactivates the user (`is_active=false`), resolves the flag, notifies |
| `reinstatement` | reactivates a suspended user |
| `dismissal` | marks the flag `dismissed` |
| `content_removed` | soft-deletes the reported job, resolves the flag |
| `note` | internal note only, no state change |

Every action writes one row to `moderation_logs`. The full history is queryable
at `GET /api/admin/moderation/logs` (filter by `action` or `user_id`).

The admin **Reports** page renders priority badges and per-flag action buttons
(Dismiss / Warn / Remove / Suspend). Covered by
`tests/Feature/Admin/ModerationTest.php`.
