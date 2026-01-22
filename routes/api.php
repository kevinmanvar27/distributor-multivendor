<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SubCategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\ShoppingCartController;
use App\Http\Controllers\API\ProformaInvoiceController;
use App\Http\Controllers\API\PageController;
use App\Http\Controllers\API\UserGroupController;
use App\Http\Controllers\API\UserGroupMemberController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\MyInvoiceController;
use App\Http\Controllers\API\ProductSearchController;
use App\Http\Controllers\API\AppConfigController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\CustomerAuthController;
use App\Http\Controllers\API\CustomerStoreController;
use App\Http\Controllers\API\VendorCustomerController;
use App\Http\Controllers\API\CustomerCartController;
use App\Http\Controllers\API\CustomerInvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Password Reset routes with OTP (public)
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
    Route::post('/resend-otp', [PasswordResetController::class, 'resendOtp']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyResetToken']);
    
    // App Version Check (public - no auth required)
    Route::get('/app-version', [AppConfigController::class, 'appVersion']);
    
    // App Settings (public - no auth required)
    Route::get('/app-settings', [AppConfigController::class, 'appSettings']);
    Route::get('/app-config', [AppConfigController::class, 'appConfigPublic']);
    Route::get('/company-info', [AppConfigController::class, 'companyInfo']);
    
    // Product Search routes (public - no auth required) - MUST be before apiResource routes
    Route::get('/products/search', [ProductSearchController::class, 'search']);
    Route::get('/products/by-category/{categoryId}', [ProductSearchController::class, 'byCategory']);
    Route::get('/products/by-subcategory/{subcategoryId}', [ProductSearchController::class, 'bySubcategory']);
    Route::get('/categories/{id}/subcategories', [ProductSearchController::class, 'subcategoriesByCategory']);
    
    // Public resources
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('subcategories', SubCategoryController::class)->only(['index', 'show']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::apiResource('pages', PageController::class)->only(['index', 'show']);
    
    // Home/Dashboard route (public - works with or without auth)
    Route::get('/home', [HomeController::class, 'index']);
    
    // =============================================
    // PUBLIC STORE ROUTES (Browse Vendors/Stores)
    // =============================================
    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);                          // List all stores
        Route::get('/featured', [StoreController::class, 'featured']);               // Featured vendors
        Route::get('/{slug}', [StoreController::class, 'show']);                     // Store details by slug
        Route::get('/{slug}/products', [StoreController::class, 'products']);        // Store products
        Route::get('/{slug}/categories', [StoreController::class, 'categories']);    // Store categories
        Route::get('/{slug}/reviews', [StoreController::class, 'reviews']);          // Store reviews
    });
    
    // Public coupon validation
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
    Route::get('/coupons/available', [CouponController::class, 'available']);
    
    // Vendor Registration (public)
    Route::post('/vendor/register', [VendorController::class, 'register']);
    Route::post('/vendor/login', [VendorController::class, 'login']);
    
    // =============================================
    // VENDOR CUSTOMER AUTH ROUTES (Public)
    // =============================================
    // Customer login - requires vendor_slug to identify which vendor's customer
    Route::post('/customer/login', [CustomerAuthController::class, 'login']);
});

