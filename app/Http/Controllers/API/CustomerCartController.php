<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShoppingCartItem;
use App\Models\ProformaInvoice;
use App\Models\VendorCustomer;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Customer Cart",
 *     description="API Endpoints for Vendor Customer Shopping Cart"
 * )
 */
class CustomerCartController extends Controller
{
    /**
     * Get the authenticated customer
     */
    private function getCustomer(Request $request): VendorCustomer
    {
        return $request->user();
    }

    /**
     * Get customer's cart items
     */
    public function index(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        // Only get cart items for products from customer's vendor
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->with(['product.mainPhoto', 'product.vendor', 'variation'])
            ->get()
            ->filter(function ($item) use ($customer) {
                // Ensure product belongs to customer's vendor
                return $item->product && $item->product->vendor_id == $customer->vendor_id;
            });
        
        $total = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        $transformedItems = $cartItems->map(function ($item) use ($customer) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'product_name' => $item->product->name,
                'product_slug' => $item->product->slug,
                'variation_name' => $item->variation ? $item->variation->display_name : null,
                'variation_attributes' => $item->variation ? $item->variation->attributes : null,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->price * $item->quantity,
                'main_photo_url' => $item->product->mainPhoto?->url,
                'in_stock' => $item->variation ? $item->variation->in_stock : $item->product->in_stock,
                'stock_quantity' => $item->variation ? $item->variation->stock_quantity : $item->product->stock_quantity,
            ];
        })->values();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart items retrieved successfully',
            'data' => [
                'items' => $transformedItems,
                'total' => number_format($total, 2, '.', ''),
                'count' => $cartItems->count(),
                'customer_discount' => $customer->discount_percentage,
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $customer = $this->getCustomer($request);
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        $variation = null;

        // Ensure product belongs to customer's vendor
        if ($product->vendor_id != $customer->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Product not available',
                'data' => null
            ], 404);
        }

        // Handle variable products with variations
        if ($request->has('product_variation_id') && $request->product_variation_id) {
            $variation = ProductVariation::findOrFail($request->product_variation_id);
            
            // Validate variation belongs to product
            if ($variation->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variation for this product',
                    'data' => null
                ], 400);
            }
        }

        // Check if item already exists in cart
        $existingCartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('product_variation_id', $variation ? $variation->id : null)
            ->first();

        $existingQuantity = $existingCartItem ? $existingCartItem->quantity : 0;
        $totalQuantityNeeded = $existingQuantity + $quantity;

        // Check stock
        if ($variation) {
            if ($variation->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Only ' . $variation->stock_quantity . ' available.',
                    'data' => null
                ], 400);
            }
        } else {
            if (!$product->in_stock || $product->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Only ' . $product->stock_quantity . ' available.',
                    'data' => null
                ], 400);
            }
        }

        // Calculate price with customer discount
        if ($variation) {
            $basePrice = $variation->selling_price ?? $variation->mrp;
        } else {
            $basePrice = $product->selling_price ?? $product->mrp;
        }
        
        $discountedPrice = $customer->getDiscountedPrice($basePrice);

        // Add or update cart item
        $cartItem = ShoppingCartItem::updateOrCreate(
            [
                'vendor_customer_id' => $customer->id,
                'product_id' => $product->id,
                'product_variation_id' => $variation ? $variation->id : null,
            ],
            [
                'quantity' => $totalQuantityNeeded,
                'price' => $discountedPrice,
            ]
        );

        // Reduce stock
        if ($variation) {
            $variation->decrement('stock_quantity', $quantity);
            if ($variation->fresh()->stock_quantity <= 0) {
                $variation->update(['in_stock' => false]);
            }
        } else {
            $product->decrement('stock_quantity', $quantity);
            if ($product->fresh()->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
        }

        // Get updated cart count
        $cartCount = ShoppingCartItem::where('vendor_customer_id', $customer->id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'data' => [
                'cart_item' => [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $product->name,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total' => $cartItem->price * $cartItem->quantity,
                ],
                'cart_count' => $cartCount,
            ]
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $customer = $this->getCustomer($request);
        
        $cartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ], 404);
        }

        $product = $cartItem->product;
        $variation = $cartItem->variation;
        $oldQuantity = $cartItem->quantity;
        $newQuantity = $request->quantity;
        $quantityDifference = $newQuantity - $oldQuantity;

        // If increasing quantity, check stock
        if ($quantityDifference > 0) {
            if ($variation) {
                if ($variation->stock_quantity < $quantityDifference) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Only ' . $variation->stock_quantity . ' more available.',
                        'data' => null
                    ], 400);
                }
                $variation->decrement('stock_quantity', $quantityDifference);
            } else {
                if ($product->stock_quantity < $quantityDifference) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Only ' . $product->stock_quantity . ' more available.',
                        'data' => null
                    ], 400);
                }
                $product->decrement('stock_quantity', $quantityDifference);
            }
        } elseif ($quantityDifference < 0) {
            // Restore stock
            if ($variation) {
                $variation->increment('stock_quantity', abs($quantityDifference));
                if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                    $variation->update(['in_stock' => true]);
                }
            } else {
                $product->increment('stock_quantity', abs($quantityDifference));
                if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                    $product->update(['in_stock' => true]);
                }
            }
        }

        // Update in_stock status
        if (!$variation) {
            $product->refresh();
            if ($product->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
        }

        $cartItem->update(['quantity' => $newQuantity]);

        // Calculate totals
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)->get();
        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => [
                'cart_item' => [
                    'id' => $cartItem->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total' => $cartItem->price * $cartItem->quantity,
                ],
                'cart_total' => number_format($cartTotal, 2, '.', ''),
            ]
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $cartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ], 404);
        }

        // Restore stock
        $product = $cartItem->product;
        $variation = $cartItem->variation;
        
        if ($variation) {
            $variation->increment('stock_quantity', $cartItem->quantity);
            if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                $variation->update(['in_stock' => true]);
            }
        } elseif ($product) {
            $product->increment('stock_quantity', $cartItem->quantity);
            if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                $product->update(['in_stock' => true]);
            }
        }

        $cartItem->delete();

        // Get updated cart info
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)->get();
        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => [
                'cart_count' => $cartItems->count(),
                'cart_total' => number_format($cartTotal, 2, '.', ''),
            ]
        ]);
    }

    /**
     * Get cart count
     */
    public function count(Request $request)
    {
        $customer = $this->getCustomer($request);
        $cartCount = ShoppingCartItem::where('vendor_customer_id', $customer->id)->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart count retrieved successfully',
            'data' => [
                'cart_count' => $cartCount
            ]
        ]);
    }

    /**
     * Clear all items from cart
     */
    public function clear(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->with(['product', 'variation'])
            ->get();
        
        // Restore stock for all items
        foreach ($cartItems as $cartItem) {
            if ($cartItem->variation) {
                $cartItem->variation->increment('stock_quantity', $cartItem->quantity);
                if ($cartItem->variation->fresh()->stock_quantity > 0 && !$cartItem->variation->in_stock) {
                    $cartItem->variation->update(['in_stock' => true]);
                }
            } elseif ($cartItem->product) {
                $cartItem->product->increment('stock_quantity', $cartItem->quantity);
                if ($cartItem->product->fresh()->stock_quantity > 0 && !$cartItem->product->in_stock) {
                    $cartItem->product->update(['in_stock' => true]);
                }
            }
        }
        
        ShoppingCartItem::where('vendor_customer_id', $customer->id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => [
                'items_removed' => $cartItems->count()
            ]
        ]);
    }

    /**
     * Generate proforma invoice from cart
     */
    public function generateInvoice(Request $request)
    {
        $customer = $this->getCustomer($request);
        $vendor = $customer->vendor;
        
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->with(['product.mainPhoto', 'product.vendor', 'variation'])
            ->get()
            ->filter(function ($item) use ($customer) {
                return $item->product && $item->product->vendor_id == $customer->vendor_id;
            });

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
                'data' => null
            ], 400);
        }

        $invoiceDate = now()->format('Y-m-d');
        
        // Calculate total
        $total = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Prepare invoice data with store details
        $invoiceData = [
            'cart_items' => $cartItems->map(function ($item) {
                $itemData = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_slug' => $item->product->slug,
                    'product_description' => $item->product->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                    'main_photo_url' => $item->product->mainPhoto?->url,
                ];
                
                if ($item->product_variation_id && $item->variation) {
                    $itemData['product_variation_id'] = $item->product_variation_id;
                    $itemData['variation_display_name'] = $item->variation->display_name;
                    $itemData['variation_attributes'] = $item->variation->formatted_attributes ?? $item->variation->attributes;
                    $itemData['variation_sku'] = $item->variation->sku;
                }
                
                return $itemData;
            })->values()->toArray(),
            'total' => $total,
            'invoice_date' => $invoiceDate,
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
            ],
            'store' => [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_logo_url' => $vendor->store_logo_url,
                'business_name' => $vendor->business_name,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number ?? null,
            ],
        ];

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Create proforma invoice
        $proformaInvoice = ProformaInvoice::create([
            'invoice_number' => $invoiceNumber,
            'vendor_id' => $vendor->id,
            'vendor_customer_id' => $customer->id,
            'total_amount' => $total,
            'invoice_data' => $invoiceData,
            'status' => ProformaInvoice::STATUS_DRAFT,
        ]);

        // Clear the cart
        ShoppingCartItem::where('vendor_customer_id', $customer->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proforma invoice generated successfully',
            'data' => [
                'invoice' => [
                    'id' => $proformaInvoice->id,
                    'invoice_number' => $proformaInvoice->invoice_number,
                    'total_amount' => $proformaInvoice->total_amount,
                    'status' => $proformaInvoice->status,
                    'created_at' => $proformaInvoice->created_at,
                ],
                'invoice_data' => $invoiceData,
            ]
        ], 201);
    }

    /**
     * Generate a serialized invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($year, $prefix) {
            $latestInvoice = ProformaInvoice::where('invoice_number', 'like', $prefix . '%')
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latestInvoice) {
                $parts = explode('-', $latestInvoice->invoice_number);
                if (count($parts) >= 3 && $parts[1] == $year) {
                    $sequence = (int)$parts[2] + 1;
                } else {
                    $sequence = 1;
                }
            } else {
                $sequence = 1;
            }

            return "INV-{$year}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        });
    }
}
