<?php

namespace App\Services;

use GuzzleHttp\Client;

class AuthService
{
    protected $authServiceUrl;

    public function __construct(private readonly Client $client)
    {
        $this->authServiceUrl = config('services.micro-services.authentication'); // URL of the User Authentication Service
    }

    public function register($data)
    {
        return $this->client->post("{$this->authServiceUrl}/register", [
            'json' => $data
        ]);
    }

    public function login($data)
    {
        return $this->client->post("{$this->authServiceUrl}/login", [
            'json' => $data
        ]);
    }

    public function logout($token)
    {
        return $this->client->post("{$this->authServiceUrl}/logout", [
            'json' => ['token' => $token]
        ]);
    }

    public function verify($token)
    {
        return $this->client->post("{$this->authServiceUrl}/verify", [
            'json' => ['token' => $token]
        ]);
    }

    public function passwordRecovery($email)
    {
        return $this->client->post("{$this->authServiceUrl}/password-recovery", [
            'json' => ['email' => $email]
        ]);
    }

    public function resetPassword($token, $newPassword)
    {
        return $this->client->post("{$this->authServiceUrl}/reset-password", [
            'json' => ['token' => $token, 'new_password' => $newPassword]
        ]);
    }

    public function verifyJWT($token)
    {
        return $this->client->post("{$this->authServiceUrl}/verify-jwt", [
            'json' => ['token' => $token]
        ]);
    }

    public function refreshJWT($token)
    {
        return $this->client->post("{$this->authServiceUrl}/refresh-token", [
            'json' => ['token' => $token]
        ]);
    }
}
