<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class CheckAgent
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['agent', 'admin', 'owner', 'viewer'])) {
            abort(403, 'Access denied.');
        }
        return $next($request);
    }
}
