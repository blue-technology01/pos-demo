<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', function () {
    return redirect()->route('auth.login');
});

Route::middleware('guest')->group(function () {

    // step 1 login
    Route::get('/login', [LoginController::class, 'index'])
    ->name('auth.login');
    Route::post('/login', [LoginController::class, 'login'])
        ->name('auth.login.post');

    // step 2 forgot password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])
        ->name('auth.forgot-password');
    Route::post('/forgot-password/send-otp', [ForgotPasswordController::class,'sendOtp'])
    ->name('auth.forgot-password.send-otp');

    // step 3 OTP verifycation.
    // it only testing for otp I don't use real sms testing. But I use logic that compair
    // phone one database with phone that input, if correctly it will send otp 6 to laravel log.
    Route::get('/forgot-password/otp', [ForgotPasswordController::class, 'otpForm'])
        ->name('auth.otp.show');
    Route::post('/forgot-password/otp/verify', [ForgotPasswordController::class, 'verifyOtp'])
        ->name('auth.otp.verify');
    Route::post('/forgot-password/otp/resend', [ForgotPasswordController::class, 'resendOtp'])
        ->name('auth.otp.resend');

    //step 4 reset password
    Route::get('/reset-password', [ForgotPasswordController::class, 'resetForm'])
        ->name('auth.reset-password.show');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
        ->name('auth.reset-password.post');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('auth.logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard.index');
        })->name('dashboard');
        Route::get('/users', [RegisterController::class, 'showFormRegister'])
            ->name('users');
        Route::post('/users/register', [RegisterController::class, 'register'])
        ->name('users.register');
        Route::put('/users/{user}', [RegisterController::class, 'update'])
        ->name('users.update');
        Route::delete('/users/{id}', [RegisterController::class, 'destroy'])
        ->name('users.destroy');

        // setting funciton  route test
        Route::get('/profile', function () {
            return view('admin.users.user-profile');
        })->name('profile');

        Route::get('/payment-method', function () {
            return view('admin.users.payment-method');
        })->name('payment-method');

        // test show product
        Route::get('/productlist', function () {
            return view('admin.products.productlist');
        })->name('productlist');
        Route::get('/create-product', function () {
            return view('admin.products.create-product');
        })->name('create-product');
        Route::get('/unit', function () {
            return view('admin.products.unit');
        })->name('unit');
        Route::get('/category',function(){
            return view('admin.products.category');
        })->name('category');

        // test show inventory
        Route::get('/stock-update',function(){
            return view('admin.stocks.stock-update');
        })->name('stock-update');

        Route::get('/stock-validation',function(){
            return view('admin.stocks.stock-validation');
        })->name('stock-validation');

    });

    // pos
    Route::prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/pos', function () {
            return view('cashier.pos.index');
        })->name('pos');
    });
});
