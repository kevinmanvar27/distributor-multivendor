<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Vendor;

class RegisterController extends Controller
{
    /**
     * Show the vendor registration form
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('vendor.auth.register');
    }

    /**
     * Handle vendor registration request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'required|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string|max:1000',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'terms' => 'required|accepted',
        ]);

        DB::beginTransaction();

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile_number' => $request->mobile_number,
                'user_role' => 'vendor',
                'is_approved' => false,
            ]);

            // Create the vendor profile
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $request->store_name,
                'store_slug' => Str::slug($request->store_name),
                'store_description' => $request->store_description,
                'business_email' => $request->business_email ?? $request->email,
                'business_phone' => $request->business_phone ?? $request->mobile_number,
                'business_address' => $request->business_address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'status' => Vendor::STATUS_PENDING,
            ]);

            DB::commit();

            // Log the user in
            Auth::login($user);

            // Redirect to pending page
            return redirect()->route('vendor.pending')->with('success', 'Your vendor registration has been submitted successfully. Please wait for admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->withErrors([
                'error' => 'Registration failed. Please try again. ' . $e->getMessage(),
            ]);
        }
    }
}
