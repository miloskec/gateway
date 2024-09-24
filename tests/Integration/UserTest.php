<?php

namespace Tests\Integration;

use App\Services\AuthService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * This test uses a mocked HTTP client to simulate interactions with another microservice. 
     * It tests how the application's service (AuthService) integrates and processes the mocked response, 
     * it checks the integration of application components rather than interacting with a live API.
     */
    public function test_admin_registration_success(): void
    {
        $mock = new MockHandler([
            new Response(201, [], json_encode([
                "data" => [
                    "id" => 2,
                    "email" => "mail_from_fake@example.com",
                    "username" => "username_from_fake",
                    "full_name" => "Microservice Admin",
                    "created_at" => "2024-07-03 11:37:34",
                    "updated_at" => "2024-07-03 11:37:34"
                ],
                "status" => "success",
                "message" => "User data retrieved successfully."
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->app->instance(Client::class, $client);

        $authService = new AuthService($client);
        $response = $authService->register([/* whatever array of data */]);

        // Convert Guzzle response to Laravel response so we could use Laravel's assertion methods
        $laravelResponse = new LaravelResponse(
            $response->getBody()->getContents(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
        // Create a test response from the Laravel response
        $response = TestResponse::fromBaseResponse($laravelResponse);

        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    "id" => 2,
                    "email" => "mail_from_fake@example.com",
                    "username" => "username_from_fake",
                    "full_name" => "Microservice Admin",
                    "created_at" => "2024-07-03 11:37:34",
                    "updated_at" => "2024-07-03 11:37:34"
                ],
                "status" => "success",
                "message" => "User data retrieved successfully."
            ]);
    }

    /**
     * This test, similar to the admin registration test, mocks the HTTP response and tests the AuthService by not hitting actual endpoints and instead simulating responses, 
     * it's focused on the internal workings and integration of application components.
     */
    public function test_user_login_success(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                "data" => [
                    "user" => [
                        "id" => 1,
                        "email" => "mail_from_fake@example.com",
                        "username" => "username_from_fake",
                        "full_name" => "Microservice Admin",
                    ],
                    "token" => [
                        "access_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYXV0aGVudGljYXRpb24vYXBpL2xvZ2luIiwiaWF0IjoxNzIwMDA4MDUzLCJleHAiOjE3MjAwODAwNTMsIm5iZiI6MTcyMDAwODA1MywianRpIjoiaThiUEFNVWVGSlNtRzljQyIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.DmCf4Y9GtoqNCDPUBIT3TH3dEzT6_PQWdhNIA3lxlRg",
                        "expires_at" => "2024-07-03 11:37:34",
                        "issued_at" => "2024-07-03 11:37:34",
                    ],
                    "status" => "success",
                    "message" => "User data retrieved successfully."
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->app->instance(Client::class, $client);

        $authService = new AuthService($client);
        $response = $authService->register([]);

        $laravelResponse = new LaravelResponse(
            $response->getBody()->getContents(),
            $response->getStatusCode(),
            $response->getHeaders()
        );

        $response = TestResponse::fromBaseResponse($laravelResponse);

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "user" => [
                        "id" => 1,
                        "email" => "mail_from_fake@example.com",
                        "username" => "username_from_fake",
                        "full_name" => "Microservice Admin",
                    ],
                    "token" => [
                        "access_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYXV0aGVudGljYXRpb24vYXBpL2xvZ2luIiwiaWF0IjoxNzIwMDA4MDUzLCJleHAiOjE3MjAwODAwNTMsIm5iZiI6MTcyMDAwODA1MywianRpIjoiaThiUEFNVWVGSlNtRzljQyIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.DmCf4Y9GtoqNCDPUBIT3TH3dEzT6_PQWdhNIA3lxlRg",
                        "expires_at" => "2024-07-03 11:37:34",
                        "issued_at" => "2024-07-03 11:37:34",
                    ],
                    "status" => "success",
                    "message" => "User data retrieved successfully."
                ]
            ]);
    }
}
