<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckSubscriptionActive Middleware
 * 
 * Blocks access if company subscription is inactive.
 * Redirects to "Account Suspended" page.
 * 
 * Usage: Applied to tenant route group.
 */
class CheckSubscriptionActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for guests
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Super-admins bypass this check
        if ($user->is_super_admin) {
            return $next($request);
        }

        // Check if user has a company
        if (!$user->company_id) {
            return $next($request);
        }

        // Load company and check is_active
        $company = $user->company;
        
        if (!$company || !$company->is_active) {
            // Store reason in session if available
            $reason = $company?->suspension_reason ?? 'Your account has been suspended.';
            
            return redirect()
                ->route('subscription.suspended')
                ->with('suspension_reason', $reason);
        }

        return $next($request);
    }
}
