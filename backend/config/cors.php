<?php

return [

    /*
     * API routes that should have CORS headers applied.
     * The wildcard covers every /api/* endpoint.
     */
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
     * Set FRONTEND_URL in .env to your SPA origin.
     * Multiple origins: use a comma-separated list or override this array.
     *
     * In development the Vite proxy (localhost:5173 → localhost:8000) means
     * the browser never directly calls Laravel, so CORS is irrelevant locally.
     * This config matters in production / when calling the API directly.
     */
    // Support comma-separated list in FRONTEND_URL for multiple dev origins.
    // e.g. FRONTEND_URL=http://localhost:5173,http://localhost:4173
    'allowed_origins' => array_values(array_filter(
        array_map('trim', explode(',', env('FRONTEND_URL', 'http://localhost:5173,http://localhost:4173')))
    )),

    'allowed_origins_patterns' => [],

    /*
     * Must include Authorization so Bearer tokens are not stripped,
     * and Content-Type / Accept for JSON requests.
     */
    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 86400, // cache preflight for 24 h

    /*
     * false: we use Bearer tokens, not cookies — credentials (cookies) are
     * not needed and enabling this would require a non-wildcard origin list.
     */
    'supports_credentials' => false,

];
