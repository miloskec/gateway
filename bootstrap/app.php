<?php

use App\Http\Middleware\CorsMiddleware;
use App\Providers\GuzzleServiceProvider;
use App\Providers\RateLimitServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        GuzzleServiceProvider::class,
        RateLimitServiceProvider::class,
        // Other Service Providers
    ])
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (Middleware $middleware) {
            $middleware->prepend(
                CorsMiddleware::class
            );
        }
    )->withExceptions(function (Exceptions $exceptions) {})
    ->create();
