<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->tenant_id) {
            return redirect()->route('billing.pricing');
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('billing.pricing')
                ->with('error', 'Please activate a subscription first.');
        }

        // Trial period check
        if ($tenant->plan === 'trial' && $tenant->trial_ends_at) {
            if (now()->lte($tenant->trial_ends_at)) {
                // Trial still active — allow access
                return $next($request);
            } else {
                // Trial expired — deactivate and redirect
                $tenant->update(['is_active' => false, 'plan' => 'basic']);
                return redirect()->route('billing.pricing')
                    ->with('error', 'Your 7-day free trial has expired. Please subscribe to continue.');
            }
        }

        if (!$tenant->is_active) {
            return redirect()->route('billing.pricing')
                ->with('error', 'Please activate a subscription first.');
        }

        return $next($request);
    }
}