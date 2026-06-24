<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Cash\CashController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
// use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductUomController;
use App\Http\Controllers\Product\UomController;
use App\Http\Controllers\Report\ReportIndexController;
use App\Http\Controllers\Report\RevenueTrackingController;
use App\Http\Controllers\Report\SalePerformanceController;
use App\Http\Controllers\Report\TopProductController;
use App\Http\Controllers\Sale\InventoryController;
use App\Http\Controllers\Sale\PosController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Sale\SaleItemController;
use App\Http\Controllers\Stock\StockValidateController;

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
    // notification
    // Route::get('/notifications/low-stock', [NotificationController::class, 'lowStock'])->name('notifications.low-stock');
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/customers/search', [CustomerController::class, 'searchAjax'])->name('customers.search.ajax');
        // Route::get('/admin/sales/{id}/pdf', [InvoiceController::class, 'exportSingleInvoicePdf'])->name('admin.sales.single-pdf');
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
        //user
        Route::get('/users', [RegisterController::class, 'showFormRegister'])->name('users');
        Route::post('/users/register', [RegisterController::class, 'register'])->name('users.register');
        Route::put('/users/{user}', [RegisterController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [RegisterController::class, 'destroy'])->name('users.destroy');
        Route::post('/preview/update', [RegisterController::class, 'updatePreview']);
        Route::get('/profile', [RegisterController::class, 'userProfile'])->name('profile');
        Route::get('/preview-settings', [RegisterController::class, 'previewSetting'])->name('preview-settings');

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
        // customer
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        // sale history
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/sales/{id}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{id}/edit', [SaleController::class, 'edit'])->name('sales.edit');
        Route::put('/sales/{id}', [SaleController::class, 'update'])->name('sales.update');
        Route::patch('/sales/{id}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
        // inventory
        Route::get('/stock-update', [InventoryController::class, 'index'])->name('stock-update');
        Route::get('/stock-validation', [StockValidateController::class, 'index'])->name('stock-validation');
        // report
        Route::get('/reports', [ReportIndexController::class, 'index'])->name('reports.index');
        Route::get('/revenue-tracking', [RevenueTrackingController::class, 'index'])->name('revenue-tracking');
        Route::get('/sale-person', [SalePerformanceController::class, 'index'])->name('sale-person');
        Route::get('/top-product', [TopProductController::class, 'index'])->name('top-product');

    });

    // pos for cashiar
    Route::prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/pos', function () {
            return view('cashier.pos.index');
        })->name('pos');
        Route::post('/open', [CashController::class, 'open'])->name('open');
        Route::post('/close', [CashController::class, 'close'])->name('close');
        Route::get('/current-shift-details', [CashController::class, 'getCurrentShiftDetails'])->name('shift-details');
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::get('/pos/products', [ProductUomController::class, 'posProducts'])->name('pos.products');
        Route::get('/pos/products/{productCode}/uoms', [ProductUomController::class, 'getByProduct'])->name('pos.products.uoms');
        Route::prefix('sale-items')->name('sale-items.')->group(function () {

            Route::post('/',            [SaleItemController::class, 'store'])->name('store');
            Route::put('/{rowId}',      [SaleItemController::class, 'update'])->name('update');
            Route::delete('/{rowId}',   [SaleItemController::class, 'destroy'])->name('destroy');
            Route::delete('/',          [SaleItemController::class, 'clear'])->name('clear');
            Route::post('/confirm',     [SaleItemController::class, 'confirm'])->name('confirm');
        });
    });
});
