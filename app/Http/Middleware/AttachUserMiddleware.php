<?php

namespace App\Http\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

class AttachUserMiddleware
{
    public static function handle(): callable
    {
        return function (callable $handler): callable {
            return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
                // Retrieve user from Laravel's request scope
                $user = request()->user(); // Assuming user is authenticated

                if ($user) {
                    // Modify the request to include user data previously collected from the authentication api
                    $request = $request->withHeader('Authorization', 'Bearer ' . request()->bearerToken());
                    $request = $request->withHeader('X-User-Email', $user->email);
                    $request = $request->withHeader('X-User-Id', $user->id);
                }

                return $handler($request, $options);
            };
        };
    }
}
