<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'owner') {
            abort(403, 'Access denied. Owner only.');
        }

        return $next($request);
    }
}