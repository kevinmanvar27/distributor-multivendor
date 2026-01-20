<?php

namespace App\Http\Controllers\API;

use App\Models\Wishlist;
use App\Models\Product;
use App\Models\ShoppingCartItem;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Wishlist",
 *     description="API Endpoints for User Wishlist Management"
 * )
 */
class WishlistController extends ApiController
{
    /**
     * Get user's wishlist
     * 
     * @OA\Get(
     *      path="/api/v1/wishlist",
     *      operationId="getWishlist",
     *      tags={"Wishlist"},
     *      summary="Get user's wishlist",
     *      description="Returns the authenticated user's wishlist with product details",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page (default: 15, max: 50)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="total", type="integer", example=10),
     *              ),
     *              @OA\Property(property="message", type="string", example="Wishlist retrieved successfully.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = min($request->per_page ?? 15, 50);
        
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with(['product' => function ($query) {
                $query->with('mainPhoto');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Add discounted price and stock info to each product
        $wishlistItems->getCollection()->transform(function ($item) use ($user) {
            if ($item->product) {
                $priceToUse = (!is_null($item->product->selling_price) && $item->product->selling_price !== '' && $item->product->selling_price >= 0) 
                    ? $item->product->selling_price 
                    : $item->product->mrp;
                
                $item->product->discounted_price = function_exists('calculateDiscountedPrice') 
                    ? calculateDiscountedPrice($priceToUse, $user) 
                    : $priceToUse;
                
                $item->product->is_available = $item->product->in_stock && 
                    in_array($item->product->status, ['active', 'published']);
            }
            
            return $item;
        });
        
        // Filter out items where product no longer exists
        $validItems = $wishlistItems->getCollection()->filter(function ($item) {
            return $item->product !== null;
        })->values();
        
        $wishlistItems->setCollection($validItems);
        
        return $this->sendResponse([
            'items' => $wishlistItems,
            'total' => Wishlist::where('user_id', $user->id)->count(),
        ], 'Wishlist retrieved successfully.');
    }
    
    /**
     * Add product to wishlist
     * 
     * @OA\Post(
     *      path="/api/v1/wishlist/{productId}",
     *      operationId="addToWishlist",
     *      tags={"Wishlist"},
     *      summary="Add product to wishlist",
     *      description="Add a product to the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to add",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Product added to wishlist",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="Product added to wishlist.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Product already in wishlist"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, $productId)
    {
        $user = $request->user();
        
        // Check if product exists and is published
        $product = Product::where('id', $productId)
            ->where('status', 'published')
            ->first();
        
        if (!$product) {
            return $this->sendError('Product not found or not available.', [], 404);
        }
        
        // Check if already in wishlist
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        
        if ($existing) {
            return $this->sendError('Product is already in your wishlist.', [], 400);
        }
        
        // Add to wishlist
        $wishlistItem = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);
        
        $wishlistItem->load(['product' => function ($query) {
            $query->with('mainPhoto');
        }]);
        
        return $this->sendResponse($wishlistItem, 'Product added to wishlist.', 201);
    }
    
    /**
     * Remove product from wishlist
     * 
     * @OA\Delete(
     *      path="/api/v1/wishlist/{productId}",
     *      operationId="removeFromWishlist",
     *      tags={"Wishlist"},
     *      summary="Remove product from wishlist",
     *      description="Remove a product from the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to remove",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product removed from wishlist",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Product removed from wishlist.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found in wishlist"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, $productId)
    {
        $user = $request->user();
        
        $wishlistItem = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        
        if (!$wishlistItem) {
            return $this->sendError('Product not found in your wishlist.', [], 404);
        }
        
        $wishlistItem->delete();
        
        return $this->sendResponse(null, 'Product removed from wishlist.');
    }
    
    /**
     * Check if product is in wishlist
     * 
     * @OA\Get(
     *      path="/api/v1/wishlist/check/{productId}",
     *      operationId="checkWishlist",
     *      tags={"Wishlist"},
     *      summary="Check if product is in wishlist",
     *      description="Check if a specific product is in the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to check",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="is_in_wishlist", type="boolean", example=true),
     *                  @OA\Property(property="added_at", type="string", format="date-time", nullable=true),
     *              ),
     *              @OA\Property(property="message", type="string", example="Wishlist status retrieved.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request, $productId)
    {
        $user = $request->user();
        
        $wishlistItem = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        
        return $this->sendResponse([
            'is_in_wishlist' => $wishlistItem !== null,
            'added_at' => $wishlistItem ? $wishlistItem->created_at : null,
        ], 'Wishlist status retrieved.');
    }
    
    /**
     * Move wishlist item to cart
     * 
     * @OA\Post(
     *      path="/api/v1/wishlist/{productId}/add-to-cart",
     *      operationId="wishlistToCart",
     *      tags={"Wishlist"},
     *      summary="Move wishlist item to cart",
     *      description="Add a product from wishlist to cart and optionally remove from wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to move to cart",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(property="quantity", type="integer", example=1, description="Quantity to add (default: 1)"),
     *              @OA\Property(property="remove_from_wishlist", type="boolean", example=true, description="Remove from wishlist after adding to cart (default: true)"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product added to cart",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="cart_item", type="object"),
     *                  @OA\Property(property="removed_from_wishlist", type="boolean"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Product added to cart.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Product out of stock or insufficient quantity"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found in wishlist"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request, $productId)
    {
        $user = $request->user();
        $quantity = $request->quantity ?? 1;
        $removeFromWishlist = $request->remove_from_wishlist ?? true;
        
        // Check if product is in wishlist
        $wishlistItem = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        
        if (!$wishlistItem) {
            return $this->sendError('Product not found in your wishlist.', [], 404);
        }
        
        // Get the product
        $product = Product::where('id', $productId)
            ->where('status', 'published')
            ->first();
        
        if (!$product) {
            return $this->sendError('Product is no longer available.', [], 404);
        }
        
        // Check stock
        if (!$product->in_stock || $product->stock_quantity < $quantity) {
            return $this->sendError('Product is out of stock or insufficient quantity available.', [
                'available_quantity' => $product->stock_quantity ?? 0,
                'requested_quantity' => $quantity,
            ], 400);
        }
        
        // Calculate price
        $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
            ? $product->selling_price 
            : $product->mrp;
        
        $discountedPrice = function_exists('calculateDiscountedPrice') 
            ? calculateDiscountedPrice($priceToUse, $user) 
            : $priceToUse;
        
        // Check if product already in cart
        $cartItem = ShoppingCartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        
        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $quantity;
            
            // Check if new quantity exceeds stock
            if ($product->stock_quantity < $quantity) {
                return $this->sendError('Cannot add more. Insufficient stock available.', [
                    'available_quantity' => $product->stock_quantity,
                    'current_cart_quantity' => $cartItem->quantity,
                    'requested_additional' => $quantity,
                ], 400);
            }
            
            $cartItem->quantity = $newQuantity;
            $cartItem->price = $discountedPrice;
            $cartItem->save();
            
            // REDUCE STOCK QUANTITY by the quantity being added
            $product->decrement('stock_quantity', $quantity);
        } else {
            // Create new cart item
            $cartItem = ShoppingCartItem::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $discountedPrice,
            ]);
            
            // REDUCE STOCK QUANTITY by the quantity being added
            $product->decrement('stock_quantity', $quantity);
        }
        
        // Update in_stock status if stock is depleted
        if ($product->fresh()->stock_quantity <= 0) {
            $product->update(['in_stock' => false]);
        }
        
        // Remove from wishlist if requested
        $removedFromWishlist = false;
        if ($removeFromWishlist) {
            $wishlistItem->delete();
            $removedFromWishlist = true;
        }
        
        $cartItem->load(['product' => function ($query) {
            $query->with('mainPhoto');
        }]);
        
        return $this->sendResponse([
            'cart_item' => $cartItem,
            'removed_from_wishlist' => $removedFromWishlist,
        ], 'Product added to cart.');
    }
    
    /**
     * Clear entire wishlist
     * 
     * @OA\Delete(
     *      path="/api/v1/wishlist/clear",
     *      operationId="clearWishlist",
     *      tags={"Wishlist"},
     *      summary="Clear entire wishlist",
     *      description="Remove all items from the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Wishlist cleared",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="items_removed", type="integer", example=5),
     *              ),
     *              @OA\Property(property="message", type="string", example="Wishlist cleared successfully.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        
        $itemsRemoved = Wishlist::where('user_id', $user->id)->delete();
        
        return $this->sendResponse([
            'items_removed' => $itemsRemoved,
        ], 'Wishlist cleared successfully.');
    }
}
