<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class RouteMiddlewareList extends Command
{
    protected $signature = 'route:middleware-list';

    protected $description = 'List all routes with their middleware';

    public function handle()
    {
        $routes = Route::getRoutes();

        $this->table(
            ['Method', 'URI', 'Name', 'Action', 'Middleware'],
            collect($routes)->map(function ($route) {
                return [
                    'Method' => implode('|', $route->methods),
                    'URI' => $route->uri,
                    'Name' => $route->getName(),
                    'Action' => $route->getActionName(),
                    'Middleware' => implode(', ', $route->middleware()),
                ];
            })->toArray()
        );
    }
}
