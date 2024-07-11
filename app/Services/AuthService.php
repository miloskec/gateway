<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

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
            'json' => $data,
        ]);
    }

    public function login($data)
    {
        return $this->client->post("{$this->authServiceUrl}/login", [
            'json' => $data,
        ]);
    }

    public function logout($token)
    {
        $cacheKey = generateJwtUserKey($token);

        $response = $this->client->post("{$this->authServiceUrl}/logout", [
            'json' => ['token' => $token],
        ]);

        $responseDecoded = json_decode($response->getBody(), true);

        if ($responseDecoded['message'] === 'Successfully logged out') {
            Cache::forget($cacheKey);
        }

        return $response;
    }

    public function verify($token)
    {
        return $this->client->post("{$this->authServiceUrl}/verify", [
            'json' => ['token' => $token],
        ]);
    }

    public function passwordRecovery($email)
    {
        return $this->client->post("{$this->authServiceUrl}/password-recovery", [
            'json' => ['email' => $email],
        ]);
    }

    public function resetPasswordWithToken($email, $resetToken, $password)
    {
        return $this->client->post("{$this->authServiceUrl}/reset-password-token", [
            'json' => ['email' => $email, 'reset_token' => $resetToken, 'password' => $password],
        ]);
    }

    public function resetPassword($token, $newPassword, $currentPassword)
    {
        return $this->client->post("{$this->authServiceUrl}/reset-password", [
            'json' => ['token' => $token, 'password' => $newPassword, 'current_password' => $currentPassword],
        ]);
    }

    public function verifyJWT($token)
    {
        return $this->client->post("{$this->authServiceUrl}/verify-jwt", [
            'json' => ['token' => $token],
        ]);
    }

    public function refreshJWT($token)
    {
        $cacheKey = generateJwtUserKey($token);
        Cache::forget($cacheKey);

        return $this->client->post("{$this->authServiceUrl}/refresh-token", [
            'json' => ['token' => $token],
        ]);
    }
}
