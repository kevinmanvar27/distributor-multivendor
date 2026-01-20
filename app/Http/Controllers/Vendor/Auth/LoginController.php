<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show the vendor login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('vendor.auth.login');
    }

    /**
     * Handle vendor login request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user is a vendor or vendor staff
            if (!$user->hasVendorAccess()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'You do not have vendor access. Please register as a vendor first.',
                ]);
            }
            
            // Check vendor status
            $vendor = $user->vendor;
            
            if (!$vendor) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Vendor profile not found. Please contact support.',
                ]);
            }
            
            if ($vendor->isPending()) {
                return redirect()->route('vendor.pending');
            }
            
            if ($vendor->isRejected()) {
                return redirect()->route('vendor.rejected');
            }
            
            if ($vendor->isSuspended()) {
                return redirect()->route('vendor.suspended');
            }
            
            // Vendor is approved, redirect to dashboard
            return redirect()->intended('vendor/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the vendor out
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }
}