// Protected API routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // =============================================
    // MOBILE APP API ROUTES (New Flutter App APIs)
    // =============================================
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);
    Route::delete('/profile/delete-account', [ProfileController::class, 'deleteAccount']);
    
    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{productId}', [WishlistController::class, 'add']);
    Route::delete('/wishlist/{productId}', [WishlistController::class, 'remove']);
    Route::get('/wishlist/check/{productId}', [WishlistController::class, 'check']);
    Route::post('/wishlist/{productId}/add-to-cart', [WishlistController::class, 'addToCart']);
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear']);
    
    // Cart routes (user-specific cart)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'remove']);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::post('/cart/generate-invoice', [CartController::class, 'generateInvoice']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    
    // Coupon routes (authenticated)
    Route::post('/coupons/apply', [CouponController::class, 'apply']);
    Route::post('/coupons/remove', [CouponController::class, 'remove']);
    
    // Store follow/unfollow routes (authenticated)
    Route::post('/stores/{slug}/follow', [StoreController::class, 'follow']);
    Route::delete('/stores/{slug}/unfollow', [StoreController::class, 'unfollow']);
    Route::get('/stores/{slug}/is-following', [StoreController::class, 'isFollowing']);
    Route::get('/my-followed-stores', [StoreController::class, 'myFollowedStores']);
    
    // My Invoices routes (user-specific invoices)
    Route::get('/my-invoices', [MyInvoiceController::class, 'index']);
    Route::get('/my-invoices/{id}', [MyInvoiceController::class, 'show']);
    Route::get('/my-invoices/{id}/download-pdf', [MyInvoiceController::class, 'downloadPdf']);
    Route::post('/my-invoices/{id}/add-to-cart', [MyInvoiceController::class, 'addToCart']);
    Route::delete('/my-invoices/{id}/items/{productId}', [MyInvoiceController::class, 'removeItem']);
    Route::delete('/my-invoices/{id}', [MyInvoiceController::class, 'destroy']);
    
    // User Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::post('/notifications/register-device', [NotificationController::class, 'registerDeviceToken']);
    
    // =============================================
    // VENDOR API ROUTES (Vendor Dashboard APIs)
    // =============================================
    Route::prefix('vendor')->group(function () {
        // Dashboard & Analytics
        Route::get('/dashboard', [VendorController::class, 'dashboard']);
        Route::get('/analytics', [VendorController::class, 'analytics']);
        
        // Profile & Store Settings
        Route::get('/profile', [VendorController::class, 'profile']);
        Route::put('/profile', [VendorController::class, 'updateProfile']);
        Route::post('/store-logo', [VendorController::class, 'uploadStoreLogo']);
        Route::post('/store-banner', [VendorController::class, 'uploadStoreBanner']);
        Route::put('/bank-details', [VendorController::class, 'updateBankDetails']);
        
        // Products Management
        Route::get('/products', [VendorController::class, 'products']);
        Route::post('/products', [VendorController::class, 'createProduct']);
        Route::put('/products/{id}', [VendorController::class, 'updateProduct']);
        Route::delete('/products/{id}', [VendorController::class, 'deleteProduct']);
        Route::get('/products/low-stock', [VendorController::class, 'lowStockProducts']);
        
        // Orders Management
        Route::get('/orders', [VendorController::class, 'orders']);
        Route::get('/orders/{id}', [VendorController::class, 'orderDetails']);
        
        // =============================================
        // VENDOR CUSTOMER MANAGEMENT ROUTES
        // Vendors can create customers with login credentials
        // These customers can only see this vendor's products
        // =============================================
        Route::get('/customers', [VendorCustomerController::class, 'index']);
        Route::post('/customers', [VendorCustomerController::class, 'store']);
        Route::get('/customers/{id}', [VendorCustomerController::class, 'show']);
        Route::put('/customers/{id}', [VendorCustomerController::class, 'update']);
        Route::delete('/customers/{id}', [VendorCustomerController::class, 'destroy']);
        Route::put('/customers/{id}/reset-password', [VendorCustomerController::class, 'resetPassword']);
        Route::put('/customers/{id}/toggle-status', [VendorCustomerController::class, 'toggleStatus']);
    });
    
    // =============================================
    // ADMIN API ROUTES (Existing Admin Panel APIs)
    // =============================================
    
    // Admin Notification routes
    Route::post('/notifications/send-to-user', [NotificationController::class, 'sendToUser']);
    Route::post('/notifications/send-to-group', [NotificationController::class, 'sendToUserGroup']);
    Route::get('/notifications/statistics', [NotificationController::class, 'getStatistics']);
    
    // Legacy notification routes (kept for backward compatibility)
    Route::post('/notifications/device-token', [NotificationController::class, 'registerDeviceToken']);
    Route::get('/notifications/stats', [NotificationController::class, 'getStatistics']);
    
    // Resource routes
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('subcategories', SubCategoryController::class)->except(['index', 'show']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('media', MediaController::class);
    Route::apiResource('settings', SettingController::class);
    Route::apiResource('shopping-cart', ShoppingCartController::class);
    Route::apiResource('proforma-invoices', ProformaInvoiceController::class);
    Route::patch('/proforma-invoices/{id}/status', [ProformaInvoiceController::class, 'updateStatus']);
    Route::delete('/proforma-invoices/{id}/items/{productId}', [ProformaInvoiceController::class, 'removeItem']);
    Route::apiResource('pages', PageController::class)->except(['index', 'show']);
    Route::apiResource('user-groups', UserGroupController::class);
    Route::apiResource('user-group-members', UserGroupMemberController::class);
});

// =============================================
// VENDOR CUSTOMER PROTECTED ROUTES
// These routes are for customers created by vendors
// Customers can only see products from their vendor
// =============================================
Route::prefix('v1/customer')->middleware(['auth:sanctum', 'vendor.customer'])->group(function () {
    // Customer Auth
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/profile', [CustomerAuthController::class, 'profile']);
    Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
    Route::put('/change-password', [CustomerAuthController::class, 'changePassword']);
    
    // Customer Store - Only shows vendor's products
    Route::get('/home', [CustomerStoreController::class, 'home']);
    Route::get('/products', [CustomerStoreController::class, 'products']);
    Route::get('/products/{id}', [CustomerStoreController::class, 'productDetails']);
    Route::get('/categories', [CustomerStoreController::class, 'categories']);
    Route::get('/categories/{id}/subcategories', [CustomerStoreController::class, 'subcategories']);
    Route::get('/search', [CustomerStoreController::class, 'search']);
    
    // Customer Cart - Cart for vendor customers
    Route::get('/cart', [CustomerCartController::class, 'index']);
    Route::get('/cart/count', [CustomerCartController::class, 'count']);
    Route::post('/cart/add', [CustomerCartController::class, 'add']);
    Route::delete('/cart/clear', [CustomerCartController::class, 'clear']);
    Route::post('/cart/generate-invoice', [CustomerCartController::class, 'generateInvoice']);
    Route::put('/cart/{id}', [CustomerCartController::class, 'update']);
    Route::delete('/cart/{id}', [CustomerCartController::class, 'remove']);
    
    // Customer Invoices - Invoices for vendor customers
    Route::get('/invoices', [CustomerInvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [CustomerInvoiceController::class, 'show']);
    Route::get('/invoices/{id}/download-pdf', [CustomerInvoiceController::class, 'downloadPdf']);
    Route::post('/invoices/{id}/add-to-cart', [CustomerInvoiceController::class, 'addToCart']);
    Route::delete('/invoices/{id}', [CustomerInvoiceController::class, 'destroy']);
    Route::delete('/invoices/{id}/items/{productId}', [CustomerInvoiceController::class, 'removeItem']);
});