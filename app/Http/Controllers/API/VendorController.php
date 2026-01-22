<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ProformaInvoice;
use App\Models\Lead;
use App\Models\Coupon;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Vendor",
 *     description="API Endpoints for Vendor Management"
 * )
 */
class VendorController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/vendor/register",
     *     summary="Register as a vendor",
     *     description="Register a new vendor account",
     *     operationId="vendorRegister",
     *     tags={"Vendor"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","store_name","business_phone"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="vendor@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Password123"),
     *             @OA\Property(property="store_name", type="string", example="John's Electronics Store"),
     *             @OA\Property(property="store_description", type="string", example="Best electronics store in town"),
     *             @OA\Property(property="business_email", type="string", format="email", example="business@example.com"),
     *             @OA\Property(property="business_phone", type="string", example="9876543210"),
     *             @OA\Property(property="business_address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="country", type="string", example="India"),
     *             @OA\Property(property="postal_code", type="string", example="400001"),
     *             @OA\Property(property="gst_number", type="string", example="27AABCU9603R1ZM"),
     *             @OA\Property(property="pan_number", type="string", example="ABCDE1234F")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Vendor registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'required|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_vendor' => true,
        ]);

        // Create vendor profile
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => $request->store_name,
            'store_description' => $request->store_description,
            'business_email' => $request->business_email ?? $request->email,
            'business_phone' => $request->business_phone,
            'business_address' => $request->business_address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'status' => Vendor::STATUS_PENDING,
            'commission_rate' => 10.00, // Default commission rate
        ]);

        $token = $user->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Vendor registered successfully. Your account is pending approval.',
            'data' => [
                'user' => $user,
                'vendor' => $vendor,
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/login",
     *     summary="Vendor login",
     *     description="Authenticate a vendor and return an access token",
     *     operationId="vendorLogin",
     *     tags={"Vendor"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="vendor@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful login"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Vendor account not approved")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->vendor) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not registered as a vendor'
            ], 403);
        }

        $vendor = $user->vendor;
        $statusMessage = null;

        if ($vendor->status === Vendor::STATUS_PENDING) {
            $statusMessage = 'Your vendor account is pending approval.';
        } elseif ($vendor->status === Vendor::STATUS_REJECTED) {
            $statusMessage = 'Your vendor account has been rejected. Reason: ' . ($vendor->rejection_reason ?? 'Not specified');
        } elseif ($vendor->status === Vendor::STATUS_SUSPENDED) {
            $statusMessage = 'Your vendor account has been suspended.';
        }

        $token = $user->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'vendor' => $vendor,
                'token' => $token,
                'vendor_status' => $vendor->status,
                'status_message' => $statusMessage,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/dashboard",
     *     summary="Get vendor dashboard data",
     *     description="Returns vendor dashboard statistics and analytics",
     *     operationId="vendorDashboard",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor")
     * )
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 403);
        }

        // Basic counts
        $productCount = Product::where('vendor_id', $vendor->id)->count();
        $categoryCount = Category::where('vendor_id', $vendor->id)->count();
        $activeProductCount = Product::where('vendor_id', $vendor->id)->where('status', 'published')->count();

        // Get vendor orders
        $vendorOrders = $this->getVendorOrders($vendor->id);

        // Revenue statistics
        $totalRevenue = $vendorOrders['delivered']->sum('vendor_total');
        $monthlyRevenue = $vendorOrders['delivered']
            ->filter(fn($order) => Carbon::parse($order['created_at'])->isCurrentMonth())
            ->sum('vendor_total');

        $lastMonthRevenue = $vendorOrders['delivered']
            ->filter(function($order) {
                $date = Carbon::parse($order['created_at']);
                return $date->month === Carbon::now()->subMonth()->month 
                    && $date->year === Carbon::now()->subMonth()->year;
            })
            ->sum('vendor_total');

        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
            : ($monthlyRevenue > 0 ? 100 : 0);

        // Order statistics
        $totalOrders = $vendorOrders['all']->count();
        $pendingOrders = $vendorOrders['pending']->count();
        $deliveredOrders = $vendorOrders['delivered']->count();

        // Today's statistics
        $todayOrders = $vendorOrders['all']
            ->filter(fn($order) => Carbon::parse($order['created_at'])->isToday())
            ->count();
        $todayRevenue = $vendorOrders['delivered']
            ->filter(fn($order) => Carbon::parse($order['created_at'])->isToday())
            ->sum('vendor_total');

        // Low stock products count
        $lowStockCount = Product::where('vendor_id', $vendor->id)
            ->where('in_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        // Out of stock count
        $outOfStockCount = Product::where('vendor_id', $vendor->id)
            ->where(function($query) {
                $query->where('in_stock', false)
                      ->orWhere('stock_quantity', '<=', 0);
            })->count();

        // Commission info
        $commissionRate = $vendor->commission_rate;
        $totalCommission = $totalRevenue * ($commissionRate / 100);
        $netEarnings = $totalRevenue - $totalCommission;

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'status' => $vendor->status,
                    'store_logo_url' => $vendor->store_logo_url,
                ],
                'statistics' => [
                    'products' => [
                        'total' => $productCount,
                        'active' => $activeProductCount,
                        'low_stock' => $lowStockCount,
                        'out_of_stock' => $outOfStockCount,
                    ],
                    'categories' => $categoryCount,
                    'orders' => [
                        'total' => $totalOrders,
                        'pending' => $pendingOrders,
                        'delivered' => $deliveredOrders,
                        'today' => $todayOrders,
                    ],
                    'revenue' => [
                        'total' => round($totalRevenue, 2),
                        'monthly' => round($monthlyRevenue, 2),
                        'today' => round($todayRevenue, 2),
                        'growth_percentage' => $revenueGrowth,
                    ],
                    'commission' => [
                        'rate' => $commissionRate,
                        'total_commission' => round($totalCommission, 2),
                        'net_earnings' => round($netEarnings, 2),
                    ],
                ],
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/profile",
     *     summary="Get vendor profile",
     *     description="Returns the authenticated vendor's profile data",
     *     operationId="getVendorProfile",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendor profile retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_description' => $vendor->store_description,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'business_email' => $vendor->business_email,
                    'business_phone' => $vendor->business_phone,
                    'business_address' => $vendor->business_address,
                    'city' => $vendor->city,
                    'state' => $vendor->state,
                    'country' => $vendor->country,
                    'postal_code' => $vendor->postal_code,
                    'gst_number' => $vendor->gst_number,
                    'pan_number' => $vendor->pan_number,
                    'bank_name' => $vendor->bank_name,
                    'bank_account_number' => $vendor->bank_account_number ? '****' . substr($vendor->bank_account_number, -4) : null,
                    'bank_ifsc_code' => $vendor->bank_ifsc_code,
                    'bank_account_holder_name' => $vendor->bank_account_holder_name,
                    'commission_rate' => $vendor->commission_rate,
                    'status' => $vendor->status,
                    'is_featured' => $vendor->is_featured,
                    'social_links' => $vendor->social_links,
                    'approved_at' => $vendor->approved_at,
                    'created_at' => $vendor->created_at,
                ],
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/profile",
     *     summary="Update vendor profile",
     *     description="Update the authenticated vendor's profile data",
     *     operationId="updateVendorProfile",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="store_name", type="string", example="John's Store"),
     *             @OA\Property(property="store_description", type="string", example="Best store in town"),
     *             @OA\Property(property="business_email", type="string", format="email", example="business@example.com"),
     *             @OA\Property(property="business_phone", type="string", example="9876543210"),
     *             @OA\Property(property="business_address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="country", type="string", example="India"),
     *             @OA\Property(property="postal_code", type="string", example="400001"),
     *             @OA\Property(property="gst_number", type="string", example="27AABCU9603R1ZM"),
     *             @OA\Property(property="pan_number", type="string", example="ABCDE1234F")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'store_name' => 'sometimes|string|max:255',
            'store_description' => 'nullable|string',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'sometimes|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Update user name if provided
        if ($request->has('name')) {
            $user->update(['name' => $request->name]);
        }

        // Update vendor profile
        $vendor->update($request->only([
            'store_name', 'store_description', 'business_email', 'business_phone',
            'business_address', 'city', 'state', 'country', 'postal_code',
            'gst_number', 'pan_number'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user->fresh(),
                'vendor' => $vendor->fresh(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/store-logo",
     *     summary="Upload store logo",
     *     description="Upload or update the vendor's store logo",
     *     operationId="uploadStoreLogo",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="logo", type="string", format="binary", description="Store logo image file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Logo uploaded successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function uploadStoreLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        // Delete old logo if exists
        if ($vendor->store_logo) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_logo);
            Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_logo);
        }

        // Store new logo
        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('vendor/' . $vendor->id, $filename, 'public');

        $vendor->update(['store_logo' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Store logo uploaded successfully',
            'data' => [
                'store_logo_url' => $vendor->fresh()->store_logo_url,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/store-banner",
     *     summary="Upload store banner",
     *     description="Upload or update the vendor's store banner",
     *     operationId="uploadStoreBanner",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="banner", type="string", format="binary", description="Store banner image file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Banner uploaded successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function uploadStoreBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        // Delete old banner if exists
        if ($vendor->store_banner) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
            Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_banner);
        }

        // Store new banner
        $file = $request->file('banner');
        $filename = 'banner_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('vendor/' . $vendor->id, $filename, 'public');

        $vendor->update(['store_banner' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Store banner uploaded successfully',
            'data' => [
                'store_banner_url' => $vendor->fresh()->store_banner_url,
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/bank-details",
     *     summary="Update bank details",
     *     description="Update the vendor's bank account details",
     *     operationId="updateBankDetails",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bank_name","bank_account_number","bank_ifsc_code","bank_account_holder_name"},
     *             @OA\Property(property="bank_name", type="string", example="State Bank of India"),
     *             @OA\Property(property="bank_account_number", type="string", example="1234567890123456"),
     *             @OA\Property(property="bank_ifsc_code", type="string", example="SBIN0001234"),
     *             @OA\Property(property="bank_account_holder_name", type="string", example="John Doe")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Bank details updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateBankDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_ifsc_code' => 'required|string|max:20',
            'bank_account_holder_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $vendor->update($request->only([
            'bank_name', 'bank_account_number', 'bank_ifsc_code', 'bank_account_holder_name'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Bank details updated successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/products",
     *     summary="Get vendor products",
     *     description="Returns list of vendor's products with pagination",
     *     operationId="getVendorProducts",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status (published, draft)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", description="Search by product name", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", description="Filter by category", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function products(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $query = Product::where('vendor_id', $vendor->id)
            ->with(['mainPhoto', 'variations']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->whereJsonContains('product_categories', [['category_id' => (int)$request->category_id]]);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/products",
     *     summary="Create a new product",
     *     description="Create a new product for the vendor",
     *     operationId="createVendorProduct",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","mrp","status"},
     *             @OA\Property(property="name", type="string", example="New Product"),
     *             @OA\Property(property="description", type="string", example="Product description"),
     *             @OA\Property(property="mrp", type="number", format="float", example=999.99),
     *             @OA\Property(property="selling_price", type="number", format="float", example=899.99),
     *             @OA\Property(property="in_stock", type="boolean", example=true),
     *             @OA\Property(property="stock_quantity", type="integer", example=100),
     *             @OA\Property(property="low_quantity_threshold", type="integer", example=10),
     *             @OA\Property(property="status", type="string", example="published"),
     *             @OA\Property(property="product_type", type="string", example="simple"),
     *             @OA\Property(property="main_photo_id", type="integer", example=1),
     *             @OA\Property(property="product_gallery", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="product_categories", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Product created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createProduct(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        if (!$vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Your vendor account must be approved to create products'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'in_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:published,draft',
            'product_type' => 'nullable|in:simple,variable',
            'main_photo_id' => 'nullable|integer|exists:media,id',
            'product_gallery' => 'nullable|array',
            'product_categories' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => $request->name,
            'description' => $request->description,
            'mrp' => $request->mrp,
            'selling_price' => $request->selling_price ?? $request->mrp,
            'in_stock' => $request->in_stock ?? true,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'low_quantity_threshold' => $request->low_quantity_threshold ?? 10,
            'status' => $request->status,
            'product_type' => $request->product_type ?? 'simple',
            'main_photo_id' => $request->main_photo_id,
            'product_gallery' => $request->product_gallery,
            'product_categories' => $request->product_categories,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['mainPhoto', 'variations'])
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/products/{id}",
     *     summary="Update a product",
     *     description="Update an existing product",
     *     operationId="updateVendorProduct",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Product"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="mrp", type="number", format="float", example=999.99),
     *             @OA\Property(property="selling_price", type="number", format="float", example=899.99),
     *             @OA\Property(property="in_stock", type="boolean", example=true),
     *             @OA\Property(property="stock_quantity", type="integer", example=100),
     *             @OA\Property(property="status", type="string", example="published")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function updateProduct(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'mrp' => 'sometimes|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'in_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'sometimes|in:published,draft',
            'product_type' => 'nullable|in:simple,variable',
            'main_photo_id' => 'nullable|integer|exists:media,id',
            'product_gallery' => 'nullable|array',
            'product_categories' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->fresh()->load(['mainPhoto', 'variations'])
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendor/products/{id}",
     *     summary="Delete a product",
     *     description="Delete a product",
     *     operationId="deleteVendorProduct",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function deleteProduct(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/orders",
     *     summary="Get vendor orders",
     *     description="Returns list of orders containing vendor's products",
     *     operationId="getVendorOrders",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function orders(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $vendorOrders = $this->getVendorOrders($vendor->id);
        $orders = $vendorOrders['all'];

        // Apply status filter
        if ($request->has('status')) {
            $orders = $orders->where('status', $request->status);
        }

        // Paginate
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 15), 50);
        $total = $orders->count();
        $orders = $orders->forPage($page, $perPage)->values();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/orders/{id}",
     *     summary="Get order details",
     *     description="Returns details of a specific order",
     *     operationId="getVendorOrderDetails",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Order/Invoice ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function orderDetails(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $invoice = ProformaInvoice::with('user')->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if this order contains vendor's products
        $invoiceData = $invoice->invoice_data;
        $vendorItems = [];
        $vendorTotal = 0;

        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                $productId = $item['product_id'] ?? $item['id'] ?? null;
                if ($productId) {
                    $product = Product::find($productId);
                    if ($product && $product->vendor_id == $vendor->id) {
                        $vendorItems[] = $item;
                        $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                    }
                }
            }
        }

        if (empty($vendorItems)) {
            return response()->json([
                'success' => false,
                'message' => 'This order does not contain your products'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order details retrieved successfully',
            'data' => [
                'order' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ],
                'customer' => [
                    'id' => $invoice->user->id ?? null,
                    'name' => $invoice->user->name ?? 'Guest',
                    'email' => $invoice->user->email ?? null,
                ],
                'vendor_items' => $vendorItems,
                'vendor_total' => round($vendorTotal, 2),
                'shipping_address' => $invoiceData['shipping_address'] ?? null,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/analytics",
     *     summary="Get vendor analytics",
     *     description="Returns detailed analytics data for the vendor",
     *     operationId="getVendorAnalytics",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="period", in="query", description="Period (weekly, monthly, yearly)", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function analytics(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $period = $request->get('period', 'monthly');
        $vendorOrders = $this->getVendorOrders($vendor->id);

        // Revenue chart data
        if ($period === 'yearly') {
            $revenueData = $this->getYearlyRevenueData($vendorOrders['delivered']);
        } elseif ($period === 'weekly') {
            $revenueData = $this->getWeeklyRevenueData($vendorOrders['delivered']);
        } else {
            $revenueData = $this->getMonthlyRevenueData($vendorOrders['delivered']);
        }

        // Order status distribution
        $orderStatusData = [
            ['status' => 'Draft', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DRAFT)->count()],
            ['status' => 'Approved', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_APPROVED)->count()],
            ['status' => 'Dispatch', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DISPATCH)->count()],
            ['status' => 'Out for Delivery', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_OUT_FOR_DELIVERY)->count()],
            ['status' => 'Delivered', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DELIVERED)->count()],
            ['status' => 'Return', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_RETURN)->count()],
        ];

        // Top selling products
        $topProducts = $this->getTopSellingProducts($vendorOrders['delivered'], $vendor->id);

        return response()->json([
            'success' => true,
            'message' => 'Analytics data retrieved successfully',
            'data' => [
                'revenue_chart' => $revenueData,
                'order_status_distribution' => $orderStatusData,
                'top_selling_products' => $topProducts,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/low-stock-products",
     *     summary="Get low stock products",
     *     description="Returns list of products with low stock",
     *     operationId="getLowStockProducts",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function lowStockProducts(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $products = Product::where('vendor_id', $vendor->id)
            ->where('in_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity', 'asc')
            ->with('mainPhoto')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Low stock products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * Helper method to get vendor orders
     */
    private function getVendorOrders($vendorId)
    {
        $allInvoices = ProformaInvoice::whereNotNull('invoice_data')->get();
        
        $vendorOrders = collect();
        
        foreach ($allInvoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            $vendorTotal = 0;
            $hasVendorProducts = false;
            
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $hasVendorProducts = true;
                            $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                        }
                    }
                }
            }
            
            if ($hasVendorProducts) {
                $vendorOrders->push([
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-' . $invoice->id,
                    'user' => $invoice->user ? [
                        'id' => $invoice->user->id,
                        'name' => $invoice->user->name,
                        'email' => $invoice->user->email,
                    ] : null,
                    'status' => $invoice->status,
                    'vendor_total' => round($vendorTotal, 2),
                    'created_at' => $invoice->created_at,
                ]);
            }
        }
        
        return [
            'all' => $vendorOrders->sortByDesc('created_at'),
            'delivered' => $vendorOrders->where('status', ProformaInvoice::STATUS_DELIVERED),
            'pending' => $vendorOrders->whereIn('status', [
                ProformaInvoice::STATUS_DRAFT,
                ProformaInvoice::STATUS_APPROVED,
                ProformaInvoice::STATUS_DISPATCH,
                ProformaInvoice::STATUS_OUT_FOR_DELIVERY
            ]),
        ];
    }

    /**
     * Helper method to get monthly revenue data
     */
    private function getMonthlyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    $orderDate = Carbon::parse($order['created_at']);
                    return $orderDate->month === $date->month && $orderDate->year === $date->year;
                })
                ->sum('vendor_total');
            
            $data[] = [
                'label' => $date->format('M Y'),
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Helper method to get weekly revenue data
     */
    private function getWeeklyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    return Carbon::parse($order['created_at'])->isSameDay($date);
                })
                ->sum('vendor_total');
            
            $data[] = [
                'label' => $date->format('D, M d'),
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Helper method to get yearly revenue data
     */
    private function getYearlyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $revenue = $deliveredOrders
                ->filter(function($order) use ($year) {
                    return Carbon::parse($order['created_at'])->year === $year;
                })
                ->sum('vendor_total');
            
            $data[] = [
                'label' => (string)$year,
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Helper method to get top selling products
     */
    private function getTopSellingProducts($deliveredOrders, $vendorId)
    {
        $productSales = [];
        
        foreach ($deliveredOrders as $order) {
            $invoice = ProformaInvoice::find($order['id']);
            if (!$invoice) continue;
            
            $invoiceData = $invoice->invoice_data;
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $productName = $item['name'] ?? $item['product_name'] ?? 'Unknown Product';
                            $quantity = $item['quantity'] ?? 1;
                            $total = $item['total'] ?? ($item['price'] ?? 0) * $quantity;
                            
                            if (!isset($productSales[$productId])) {
                                $productSales[$productId] = [
                                    'id' => $productId,
                                    'name' => $productName,
                                    'quantity_sold' => 0,
                                    'revenue' => 0
                                ];
                            }
                            $productSales[$productId]['quantity_sold'] += $quantity;
                            $productSales[$productId]['revenue'] += $total;
                        }
                    }
                }
            }
        }
        
        usort($productSales, function($a, $b) {
            return $b['quantity_sold'] - $a['quantity_sold'];
        });
        
        return array_slice(array_values($productSales), 0, 10);
    }
}
