<?php

// routes/api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProxyController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/password-recovery', [AuthController::class, 'passwordRecovery']);
    Route::post('/reset-password-token', [AuthController::class, 'resetPasswordWithToken']);
});

Route::middleware(['throttle:100,1'])->group(function () {
    Route::middleware(['jwt.auth', 'authorize:profile.show'])->group(function () {
        Route::get('/profile', [ProxyController::class, 'handleProfile']);
    });

    Route::middleware(['jwt.auth', 'authorize:admin.panel'])->group(function () {
        Route::get('/profile/admin', [ProxyController::class, 'handleAdminProfile']);
    });

    Route::middleware(['jwt.auth', 'profile.access'])->group(function () {
        Route::get('/profile/{id}', [ProxyController::class, 'handleProfile'])->whereNumber('id');
    });

    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/verify-jwt', [AuthController::class, 'verifyJWT']);
        Route::post('/refresh-token', [AuthController::class, 'refresh']);
    });

    Route::get('/health', function () {
        return response()->json(['status' => 'OK'], 200);
    });

    // Proxy routes
    Route::any("/{service}/{path?}", [ProxyController::class, 'handleDynamic'])
        ->middleware(JWTMiddleware::class)
        ->where('path', '.*')
        ->name('proxy');
});
