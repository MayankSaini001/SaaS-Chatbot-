<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Stripe PHP manual autoload
        $stripePath = base_path('vendor-manual/stripe-php/init.php');
        if (file_exists($stripePath)) {
            require_once $stripePath;
        }

        // Pusher PHP server manual autoload (pusher/pusher-php-server not in
        // composer.lock on this server; vendored manually so real-time
        // broadcasting — e.g. Internal Notes — works without composer access)
        $pusherPath = base_path('vendor-manual/pusher-php-server/init.php');
        if (file_exists($pusherPath)) {
            require_once $pusherPath;
        }
    }

    public function boot(): void
    {
        //
    }
}
