<?php

namespace Tests;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected $password = 'password!3%A';

    protected function setUp(): void
    {
        parent::setUp();
        Log::swap(new \Illuminate\Log\LogManager(app()));
    }


    public function createRequest($method, $uri, $params, $cookies = [], $files = [], $server = [], $content = null, $headers = []): Request
    {
        $symfonyRequest = SymfonyRequest::create(
            $uri,
            $method,
            $params,
            cookies: $cookies, // You can also pass cookies if needed
            files: $files,    // Files can be passed here
            server: $server,   // Server parameters
            content: $content // Raw body data
        );

        foreach ($headers as $key => $value) {
            $symfonyRequest->headers->set($key, $value);
        }
        return Request::createFromBase($symfonyRequest);
    }
}
