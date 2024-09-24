<?php

namespace Tests\Unit;

use App\Http\Middleware\AuthorizeProfileAccessMiddleware;
use App\Models\User;
use App\Services\AutzService;
use Database\Factories\UserFactory;
use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Tests\TestCase;

class AuthorizeProfileAccessMiddlewareTest extends TestCase
{
    public function test_access_profile_with_admin_permission_as_admin_success()
    {
        $autzServiceMock = $this->createMock(AutzService::class);
        $autzServiceMock->method('getRoles')->willReturn(new Response(200, [], json_encode(['roles' => ['admin']])));
        $middleware = new AuthorizeProfileAccessMiddleware($autzServiceMock);

        $user = User::factory()->make([
            'id' => 1,
            'username' => 'user',
            'email' => 'test_email@mail.com'
        ]);

        $request = Request::create('/api/profile/1', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $request->headers->set('Authorization', 'Bearer valid-token');

        $next = function ($req) {
            $this->assertEquals(1, $req->user()->id);
            return new HttpFoundationResponse('Passed through middleware', 200);
        };

        $response = $middleware->handle($request, $next);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_access_profile_with_admin_permission_as_no_admin_success()
    {
        $autzServiceMock = $this->createMock(AutzService::class);
        $autzServiceMock->method('getRoles')->willReturn(new Response(200, [], json_encode(['roles' => []])));
        $middleware = new AuthorizeProfileAccessMiddleware($autzServiceMock);

        $user = User::factory()->make([
            'id' => 1,
            'username' => 'user',
            'email' => 'test_email@mail.com'
        ]);

        $request = Request::create('/api/profile/1', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $request->headers->set('Authorization', 'Bearer valid-token');

        $next = function ($req) {
            $this->assertEquals(1, $req->user()->id);
            return new HttpFoundationResponse('Passed through middleware', 200);
        };

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Forbidden');
        $middleware->handle($request, $next);
    }

    /** ... */
}
