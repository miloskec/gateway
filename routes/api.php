<?php

// routes/api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProxyController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-jwt', [AuthController::class, 'verifyJWT']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password-recovery', [AuthController::class, 'passwordRecovery']);
    Route::post('/reset-password-token', [AuthController::class, 'resetPasswordWithToken']);
});
/**
 * TODO: Implement the following routes
 * Route::post('/verify', [AuthController::class, 'verify']);
 */
Route::middleware(['throttle:global'])->group(function () {
    Route::middleware(['jwt.auth', 'authorize:profile.show'])->group(function () {
        Route::get('/profile', [ProxyController::class, 'handleProfile'])->name('profile.show');
    });
    //authorize.profile only allowe users with role admin.panel to access it
    Route::middleware(['jwt.auth', 'authorize:admin.panel'])->group(function () {
        Route::get('/profile/admin', [ProxyController::class, 'handleAdminProfile'])->name('profile.admin.panel');
    });
    //authorize.profile only allowe admin or owner of profile to access it
    Route::middleware(['jwt.auth', 'profile.access'])->group(function () {
        Route::get('/profile/{id}', [ProxyController::class, 'handleProfile'])->whereNumber('id')->name('profile.show.id');
    });

    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::get('/health', function () {
        return response()->json(['status' => 'OK'], 200);
    });

    //Proxy routes
    $services = ['profile'];
    foreach ($services as $service) {
        Route::any("/{{$service}}/{path?}", [ProxyController::class, 'handleDynamic'])
            ->middleware(JWTMiddleware::class)
            ->where('path', '.*')
            ->name($service);
    }
});
