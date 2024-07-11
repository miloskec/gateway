<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Request;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AttachUserMiddleware
{
    public static function handle(): callable
    {
        return function (callable $handler): callable {
            return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
                // Retrieve user from Laravel's request scope
                $user = request()->user(); // Assuming user is authenticated

                if ($user) {
                    // Modify the request to include user data
                    $request = $request->withHeader('X-User-Email', $user->email);
                }

                return $handler($request, $options);
            };
        };
    }
}
