<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Admin ka last_seen update mat karo — widget sirf agents/owners check karta hai
            if ($user->role !== 'admin') {
                // Sirf tab update karo jab last_seen 60 second se purani ho
                // (har request pe DB hit avoid karo)
                if (!$user->last_seen || $user->last_seen->lt(now()->subSeconds(60))) {
                    $user->updateQuietly([
                        'is_online' => true,
                        'last_seen' => now(),
                    ]);
                }
            }
        }

        return $next($request);
    }
}
