<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVendorPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // If user is not authenticated, redirect to vendor login
        if (!Auth::check()) {
            return redirect()->route('vendor.login');
        }

        $user = Auth::user();
        
        // Vendor owners have all permissions
        if ($user->isVendor()) {
            return $next($request);
        }
        
        // Check if vendor staff has any of the required permissions
        if ($user->isVendorStaff()) {
            $staffRecord = $user->vendorStaff;
            
            if ($staffRecord && $staffRecord->hasAnyPermission($permissions)) {
                return $next($request);
            }
        }
        
        // Redirect back with error message
        return redirect()->back()->with('error', 'You do not have permission to perform this action.');
    }
}
