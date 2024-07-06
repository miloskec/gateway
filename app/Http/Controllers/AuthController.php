<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRecoveryRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordResetWithTokenRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\VerifyTokenRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(UserRegisterRequest $request)
    {
        return $this->authService->register($request->validated());
    }

    public function login(UserLoginRequest $request)
    {
        return $this->authService->login($request->validated());
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request->bearerToken());
    }

    public function verify(VerifyTokenRequest $request)
    {
        return $this->authService->verify($request->bearerToken());
    }

    public function verifyJWT(Request $request)
    {
        return $this->authService->verifyJWT($request->bearerToken());
    }

    public function passwordRecovery(PasswordRecoveryRequest $request)
    {
        return $this->authService->passwordRecovery($request->email);
    }

    public function resetPasswordWithToken(PasswordResetWithTokenRequest $request)
    {
        return $this->authService->resetPasswordWithToken($request->email, $request->reset_token, $request->password);
    }

    public function resetPassword(PasswordResetRequest $request)
    {
        return $this->authService->resetPassword($request->bearerToken(), $request->password, $request->current_password);
    }

    public function refresh(Request $request)
    {
        return $this->authService->refreshJWT($request->bearerToken());
    }
}