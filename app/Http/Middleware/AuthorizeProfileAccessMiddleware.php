<?php

namespace App\Http\Middleware;

use App\Services\AutzService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeProfileAccessMiddleware
{
    public function __construct(protected readonly AutzService $autzService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Log::channel('gateway')->info('Middleware invoked');
        $user = $request->user();
        /* if (!$user) {
            Log::channel('gateway')->error('No authenticated user found');
            throw new AuthorizationException('No authenticated user found', 403);
        } */
        $profileId = $request->route('id');

        //$cacheKey = "roles_{$user->username}_{$user->email}";

        $response = $this->autzService->getRoles([
            'username' => $user->username,
            'email' => $user->email
        ]);

        $rolesData =json_decode($response->getBody(), true);
        Log::channel('gateway')->info($rolesData);
        // Check if user is admin or the owner of the profile
        if (in_array('admin', $rolesData['roles']) || $user->id == $profileId) {
            return $next($request);
        }

        throw new AuthorizationException('Forbidden', 403);
    }
}
