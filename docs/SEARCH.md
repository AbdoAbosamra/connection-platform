# Search (relevance-ranked, faceted)

Job and professional search are **relevance-ranked full-text** searches with
**facet counts**, replacing the old `LIKE '%term%'` scans.

## How it works

- **MySQL (production):** `FULLTEXT` indexes
  (`jobs_fulltext` on `title, description, requirements`; `seekers_fulltext` on
  `headline, bio, current_job_title, desired_job_title`, migration `000020`) are
  queried with `MATCH … AGAINST(… IN BOOLEAN MODE)`. A relevance score is selected
  and results are ordered **featured → relevance → recency**.
- **Prefix/partial matching:** each query word becomes `+word*` (boolean mode), so
  `engin` matches *Engineer* and `senior php` requires both terms.
- **SQLite (test DB):** the layer transparently falls back to an escaped `LIKE`
  scan over the same columns, so the suite runs with no MySQL dependency.
- **Driver detection** is automatic (`DB::connection()->getDriverName()`), and the
  FULLTEXT migration is MySQL-guarded.

Implemented in [`JobService`](../backend/app/Services/JobService.php) and
[`ProfessionalService`](../backend/app/Services/ProfessionalService.php).

## Facets

`GET /api/jobs` returns facet counts alongside the paginator (without changing
its shape):

```jsonc
{
  "data": [ /* jobs */ ],
  "current_page": 1, "last_page": 3, "total": 42,
  "facets": {
    "category":         { "Engineering": 30, "Design": 12 },
    "employment_type":  { "full_time": 38, "contract": 4 },
    "experience_level": { "senior": 20, "mid": 15, "entry": 7 }
  }
}
```

Facet counts honour the **search term** but ignore the per-dimension filters, so
the UI shows the full distribution for the query. The job search page renders the
counts inline on each filter option, e.g. *Full-time (38)*.

> Note: facet queries deliberately apply only the text-match `WHERE` (not the
> relevance `SELECT`), so the aggregate is valid under `ONLY_FULL_GROUP_BY`.

## Tested

Backend: `tests/Feature/PublicJobTest.php` (search match, facet counts, facets
reflect the query). Frontend: `src/stores/jobs.spec.js` (store populates
jobs/pagination/facets, strips blanks, empty-facets fallback). The MySQL FULLTEXT
path was verified live (`q=Laravel`, prefix `q=engin`).
