# Deploying RemoteArena to production (remotearena.io)

Target: Hostinger VPS · Ubuntu 24.04 · CloudPanel · PHP 8.3 · MySQL.
Architecture: **one domain** — Nginx serves the built Vue SPA and proxies
`/api`, `/storage`, `/up`, `/broadcasting`, `/telescope` to the Laravel backend.

---

## 0. Prerequisites (do once)

1. **DNS** — in your domain registrar, point `remotearena.io` to the server:
   - `A` record: `@`   → `2.25.66.54`
   - `A` record: `www` → `2.25.66.54`
   Wait for it to propagate (`ping remotearena.io` should show the IP).

2. **Server packages** (most ship with CloudPanel; install whatever is missing):
   ```bash
   php -v            # must be 8.3+
   node -v           # need 20+  → if missing: install via nvm or nodesource
   composer -V
   git --version
   ```

---

## 1. Create the site + database in CloudPanel

- **Sites → Add Site → PHP** → domain `remotearena.io`, PHP 8.3, create a site user
  (note the username — paths below use `SITE_USER`).
- **Databases → Add Database** → name `remotearena`, user `remotearena`, strong password.
- **SSL/TLS → Let's Encrypt** → issue a certificate for `remotearena.io` (+ `www`).

The site root is typically: `/home/SITE_USER/htdocs/remotearena.io/`

---

## 2. Get the code onto the server

```bash
cd /home/SITE_USER/htdocs/remotearena.io
git clone https://github.com/AbdoAbosamra/connection-platform.git .
```

You should now have `backend/` and `frontend/` here.

---

## 3. Backend (Laravel)

```bash
cd /home/SITE_USER/htdocs/remotearena.io/backend

composer install --no-dev --optimize-autoloader

cp .env.production.example .env
nano .env                      # fill DB_PASSWORD, MAIL_*, etc.
php artisan key:generate

php artisan migrate --force
php artisan db:seed --force    # seeds skills + subscription plans (+ demo data)
php artisan storage:link

# Create YOUR admin (replace email)
php artisan admin:create --email=owner@remotearena.io --name="Owner"

# Cache config/routes/views for speed
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions so the web user can write logs/cache/uploads
sudo chown -R SITE_USER:SITE_USER storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

> After seeding, **delete or re-secure the demo admin** `admin@connextion.io`
> (weak password): `php artisan tinker --execute="App\Models\User::where('email','admin@connextion.io')->forceDelete();"`

---

## 4. Frontend (Vue)

```bash
cd /home/SITE_USER/htdocs/remotearena.io/frontend
npm ci
npm run build                  # outputs dist/
```

`frontend/.env` already uses `VITE_API_BASE_URL=/api`, so the SPA calls the API
on the same domain — no change needed.

---

## 5. Nginx — serve SPA + proxy the API

In CloudPanel → your site → **Vhost**, set the config below (adjust `SITE_USER`
and the PHP-FPM socket if different). CloudPanel manages the SSL/listen blocks;
paste the `location` rules into the server block.

```nginx
root /home/SITE_USER/htdocs/remotearena.io/frontend/dist;
index index.html;

# Laravel public dir (PHP backend)
set $laravel /home/SITE_USER/htdocs/remotearena.io/backend/public;

# API + framework routes → Laravel
location ~ ^/(api|up|telescope|broadcasting|storage)(/|$) {
    root /home/SITE_USER/htdocs/remotearena.io/backend/public;
    try_files $uri /index.php?$query_string;
}

location ~ \.php$ {
    root /home/SITE_USER/htdocs/remotearena.io/backend/public;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;   # match your PHP-FPM socket
}

# Vue SPA — all other routes fall back to index.html
location / {
    try_files $uri $uri/ /index.html;
}
```

Reload: `sudo systemctl reload nginx` (or use CloudPanel's reload).

---

## 6. Security hardening (do not skip)

```bash
# Firewall: allow SSH + web only. DO NOT run `ufw disable`.
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

Checklist:
- [ ] `APP_DEBUG=false` and `APP_ENV=production` in `.env`
- [ ] `TELESCOPE_ENABLED=false`
- [ ] Demo admin `admin@connextion.io` removed / password changed
- [ ] Strong unique `DB_PASSWORD` (not the dev password)
- [ ] HTTPS forced (CloudPanel "Force HTTPS")
- [ ] A queue worker running if you use queued mail: `php artisan queue:work`

---

## 7. Verify

- `https://remotearena.io` → site loads (logo, login)
- `https://remotearena.io/api/jobs` → JSON response
- Register a **company** with a Gmail address → rejected ✅
- Log in as your admin → `/admin/dashboard`

---

## 8. Future deploys (automatic)

Once the server is set up, every push to `main` auto-deploys via
`.github/workflows/deploy.yml` — add these repo secrets
(**Settings → Secrets and variables → Actions**):

| Secret | Value |
|--------|-------|
| `SSH_HOST` | `2.25.66.54` |
| `SSH_USER` | `SITE_USER` |
| `SSH_KEY`  | the private SSH key authorized on the server |
| `DEPLOY_PATH` | `/home/SITE_USER/htdocs/remotearena.io` |
| `SSH_PORT` | `22` (omit if default) |

The workflow rebuilds the frontend, ships `dist/`, pulls the backend, runs
`composer install --no-dev`, `migrate --force`, and rebuilds caches.
