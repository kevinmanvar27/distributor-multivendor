<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\ProductView;
use App\Services\GeoLocationService;

class FrontendController extends Controller
{
    /**
     * Show the home page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch all active categories with their images and subcategories
        $categories = Category::where('is_active', true)
            ->with('image', 'subCategories')
            ->get()
            ->filter(function ($category) {
                // Check if category has any subcategories with products
                foreach ($category->subCategories as $subCategory) {
                    // Check if this subcategory has any products
                    $products = Product::where('status', 'published')
                        ->get()
                        ->filter(function ($product) use ($category, $subCategory) {
                            if (!$product->product_categories) {
                                return false;
                            }
                            
                            // Check if product belongs to both the main category and this specific subcategory
                            foreach ($product->product_categories as $catData) {
                                if (isset($catData['category_id']) && $catData['category_id'] == $category->id &&
                                    isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                    return true;
                                }
                            }
                            
                            return false;
                        });
                    
                    // If we found products in this subcategory, the category should be displayed
                    if ($products->count() > 0) {
                        return true;
                    }
                }
                
                // NEW: Check if the parent category itself has products (not in subcategories)
                $directCategoryProducts = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($category) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        // Check if product belongs directly to this category (without subcategories)
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                                // Check if subcategory_ids is empty or not set (meaning product is directly in category)
                                if (!isset($catData['subcategory_ids']) || empty($catData['subcategory_ids'])) {
                                    return true;
                                }
                            }
                        }
                        
                        return false;
                    });
                
                // If we found direct products in this category, display it
                if ($directCategoryProducts->count() > 0) {
                    return true;
                }
                
                // No subcategories with products or direct products found
                return false;
            })
            ->values()
            ->map(function ($category) {
                // Count products for this category (including both direct and subcategory products)
                $productCount = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($category) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        // Check if product belongs to this category (either directly or through subcategories)
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                                return true;
                            }
                        }
                        
                        return false;
                    })
                    ->count();
                
                // Add product count to category
                $category->product_count = $productCount;
                return $category;
            });
            
        // Fetch only published products with their main photos and variations (for stock calculation)
        // Note: Not loading galleryMedia due to implementation issues
        $products = Product::where('status', 'published')
            ->with(['mainPhoto', 'variations'])
            ->get();

        return view('frontend.home', compact('categories', 'products'));
    }
    
    /**
     * Show the user profile page
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        return view('frontend.profile', ['user' => Auth::user()]);
    }
    
    /**
     * Show the category detail page
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\View\View
     */
    public function showCategory(Category $category, Request $request)
    {
        // Check if category is active
        if (!$category->is_active) {
            abort(404);
        }
        
        // Load active subcategories with their images
        // Only show subcategories that have products
        $subCategories = $category->subCategories()
            ->where('is_active', true)
            ->with('image')
            ->get()
            ->filter(function ($subCategory) use ($category) {
                // Check if this subcategory has any products in this category
                $products = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($category, $subCategory) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        // Check if product belongs to both the main category and this specific subcategory
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $category->id &&
                                isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                return true;
                            }
                        }
                        
                        return false;
                    });
                
                return $products->count() > 0;
            })
            ->values();
            
        // Get the selected subcategory ID from the request
        $selectedSubcategoryId = $request->query('subcategory');
        
        // Get the sort parameter from the request
        $sort = $request->query('sort', 'default');
        
        // Load products associated with this category (published only)
        // Products store categories in a JSON array in the product_categories field
        // Load variations for stock calculation
        $products = Product::where('status', 'published')
            ->with(['mainPhoto', 'variations'])
            ->get()
            ->filter(function ($product) use ($category, $selectedSubcategoryId) {
                if (!$product->product_categories) {
                    return false;
                }
                
                // Check if product belongs to the main category
                $belongsToCategory = false;
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                        $belongsToCategory = true;
                        break;
                    }
                }
                
                if (!$belongsToCategory) {
                    return false;
                }
                
                // If a subcategory filter is applied, check if product belongs to that subcategory
                if ($selectedSubcategoryId) {
                    foreach ($product->product_categories as $catData) {
                        // Check if subcategory_ids array exists and contains the selected subcategory
                        if (isset($catData['subcategory_ids']) && in_array($selectedSubcategoryId, $catData['subcategory_ids'])) {
                            return true;
                        }
                    }
                    return false;
                }
                
                return true;
            })
            ->values();
            
        // Sort products based on the sort parameter
        if ($sort === 'name') {
            $products = $products->sortBy('name');
        } elseif ($sort === 'price-low') {
            $products = $products->sortBy('selling_price');
        } elseif ($sort === 'price-high') {
            $products = $products->sortByDesc('selling_price');
        }
            
        // SEO meta tags
        $metaTitle = $category->name . ' - ' . setting('site_title', 'Frontend App');
        $metaDescription = $category->description ?? 'Explore products in ' . $category->name . ' category';
        
        // If request is AJAX, return only the products partial view
        if ($request->ajax()) {
            return view('frontend.partials.products-list', compact('products'));
        }
        
        return view('frontend.category', compact('category', 'subCategories', 'products', 'metaTitle', 'metaDescription', 'selectedSubcategoryId', 'sort'));
    }
    
    /**
     * Show the product detail page
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function showProduct(Product $product)
    {
        // Check if product is published
        if ($product->status !== 'published') {
            abort(404);
        }
        
        // Load main photo and gallery media
        $product->load('mainPhoto', 'galleryMedia');
        
        // Track product view
        $this->trackProductView($product);
        
        // SEO meta tags
        $metaTitle = $product->name . ' - ' . setting('site_title', 'Frontend App');
        $metaDescription = $product->meta_description ?? Str::limit($product->description, 160);
        
        return view('frontend.product', compact('product', 'metaTitle', 'metaDescription'));
    }
    
    /**
     * Track product view for analytics
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    protected function trackProductView(Product $product)
    {
        try {
            $request = request();
            $sessionId = session()->getId();
            $userAgent = $request->userAgent();
            $ipAddress = $request->ip();
            
            // Prevent duplicate views from same session within 30 minutes
            $recentView = ProductView::where('product_id', $product->id)
                ->where('session_id', $sessionId)
                ->where('created_at', '>=', now()->subMinutes(30))
                ->exists();
            
            if (!$recentView) {
                // Get location data from IP
                $location = GeoLocationService::getLocation($ipAddress);
                
                ProductView::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'referrer' => $request->header('referer'),
                    'device_type' => ProductView::detectDeviceType($userAgent),
                    'browser' => ProductView::detectBrowser($userAgent),
                    'country' => $location['country'],
                    'country_code' => $location['country_code'],
                    'region' => $location['region'],
                    'city' => $location['city'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break product page if tracking fails
            Log::error('Product view tracking failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get subcategories for a category via AJAX
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function getSubcategories(Category $category)
    {
        // Load active subcategories with their images
        $subCategories = $category->subCategories()
            ->where('is_active', true)
            ->with('image')
            ->get();
            
        return response()->view('frontend.partials.subcategories', compact('subCategories'));
    }
    
    /**
     * Update the user profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate the request
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        
        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile_number = $request->mobile_number;
        $user->date_of_birth = $request->date_of_birth;
        $user->address = $request->address;
        $user->save();
        
        return redirect()->route('frontend.profile')->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Update the user's avatar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAvatar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate only the avatar field
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
        ], [
            'avatar.required' => 'Please select an image to upload.',
            'avatar.image' => 'The file must be an image.',
            'avatar.max' => 'The image may not be greater than 2MB.',
        ]);
        
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }
        
        // Store new avatar
        $avatarName = time() . '_' . $user->id . '.' . $request->file('avatar')->extension();
        $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
        $user->avatar = $avatarName;
        $user->save();
        
        return redirect()->route('frontend.profile')->with('success', 'Profile picture updated successfully.');
    }
    
    /**
     * Remove the user's avatar
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeAvatar()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Delete avatar file if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->avatar = null;
            $user->save();
        }
        
        return redirect()->route('frontend.profile')->with('success', 'Profile picture removed successfully.');
    }
    
    /**
     * Change the user's password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate the request
        $request->validate([
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'The new password must be different from your current password.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);
        
        // Update password
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('frontend.profile')->with('success', 'Password changed successfully.');
    }
}