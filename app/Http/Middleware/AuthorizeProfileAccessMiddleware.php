<?php

namespace App\Http\Middleware;

use App\Services\AutzService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
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
        $user = $request->user();
        $profileId = (int) $request->route('id');

        //$cacheKey = "roles_{$user->username}_{$user->email}";

        $response = $this->autzService->getRoles([
            'username' => $user->username,
            'email' => $user->email,
        ]);

        $rolesData = json_decode($response->getBody(), true);

        //Log::channel('gateway')->info($rolesData);
        // Check if user is admin or the owner of the profile
        if (
            in_array('admin', $rolesData['roles']) || // ToDo: If the roles data structure grows we will use more efficient data structure or method for role checking.
            $user->id === $profileId
        ) {
            return $next($request);
        }

        throw new AuthorizationException('Forbidden', Response::HTTP_FORBIDDEN);
    }
}
