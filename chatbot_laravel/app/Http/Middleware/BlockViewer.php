<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockViewer
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'viewer') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Viewers have read-only access.'], 403);
            }
            abort(403, 'Viewers have read-only access.');
        }

        return $next($request);
    }
}
