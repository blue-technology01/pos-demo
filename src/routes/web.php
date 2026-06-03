<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Cash\CashController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductUomController;
use App\Http\Controllers\Product\UomController;

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


    Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard.index');
        })->name('dashboard');
        Route::get('/users', [RegisterController::class, 'showFormRegister'])->name('users');
        Route::post('/users/register', [RegisterController::class, 'register'])->name('users.register');
        Route::put('/users/{user}', [RegisterController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [RegisterController::class, 'destroy'])->name('users.destroy');
        Route::post('/preview/update', [RegisterController::class, 'updatePreview']);
        // category
        Route::get('/category',[CategoryController::class,'index'])->name('category');
        Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
        Route::put('/category/{code}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/category/{code}', [CategoryController::class, 'destroy'])->name('category.destroy');
        // unit
        Route::get('/unit',[UomController::class,'index'])->name('unit');
        Route::post('/unit', [UomController::class, 'store'])->name('unit.store');
        Route::put('/unit/{code}', [UomController::class, 'update'])->name('unit.update');
        Route::delete('/unit/{code}', [UomController::class, 'destroy'])->name('unit.destroy');
        // product
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{code}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{code}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{code}', [ProductController::class, 'destroy'])->name('products.destroy');
        // product uom
        Route::get('/product-uom', [ProductUomController::class, 'index'])->name('product-uom.index');
        Route::get('/product-uom/create', [ProductUomController::class, 'create'])->name('product-uom.create');
        Route::post('/product-uom', [ProductUomController::class, 'store'])->name('product-uom.store');
        Route::get('/product-uom/{id}/edit', [ProductUomController::class, 'edit'])->name('product-uom.edit');
        Route::put('/product-uom/{id}', [ProductUomController::class, 'update'])->name('product-uom.update');
        Route::delete('/product-uom/{id}', [ProductUomController::class, 'destroy'])->name('product-uom.destroy');
        // cashe register
        Route::get('/shift', [CashController::class, 'index'])->name('shift');

        // setting funciton  route test
        Route::get('/profile', function () {
            return view('admin.users.user-profile');
        })->name('profile');

        Route::get('/payment-method', function () {
            return view('admin.users.payment-method');
        })->name('payment-method');
        Route::get('/preview-settings', function () {
            return view('admin.users.preview-settings');
        })->name('preview-settings');

        // test show inventory
        Route::get('/stock-update',function(){
            return view('admin.stocks.stock-update');
        })->name('stock-update');

        Route::get('/stock-validation',function(){
            return view('admin.stocks.stock-validation');
        })->name('stock-validation');

        // show sale
        Route::get('/sale-list',function(){
            return view('admin.sales.sale-list');
        })->name('sale-list');

        // report
        Route::get('/index',function(){
            return view('admin.reports.index');
        })->name('index');

        Route::get('/revent-tracking',function(){
            return view('admin.reports.revent-tracking');
        })->name('revent-tracking');

        Route::get('/sale-person',function(){
            return view('admin.reports.sale-person');
        })->name('sale-person');

        Route::get('/top-product',function(){
            return view('admin.reports.top-product');
        })->name('top-product');

        Route::get('/customer-list',function(){
            return view('admin.customers.customer-list');
        })->name('customer-list');

    });

    // pos for cashiar
    Route::prefix('cashier')->name('cashier.')->group(function () {
    // Route::prefix('cashier')->name('cashier.')->middleware(['auth', 'role:cashier'])->group(function () {
        Route::get('/pos', function () {
            return view('cashier.pos.index');
        })->name('pos');
        Route::post('/open', [CashController::class, 'open'])->name('open');
        Route::post('/close', [CashController::class, 'close'])->name('close');
    });
});
