<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

    $middleware->validateCsrfTokens(except: [
        'stripe/webhook',
    ]);

    $middleware->append(\App\Http\Middleware\UpdateLastSeen::class);

    $middleware->alias([
        'admin' => \App\Http\Middleware\CheckAdmin::class,
        'agent' => \App\Http\Middleware\CheckAgent::class,
        'tenant' => \App\Http\Middleware\CheckTenant::class,
        'owner' => \App\Http\Middleware\CheckOwner::class,
		'subscription' => \App\Http\Middleware\CheckSubscription::class,
        'not_viewer' => \App\Http\Middleware\BlockViewer::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();