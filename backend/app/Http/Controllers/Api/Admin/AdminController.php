<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;

/**
 * Base controller for all admin endpoints.
 *
 * DEFENSE IN DEPTH: Even though the `role:admin` middleware on the route group
 * already blocks non-admins, we add a second layer here. This ensures that if
 * the middleware is ever accidentally removed from a route (e.g. during a
 * refactor), the controller still refuses non-admin requests.
 *
 * All Admin controllers should extend this class, NOT the base Controller.
 */
abstract class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()?->isAdmin()) {
                abort(403, 'Admin access required.');
            }

            return $next($request);
        });
    }
}
