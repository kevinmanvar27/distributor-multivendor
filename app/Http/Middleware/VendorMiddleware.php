<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VendorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('vendor.login');
        }

        $user = Auth::user();

        // Check if user is a vendor or vendor staff
        if (!$user->hasVendorAccess()) {
            Auth::logout();
            return redirect()->route('vendor.login')->with('error', 'You do not have vendor access.');
        }

        // Check if vendor is approved
        if (!$user->isVendorApproved()) {
            $vendor = $user->vendor;
            
            if ($vendor && $vendor->isPending()) {
                return redirect()->route('vendor.pending');
            }
            
            if ($vendor && $vendor->isRejected()) {
                return redirect()->route('vendor.rejected');
            }
            
            if ($vendor && $vendor->isSuspended()) {
                return redirect()->route('vendor.suspended');
            }
            
            Auth::logout();
            return redirect()->route('vendor.login')->with('error', 'Your vendor account is not active.');
        }

        return $next($request);
    }
}
