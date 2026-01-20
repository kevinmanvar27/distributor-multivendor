<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Setting;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // Get the frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // If registration is disabled, redirect to login
        if ($accessPermission === 'registered_users_only') {
            return redirect()->route('frontend.login')->with('error', 'Registration is disabled. Only existing users can access the site.');
        }
        
        return view('frontend.auth.register');
    }

    /**
     * Handle registration request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Get the frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // If registration is disabled, redirect to login
        if ($accessPermission === 'registered_users_only') {
            return redirect()->route('frontend.login')->with('error', 'Registration is disabled. Only existing users can access the site.');
        }
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Users are never approved by default upon registration
        // They must either be approved by admin or login manually
        $isApproved = false;
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_role' => 'user',
            'is_approved' => $isApproved,
        ]);

        // Always redirect to login page with appropriate message
        if ($accessPermission === 'admin_approval_required') {
            // For admin approval required, show pending approval message
            return redirect()->route('frontend.login')->with('success', 'Registration successful. Your account is pending admin approval. Please wait for admin approval before logging in.');
        } else {
            // For other modes, still require manual login but account is active
            return redirect()->route('frontend.login')->with('success', 'Registration successful. Please login to access your account.');
        }
    }
}