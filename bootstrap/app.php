<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'owner' => \App\Http\Middleware\OwnerMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'super-admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'subscription.active' => \App\Http\Middleware\CheckSubscriptionActive::class,
        ]);

        // Add security middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\BlockSuspiciousRequests::class,
            \App\Http\Middleware\LogUserActivity::class,
            \App\Http\Middleware\CheckSubscriptionActive::class, // Subscription guard
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
