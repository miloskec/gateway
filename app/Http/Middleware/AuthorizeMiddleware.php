<?php

namespace App\Http\Middleware;

use App\Services\AutzService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeMiddleware
{
    public function __construct(protected readonly AutzService $autzService) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $requiredPermission): Response
    {
        $username = $request->user()->username;
        $email = $request->user()->email;

        $response = $this->autzService->checkPermissions([
            'username' => $username,
            'email' => $email,
        ]);

        $permissionsData = json_decode($response->getBody(), true);

        if (
            in_array($requiredPermission, array_column($permissionsData['all_permissions'], 'name')) ||
            in_array('admin', $permissionsData['roles'])
        ) {
            return $next($request);
        }

        throw new AuthorizationException('Forbidden', Response::HTTP_FORBIDDEN);
    }
}
