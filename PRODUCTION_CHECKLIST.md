# 🚀 RemoteArena — Production Launch Checklist (remotearena.io)

Tick every box top to bottom. Each step has a **Verify** line — do not move on
until it passes. Server: Hostinger VPS · `2.25.66.54` · Ubuntu 24.04 · CloudPanel.

---

## Phase 0 — Pre-flight (local / GitHub) ✅ already done this session

- [x] Backend tests green — `php artisan test` → 152 passed
- [x] Code style green — `./vendor/bin/pint --test` → passed
- [x] Frontend green — `npm run lint`, `npm run test:run`, `npm run build`
- [x] Everything pushed to `main`
- [ ] **GitHub Actions CI is green** — open
      `https://github.com/AbdoAbosamra/connection-platform/actions`,
      confirm the latest **CI** run shows ✅ for both *Backend (Laravel)* and *Frontend (Vue)*.
      **Verify:** no red ❌ on the latest commit.

---

## Phase 1 — Domain & DNS

- [ ] Domain `remotearena.io` is registered (you own it).
- [ ] Add DNS records at the registrar:
  - [ ] `A`  `@`   → `2.25.66.54`
  - [ ] `A`  `www` → `2.25.66.54`
- [ ] Wait for propagation (5–30 min).
      **Verify:** `ping remotearena.io` returns `2.25.66.54`
      (or check https://dnschecker.org).

---

## Phase 2 — CloudPanel: site + database + SSL

- [ ] **Sites → Add Site → Create a PHP Site** → `remotearena.io`, PHP **8.3**.
      Note the **site user** (used as `SITE_USER` below).
- [ ] **Databases → Add Database** → name `remotearena`, user `remotearena`,
      strong password (save it).
- [ ] **SSL/TLS → Let's Encrypt** → issue cert for `remotearena.io` + `www`.
- [ ] Enable **Force HTTPS**.
      **Verify:** `https://remotearena.io` shows CloudPanel's default page over HTTPS (padlock).

---

## Phase 3 — Get the code on the server (SSH)

- [ ] `ssh root@2.25.66.54` (or the site user)
- [ ] `php -v` → 8.3+ · `node -v` → 20+ · `composer -V` · `git --version`
      (install any that are missing)
- [ ] ```bash
      cd /home/SITE_USER/htdocs/remotearena.io
      git clone https://github.com/AbdoAbosamra/connection-platform.git .
      ```
      **Verify:** `ls` shows `backend/`, `frontend/`, `DEPLOYMENT.md`.

---

## Phase 4 — Backend (Laravel)

- [ ] `cd backend && composer install --no-dev --optimize-autoloader`
- [ ] `cp .env.production.example .env`
- [ ] Edit `.env` (`nano .env`): set `DB_PASSWORD`, `MAIL_*`, confirm
      `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://remotearena.io`.
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --force`
      **Verify:** "Migrated" lines, no errors.
- [ ] `php artisan db:seed --force` (skills + subscription plans)
- [ ] `php artisan storage:link`
- [ ] `php artisan admin:create --email=owner@remotearena.io --name="Owner"`
      → **save the printed password.**
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Permissions:
      ```bash
      chown -R SITE_USER:SITE_USER storage bootstrap/cache
      chmod -R ug+rwX storage bootstrap/cache
      ```
      **Verify:** `php artisan about` runs without error and shows `Environment: production`.

---

## Phase 5 — Frontend (Vue)

- [ ] `cd ../frontend && npm ci`
- [ ] `npm run build`
      **Verify:** `ls dist/` shows `index.html` + `assets/`.

---

## Phase 6 — Nginx (serve SPA + proxy /api)

- [ ] In CloudPanel → site → **Vhost**, add the `location` rules from
      [`DEPLOYMENT.md`](DEPLOYMENT.md#5-nginx--serve-spa--proxy-the-api)
      (set `SITE_USER` + correct PHP-FPM socket).
- [ ] Reload nginx (CloudPanel button or `sudo systemctl reload nginx`).
      **Verify:**
      - `https://remotearena.io` → the RemoteArena site (logo, login) loads.
      - `https://remotearena.io/api/jobs` → JSON (not HTML / 404).

---

## Phase 7 — Security hardening

- [ ] Firewall (do **NOT** `ufw disable`):
      ```bash
      sudo ufw allow 22/tcp && sudo ufw allow 80/tcp && sudo ufw allow 443/tcp
      sudo ufw enable
      ```
- [ ] `APP_DEBUG=false` and `TELESCOPE_ENABLED=false` in `.env` (re-check).
- [ ] Remove the weak demo admin:
      ```bash
      php artisan tinker --execute="App\Models\User::where('email','admin@connextion.io')->forceDelete();"
      ```
- [ ] Confirm `DB_PASSWORD` is strong and unique (not the dev `07775000`).
      **Verify:** `curl -s https://remotearena.io/api/nonexistent` returns JSON
      `{"message":"..."}` with **no stack trace**.

---

## Phase 8 — Smoke test the live site

- [ ] Home page + Browse Jobs load, seeded jobs appear.
- [ ] Register a **company** with `test@gmail.com` → **rejected** (business-email rule). ✅
- [ ] Register a company with a real business email → succeeds.
- [ ] Register a **job seeker** with any email → succeeds.
- [ ] Log in as your admin → `/admin/dashboard` loads with real stats.
- [ ] (If used) post a job as a verified employer → appears on `/jobs`.

---

## Phase 9 — GitHub Actions CD (auto-deploy on every push)

- [ ] Generate a deploy SSH key **on the server** and authorize it:
      ```bash
      ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N ""
      cat ~/.ssh/deploy_key.pub >> ~/.ssh/authorized_keys
      cat ~/.ssh/deploy_key            # copy the PRIVATE key
      ```
- [ ] In GitHub → **Settings → Secrets and variables → Actions**, add:
  - [ ] `SSH_HOST` = `2.25.66.54`
  - [ ] `SSH_USER` = `SITE_USER`
  - [ ] `SSH_KEY`  = the private key contents
  - [ ] `DEPLOY_PATH` = `/home/SITE_USER/htdocs/remotearena.io`
  - [ ] `SSH_PORT` = `22` (only if non-default)
- [ ] Trigger it: **Actions → Deploy to VPS → Run workflow** (manual dispatch).
      **Verify:** the run is **green** and the *Deploy backend* step shows the
      git pull + migrate output. (Before secrets were set it skipped green —
      now it should actually deploy.)
- [ ] Confirm the pipeline order: push to `main` → **CI** runs → on success →
      **Deploy to VPS** runs automatically (deploy is gated on CI success).
      **Verify:** make a tiny commit, watch CI go green, then Deploy fire.

---

## Phase 10 — Go-live

- [ ] HTTPS forced, padlock on every page.
- [ ] A backup/snapshot taken in Hostinger (Backups & Monitoring).
- [ ] (Recommended) queue worker for emails: a CloudPanel/systemd service running
      `php artisan queue:work --tries=3`.
- [ ] 🎉 Announce — `https://remotearena.io` is live.

---

### Quick reference

| Thing | Value |
|---|---|
| Server IP | `2.25.66.54` |
| Repo | `https://github.com/AbdoAbosamra/connection-platform` |
| Deploy path | `/home/SITE_USER/htdocs/remotearena.io` |
| Full guide | [`DEPLOYMENT.md`](DEPLOYMENT.md) |
| Prod env template | [`backend/.env.production.example`](backend/.env.production.example) |
