<?php

namespace App\Providers;

use App\Http\Middleware\AuthorizeMiddleware;
use App\Http\Middleware\AuthorizeProfileAccessMiddleware;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MiddlewareAliasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('jwt.auth', JWTMiddleware::class);
        $router->aliasMiddleware('profile.access', AuthorizeProfileAccessMiddleware::class);
        $router->aliasMiddleware('authorize', AuthorizeMiddleware::class);
    }
}
