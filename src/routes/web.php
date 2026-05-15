<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return redirect()->route('auth.login');
});

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showFormLogin'])
        ->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login.post');

    Route::get('/forgot-password', [AuthController::class, 'showFormForgotPassword'])
        ->name('auth.forgot-password');
    Route::post('/forgot-password',[AuthController::class,'forgotPassword'])
        ->name('auth.forgot-password.post');

    Route::get('/otp', [AuthController::class, 'showFormOtp'])
        ->name('auth.otp.show');
    Route::post('/otp', [AuthController::class, 'verifyOtp'])
        ->name('auth.otp.verify');

    Route::get('/reset-password', [AuthController::class, 'showFormResetPassword'])
        ->name('auth.reset-password.show');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('auth.reset-password.post');

});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard.index');
        })->name('dashboard');

        Route::get('/users', [AuthController::class, 'showFormRegister'])
            ->name('users');

        Route::post('/users/register', [AuthController::class, 'register'])
            ->name('users.register');
    });
});
