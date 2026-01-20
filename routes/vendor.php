<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\Auth\LoginController;
use App\Http\Controllers\Vendor\Auth\RegisterController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\CategoryController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\ReportController;
use App\Http\Controllers\Vendor\MediaController;
use App\Http\Controllers\Vendor\LeadController;
use App\Http\Controllers\Vendor\CouponController;
use App\Http\Controllers\Vendor\ProductAnalyticsController;
use App\Http\Controllers\Vendor\SalaryController;
use App\Http\Controllers\Vendor\AttendanceController;
use App\Http\Controllers\Vendor\PendingBillController;
use App\Http\Controllers\Vendor\InvoiceController;
use App\Http\Controllers\Vendor\StaffController;

/*
|--------------------------------------------------------------------------
| Vendor Routes
|--------------------------------------------------------------------------
|
| Here is where you can register vendor routes for your application.
|
*/

// Vendor Authentication Routes (Guest)
Route::prefix('vendor')->name('vendor.')->group(function () {
    // Login routes
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.post');
    
    // Registration routes
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.post');
    
    // Logout route
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Status pages (accessible when logged in but not approved)
    Route::middleware('auth')->group(function () {
        Route::get('pending', [DashboardController::class, 'pending'])->name('pending');
        Route::get('rejected', [DashboardController::class, 'rejected'])->name('rejected');
        Route::get('suspended', [DashboardController::class, 'suspended'])->name('suspended');
    });
});

// Vendor Protected Routes (Requires authentication and approved vendor)
Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'vendor'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    
    // Profile Management
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/address', [ProfileController::class, 'updateAddress'])->name('profile.update-address');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::put('profile/store-settings', [ProfileController::class, 'updateStoreSettings'])->name('profile.store-settings');
    Route::get('profile/store', [ProfileController::class, 'storeSettings'])->name('profile.store');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::post('profile/avatar/remove', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::post('profile/store-logo', [ProfileController::class, 'updateStoreLogo'])->name('profile.store-logo.update');
    Route::post('profile/store-logo/remove', [ProfileController::class, 'removeStoreLogo'])->name('profile.store-logo.remove');
    Route::post('profile/store-banner', [ProfileController::class, 'updateStoreBanner'])->name('profile.store-banner.update');
    Route::post('profile/store-banner/remove', [ProfileController::class, 'removeStoreBanner'])->name('profile.store-banner.remove');
    Route::post('profile/bank-details', [ProfileController::class, 'updateBankDetails'])->name('profile.bank-details.update');
    Route::post('profile/social-links', [ProfileController::class, 'updateSocialLinks'])->name('profile.social-links.update');
    
    // Product Management
    Route::resource('products', ProductController::class);
    Route::get('products-low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    
    // Category Management
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // AJAX routes for categories
    Route::get('categories-all', [CategoryController::class, 'getAllCategories'])->name('categories.all');
    Route::post('categories/create', [CategoryController::class, 'createCategory'])->name('categories.create.ajax');
    Route::post('subcategories/create', [CategoryController::class, 'createSubCategory'])->name('subcategories.create.ajax');
    
    // Subcategory routes
    Route::get('categories/{category}/subcategories', [CategoryController::class, 'getSubCategories'])->name('categories.subcategories');
    Route::post('subcategories', [CategoryController::class, 'storeSubCategory'])->name('subcategories.store');
    Route::get('subcategories/{subCategory}', [CategoryController::class, 'showSubCategory'])->name('subcategories.show');
    Route::put('subcategories/{subCategory}', [CategoryController::class, 'updateSubCategory'])->name('subcategories.update');
    Route::delete('subcategories/{subCategory}', [CategoryController::class, 'destroySubCategory'])->name('subcategories.destroy');
    
    // Media Library
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/list', [MediaController::class, 'getMedia'])->name('media.list');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    
    // Order Management
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    
    // Reports & Analytics
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    
    // Lead Management
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('leads/trashed', [LeadController::class, 'trashed'])->name('leads.trashed');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');
    Route::delete('leads/{id}/force-delete', [LeadController::class, 'forceDelete'])->name('leads.force-delete');
    
    // Coupon Management
    Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('coupons/create', [CouponController::class, 'create'])->name('coupons.create');
    Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
    Route::get('coupons/{coupon}', [CouponController::class, 'show'])->name('coupons.show');
    Route::get('coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
    Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
    Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
    Route::post('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
    
    // Product Analytics
    Route::get('analytics/products', [ProductAnalyticsController::class, 'index'])->name('analytics.products');
    Route::get('analytics/products/export', [ProductAnalyticsController::class, 'export'])->name('analytics.products.export');
    Route::get('analytics/products/{product}', [ProductAnalyticsController::class, 'show'])->name('analytics.products.show');
    
    // Salary Management
    Route::get('salary', [SalaryController::class, 'index'])->name('salary.index');
    Route::get('salary/create', [SalaryController::class, 'create'])->name('salary.create');
    Route::post('salary', [SalaryController::class, 'store'])->name('salary.store');
    Route::get('salary/payments', [SalaryController::class, 'payments'])->name('salary.payments');
    Route::get('salary/{userId}', [SalaryController::class, 'show'])->name('salary.show');
    Route::delete('salary/{id}', [SalaryController::class, 'destroy'])->name('salary.destroy');
    Route::post('salary/payments/{id}/process', [SalaryController::class, 'processPayment'])->name('salary.payments.process');
    Route::put('salary/payments/{id}/adjustments', [SalaryController::class, 'updateAdjustments'])->name('salary.payments.adjustments');
    Route::get('salary/payments/{id}/slip', [SalaryController::class, 'slip'])->name('salary.payments.slip');
    
    // Attendance Management
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/bulk', [AttendanceController::class, 'bulk'])->name('attendance.bulk');
    Route::post('attendance/bulk', [AttendanceController::class, 'storeBulk'])->name('attendance.store-bulk');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/data', [AttendanceController::class, 'getAttendance'])->name('attendance.data');
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    
    // Pending Bills Management
    Route::get('pending-bills', [PendingBillController::class, 'index'])->name('pending-bills.index');
    Route::get('pending-bills/summary', [PendingBillController::class, 'summary'])->name('pending-bills.summary');
    Route::get('pending-bills/user/{userId}', [PendingBillController::class, 'userBills'])->name('pending-bills.user');
    Route::get('pending-bills/{invoice}', [PendingBillController::class, 'show'])->name('pending-bills.show');
    Route::post('pending-bills/{invoice}/payment', [PendingBillController::class, 'recordPayment'])->name('pending-bills.record-payment');
    
    // Invoices Management
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    
    // Staff Management
    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('staff/{id}', [StaffController::class, 'show'])->name('staff.show');
    Route::get('staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
});
