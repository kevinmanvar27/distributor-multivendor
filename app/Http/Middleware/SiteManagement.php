<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SiteManagement
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $setting = Setting::first();
        
        if ($setting) {
            // Check if maintenance mode is enabled
            if ($setting->maintenance_mode) {
                // Auto-disable maintenance mode if end time has passed
                if ($setting->maintenance_end_time && now()->greaterThan($setting->maintenance_end_time)) {
                    $setting->maintenance_mode = false;
                    $setting->save();
                } 
                // If still enabled and user is not admin, show maintenance page
                elseif (!Auth::check() || !$this->isUserAdmin(Auth::user())) {
                    return response()->view('errors.maintenance', compact('setting'), 503);
                }
            }
            // Only check coming soon mode if maintenance mode is not enabled
            else if ($setting->coming_soon_mode) {
                // Auto-disable coming soon mode if launch time has passed
                if ($setting->launch_time && now()->greaterThan($setting->launch_time)) {
                    $setting->coming_soon_mode = false;
                    $setting->save();
                } 
                // If still enabled and user is not authenticated, show coming soon page
                elseif (!Auth::check()) {
                    return response()->view('errors.coming-soon', compact('setting'));
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Check if the user is an admin
     *
     * @param mixed $user
     * @return bool
     */
    private function isUserAdmin($user): bool
    {
        if (!$user instanceof User) {
            return false;
        }
        
        return $user->isAdmin();
    }
}