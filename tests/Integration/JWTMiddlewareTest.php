<?php

namespace Tests\Integration;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class JWTMiddlewareTest extends TestCase
{
    public function test_jwt_middleware_with_invalid_token()
    {
        // AuthService mocked to throw an authorization exception
        $token = 'invalid-jwt-token';
        Cache::shouldReceive('remember')->once()->andThrow(new \Illuminate\Auth\Access\AuthorizationException);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('/api/profile');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
