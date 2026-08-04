<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->tenant_id) {
            abort(403, 'No tenant found.');
        }

        return $next($request);
    }
}