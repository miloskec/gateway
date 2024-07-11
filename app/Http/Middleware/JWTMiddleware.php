<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuthService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class JWTMiddleware
{
    public function __construct(protected readonly AuthService $authService) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            throw new AuthorizationException('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $cacheKey = generateJwtUserKey($token);

        $user = Cache::remember($cacheKey, config('jwt.ttl'), function () use ($token) {
            Log::channel('gateway')->info('JWT Middleware CREATING: '.$token);
            $response = $this->authService->verifyJWT($token);

            if ($response?->getStatusCode() !== Response::HTTP_OK) {
                throw new AuthorizationException('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }

            $userData = json_decode($response->getBody(), true)['data'];

            if (! $userData) {
                throw new AuthorizationException('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }

            // Convert the user data array to a User model instance
            return new User($userData);
        });

        Log::channel('gateway')->info('JWT Middleware USER: '.$user->id);
        // Attach the user model to the request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
