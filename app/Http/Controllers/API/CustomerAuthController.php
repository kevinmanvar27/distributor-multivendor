<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\VendorCustomer;
use App\Models\Vendor;

/**
 * @OA\Tag(
 *     name="Customer Auth",
 *     description="API Endpoints for Vendor Customer Authentication"
 * )
 */
class CustomerAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/customer/login",
     *     summary="Customer login",
     *     description="Authenticate a vendor customer and return an access token. Customer can only see products from their vendor.",
     *     operationId="customerLogin",
     *     tags={"Customer Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="customer@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123"),
     *             @OA\Property(property="vendor_slug", type="string", example="johns-store", description="Optional: The vendor store slug or ID. If not provided, will search by email.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful login"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Account is inactive"),
     *     @OA\Response(response=404, description="Customer not found")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'vendor_slug' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $customer = null;
        $vendor = null;

        // If vendor_slug is provided, find customer for that specific vendor
        if ($request->filled('vendor_slug')) {
            $vendor = Vendor::where('store_slug', $request->vendor_slug)
                ->orWhere('id', $request->vendor_slug)
                ->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found',
                    'data' => null
                ], 404);
            }

            if (!$vendor->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This vendor store is not available',
                    'data' => null
                ], 403);
            }

            $customer = VendorCustomer::where('vendor_id', $vendor->id)
                ->where('email', $request->email)
                ->first();
        } else {
            // No vendor_slug provided - find customer by email only
            $customer = VendorCustomer::where('email', $request->email)
                ->whereNotNull('password')
                ->first();
            
            if ($customer) {
                $vendor = $customer->vendor;
            }
        }

        // Validate customer and password
        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null
            ], 401);
        }

        // Check if vendor is approved
        if (!$vendor || !$vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'This vendor store is not available',
                'data' => null
            ], 403);
        }

        if (!$customer->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact the vendor.',
                'data' => null
            ], 403);
        }

        // Update last login
        $customer->updateLastLogin();

        // Create token
        $token = $customer->createToken('customer-token', ['customer'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'mobile_number' => $customer->mobile_number,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'discount_percentage' => $customer->discount_percentage,
                    'last_login_at' => $customer->last_login_at,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer/logout",
     *     summary="Customer logout",
     *     description="Logout the authenticated customer",
     *     operationId="customerLogout",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful logout"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/profile",
     *     summary="Get customer profile",
     *     description="Get the authenticated customer's profile",
     *     operationId="getCustomerProfile",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function profile(Request $request)
    {
        $customer = $request->user();
        $vendor = $customer->vendor;

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'mobile_number' => $customer->mobile_number,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'discount_percentage' => $customer->discount_percentage,
                    'created_at' => $customer->created_at,
                    'last_login_at' => $customer->last_login_at,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'business_phone' => $vendor->business_phone,
                    'business_email' => $vendor->business_email,
                ]
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/customer/profile",
     *     summary="Update customer profile",
     *     description="Update the authenticated customer's profile",
     *     operationId="updateCustomerProfile",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="mobile_number", type="string", example="9876543210"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="postal_code", type="string", example="400001")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateProfile(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'mobile_number' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $customer->update($request->only([
            'name',
            'mobile_number',
            'address',
            'city',
            'state',
            'postal_code',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'mobile_number' => $customer->mobile_number,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                ]
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/customer/change-password",
     *     summary="Change customer password",
     *     description="Change the authenticated customer's password",
     *     operationId="changeCustomerPassword",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password","password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="OldPassword123"),
     *             @OA\Property(property="password", type="string", format="password", example="NewPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password changed successfully"),
     *     @OA\Response(response=401, description="Unauthenticated or current password incorrect"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function changePassword(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null
            ], 401);
        }

        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            'data' => null
        ]);
    }
}
