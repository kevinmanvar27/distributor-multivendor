<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Vendor;

class ProfileController extends Controller
{
    /**
     * Show the vendor profile index page.
     */
    public function index()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        return view('vendor.profile.index', compact('user', 'vendor'));
    }

    /**
     * Show the vendor profile.
     */
    public function show()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        return view('vendor.profile.index', compact('user', 'vendor'));
    }

    /**
     * Show the store settings page.
     */
    public function storeSettings()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        $settings = $vendor->settings ?? [];
        
        return view('vendor.profile.store', compact('user', 'vendor', 'settings'));
    }

    /**
     * Update the vendor profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile_number' => 'nullable|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string|max:1000',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
        ]);

        // Update user info
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
        ]);

        // Update vendor info
        $vendor->update([
            'store_name' => $request->store_name,
            'store_description' => $request->store_description,
            'business_email' => $request->business_email,
            'business_phone' => $request->business_phone,
            'business_address' => $request->business_address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
        ]);

        return redirect()->route('vendor.profile.index')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the vendor address.
     */
    public function updateAddress(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        $request->validate([
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $vendor->update([
            'business_address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
        ]);

        return redirect()->route('vendor.profile.index')->with('success', 'Address updated successfully.');
    }

    /**
     * Update the vendor password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('vendor.profile.index')->with('success', 'Password changed successfully.');
    }

    /**
     * Update store settings.
     */
    public function updateStoreSettings(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        $request->validate([
            'tagline' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'return_policy' => 'nullable|string',
            'shipping_policy' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($vendor->store_banner) {
                Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
            }
            
            $file = $request->file('banner');
            $filename = time() . '_banner_' . $file->getClientOriginalName();
            $file->storeAs('vendor', $filename, 'public');
            $vendor->update(['store_banner' => $filename]);
        }

        // Get all settings from request
        $settings = $request->except(['_token', '_method', 'banner']);
        
        $vendor->update([
            'settings' => $settings,
        ]);

        return redirect()->route('vendor.profile.store')->with('success', 'Store settings updated successfully.');
    }

    /**
     * Update the vendor's avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        // Store new avatar
        $file = $request->file('avatar');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('avatars', $filename, 'public');

        $user->update(['avatar' => $filename]);

        return redirect()->route('vendor.profile.index')->with('success', 'Avatar updated successfully.');
    }

    /**
     * Remove the vendor's avatar.
     */
    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->update(['avatar' => null]);
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Avatar removed successfully.');
    }

    /**
     * Update the store logo.
     */
    public function updateStoreLogo(Request $request)
    {
        $request->validate([
            'store_logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $vendor = Auth::user()->vendor;

        // Delete old logo if exists
        if ($vendor->store_logo) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_logo);
        }

        // Store new logo
        $file = $request->file('store_logo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('vendor', $filename, 'public');

        $vendor->update(['store_logo' => $filename]);

        return redirect()->route('vendor.profile.index')->with('success', 'Store logo updated successfully.');
    }

    /**
     * Remove the store logo.
     */
    public function removeStoreLogo()
    {
        $vendor = Auth::user()->vendor;

        if ($vendor->store_logo) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_logo);
            $vendor->update(['store_logo' => null]);
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Store logo removed successfully.');
    }

    /**
     * Update the store banner.
     */
    public function updateStoreBanner(Request $request)
    {
        $request->validate([
            'store_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $vendor = Auth::user()->vendor;

        // Delete old banner if exists
        if ($vendor->store_banner) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
        }

        // Store new banner
        $file = $request->file('store_banner');
        $filename = time() . '_banner_' . $file->getClientOriginalName();
        $file->storeAs('vendor', $filename, 'public');

        $vendor->update(['store_banner' => $filename]);

        return redirect()->route('vendor.profile.index')->with('success', 'Store banner updated successfully.');
    }

    /**
     * Remove the store banner.
     */
    public function removeStoreBanner()
    {
        $vendor = Auth::user()->vendor;

        if ($vendor->store_banner) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
            $vendor->update(['store_banner' => null]);
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Store banner removed successfully.');
    }

    /**
     * Update bank details.
     */
    public function updateBankDetails(Request $request)
    {
        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc_code' => 'nullable|string|max:20',
            'bank_account_holder_name' => 'nullable|string|max:255',
        ]);

        $vendor = Auth::user()->vendor;

        $vendor->update([
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_ifsc_code' => $request->bank_ifsc_code,
            'bank_account_holder_name' => $request->bank_account_holder_name,
        ]);

        return redirect()->route('vendor.profile.index')->with('success', 'Bank details updated successfully.');
    }

    /**
     * Update social links.
     */
    public function updateSocialLinks(Request $request)
    {
        $request->validate([
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $vendor = Auth::user()->vendor;

        $vendor->update([
            'social_links' => [
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'instagram' => $request->instagram,
                'linkedin' => $request->linkedin,
                'youtube' => $request->youtube,
                'website' => $request->website,
            ],
        ]);

        return redirect()->route('vendor.profile.index')->with('success', 'Social links updated successfully.');
    }
}
