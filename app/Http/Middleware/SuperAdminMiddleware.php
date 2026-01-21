<?php

namespace App\Http\Middleware;

use App\Models\Scopes\TenantScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SuperAdminMiddleware
 * 
 * Allows super-admins to bypass TenantScope and access all company data.
 * Used for platform-level administration.
 * 
 * Usage: Route::...->middleware('super-admin');
 */
class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verify super-admin status
        if (!$user->is_super_admin) {
            abort(403, 'Super-Admin access required.');
        }

        // Disable TenantScope for this request
        // This allows super-admin to see all data from all companies
        app()->instance('disable_tenant_scope', true);

        return $next($request);
    }
}
