<?php

namespace Tests\Unit;

use App\Http\Middleware\JWTMiddleware;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class JWTMiddlewareTest extends TestCase
{
    public function test_handle_with_no_token()
    {
        $authServiceMock = $this->createMock(AuthService::class);
        $middleware = new JWTMiddleware($authServiceMock);

        $request = Request::create('/api/profile', 'GET');

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $middleware->handle($request, function () {
        });
    }

    public function test_handle_with_valid_token()
    {
        $authServiceMock = $this->createMock(AuthService::class);
        $authServiceMock->method('verifyJWT')->willReturn(new Response(json_encode(['data' => ['id' => 1, 'email' => 'user@example.com']])));
        Cache::shouldReceive('remember')->andReturn(new User(['id' => 1, 'email' => 'user@example.com']));

        $middleware = new JWTMiddleware($authServiceMock);
        $request = Request::create('/api/profile', 'GET');
        $request->headers->set('Authorization', 'Bearer valid-token');

        $next = function ($req) {
            $this->assertEquals(1, $req->user()->id);
            return new Response('Passed through middleware', 200);
        };

        $response = $middleware->handle($request, $next);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
