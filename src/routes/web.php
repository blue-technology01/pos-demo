<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Cash\CashController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Printer\PrinterController;
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

Route::get('/', fn () => redirect()->route('login'));
Route::get('/403', fn () => view('errors.403'))->name('forbidden');
Route::get('/404', fn () => view('errors.404'))->name('notFound');
Route::get('/500', fn () => view('errors.500'))->name('serverError');

Route::middleware('guest')->group(function () {
    Route::get('/login',                        [LoginController::class, 'index'])->name('login');
    Route::post('/login',                       [LoginController::class, 'login'])->name('auth.login.post');

    Route::get('/forgot-password',              [ForgotPasswordController::class, 'index'])->name('auth.forgot-password');
    Route::post('/forgot-password/send-otp',    [ForgotPasswordController::class, 'sendOtp'])->name('auth.forgot-password.send-otp');
    Route::get('/forgot-password/otp',          [ForgotPasswordController::class, 'otpForm'])->name('auth.otp.show');
    Route::post('/forgot-password/otp/verify',  [ForgotPasswordController::class, 'verifyOtp'])->name('auth.otp.verify');
    Route::post('/forgot-password/otp/resend',  [ForgotPasswordController::class, 'resendOtp'])->name('auth.otp.resend');
    Route::get('/reset-password',               [ForgotPasswordController::class, 'resetForm'])->name('auth.reset-password.show');
    Route::post('/reset-password',              [ForgotPasswordController::class, 'resetPassword'])->name('auth.reset-password.post');
});

Route::get('/cashier/customer-display', function () {
            return view('cashier.customer-display');
})->name('cashier.customer.display');

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/fetch',                    [NotificationController::class, 'fetch'])->name('fetch');
        Route::patch('/{notification}/read',    [NotificationController::class, 'markAsRead'])->name('read');
        Route::patch('/read-all',               [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('/generate',                [NotificationController::class, 'generate'])->name('generate');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Dashboard
        Route::get('/dashboard',        [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data',   [DashboardController::class, 'data'])->name('dashboard.data');
        // Profile
        Route::get('/profile',          [RegisterController::class, 'userProfile'])->name('profile');
        Route::get('/preview-settings', [RegisterController::class, 'previewSetting'])->name('preview-settings');
        Route::post('/preview/update',  [PrinterController::class, 'updatePreview'])->name('preview.update');
        // Users
        Route::get('/users',                [RegisterController::class, 'showFormRegister'])->name('users');
        Route::post('/users/register',      [RegisterController::class, 'register'])->name('users.register');
        Route::put('/users/{user}',         [RegisterController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',      [RegisterController::class, 'destroy'])->name('users.destroy');
        // Category
        Route::get('/category',             [CategoryController::class, 'index'])->name('category');
        Route::post('/category',            [CategoryController::class, 'store'])->name('category.store');
        Route::put('/category/{code}',      [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/category/{code}',   [CategoryController::class, 'destroy'])->name('category.destroy');
        // Unit
        Route::get('/unit',             [UomController::class, 'index'])->name('unit');
        Route::post('/unit',            [UomController::class, 'store'])->name('unit.store');
        Route::put('/unit/{code}',      [UomController::class, 'update'])->name('unit.update');
        Route::delete('/unit/{code}',   [UomController::class, 'destroy'])->name('unit.destroy');
        // Products
        Route::get('/products',             [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create',      [ProductController::class, 'create'])->name('products.create');
        Route::post('/products',            [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{code}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{code}',      [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{code}',   [ProductController::class, 'destroy'])->name('products.destroy');
        // Product UOM
        Route::get('/product-uom',              [ProductUomController::class, 'index'])->name('product-uom.index');
        Route::get('/product-uom/create',       [ProductUomController::class, 'create'])->name('product-uom.create');
        Route::post('/product-uom',             [ProductUomController::class, 'store'])->name('product-uom.store');
        Route::get('/product-uom/{id}/edit',    [ProductUomController::class, 'edit'])->name('product-uom.edit');
        Route::put('/product-uom/{id}',         [ProductUomController::class, 'update'])->name('product-uom.update');
        Route::delete('/product-uom/{id}',      [ProductUomController::class, 'destroy'])->name('product-uom.destroy');
        // Customers
        Route::get('/customers/search',         [CustomerController::class, 'searchAjax'])->name('customers.search.ajax');
        Route::get('/customers',                [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers',               [CustomerController::class, 'store'])->name('customers.st   ore');
        Route::put('/customers/{customer}',     [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}',  [CustomerController::class, 'destroy'])->name('customers.destroy');
        // Sales
        Route::get('/sales',                    [SaleController::class, 'index'])->name('sales.index');
        Route::post('/sales',                   [SaleController::class, 'store'])->name('sales.store');
        Route::get('/sales/{sale}',             [SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{sale}/edit',        [SaleController::class, 'edit'])->name('sales.edit');
        Route::put('/sales/{sale}',             [SaleController::class, 'update'])->name('sales.update');
        Route::patch('/sales/{sale}/cancel',    [SaleController::class, 'cancel'])->name('sales.cancel');
        // Inventory & Stock
        Route::get('/stock-update',     [InventoryController::class, 'index'])->name('stock-update');
        Route::get('/stock-validation', [StockValidateController::class, 'index'])->name('stock-validation');
        // Cash Register / Shift
        Route::get('/shift', [CashController::class, 'index'])->name('shift');
        // Reports
        Route::get('/reports',          [ReportIndexController::class, 'index'])->name('reports.index');
        Route::get('/revenue-tracking', [RevenueTrackingController::class, 'index'])->name('revenue-tracking');
        Route::get('/sale-person',      [SalePerformanceController::class, 'index'])->name('sale-person');
        Route::get('/top-product',      [TopProductController::class, 'index'])->name('top-product');
    });

    Route::prefix('cashier')->name('cashier.')->middleware('role:cashier')->group(function () {
        // Customers
        Route::get('/customers/search', [CustomerController::class, 'searchAjax'])->name('customers.search.ajax');
        Route::post('/customers',       [CustomerController::class, 'store'])->name('customers.store');
        // POS
        Route::get('/pos',                                      [PosController::class, 'index'])->name('pos');
        Route::get('/pos/products',                             [ProductUomController::class, 'posProducts'])->name('pos.products');
        Route::get('/pos/products/{productCode}/uoms',          [ProductUomController::class, 'getByProduct'])->name('pos.products.uoms');
        // Shift
        Route::post('/open',                    [CashController::class, 'open'])->name('open');
        Route::post('/close',                   [CashController::class, 'close'])->name('close');
        Route::get('/current-shift-details',    [CashController::class, 'getCurrentShiftDetails'])->name('shift-details');
        // Sale Items (cart)
        Route::prefix('sale-items')->name('sale-items.')->group(function () {
            Route::post('/',            [SaleItemController::class, 'store'])->name('store');
            Route::put('/{rowId}',      [SaleItemController::class, 'update'])->name('update');
            Route::delete('/{rowId}',   [SaleItemController::class, 'destroy'])->name('destroy');
            Route::delete('/',          [SaleItemController::class, 'clear'])->name('clear');
            Route::post('/confirm',     [SaleItemController::class, 'confirm'])->name('confirm');
        });
    });
});
