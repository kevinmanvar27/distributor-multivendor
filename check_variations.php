<?php
/**
 * Quick diagnostic script to check variation products and cart items
 * Run this from browser: http://127.0.0.1:8000/check_variations.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Variation Products Diagnostic</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; }
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>";

// Check 1: Variable Products
echo "<h2>1. Variable Products in Database</h2>";
$products = DB::table('products')
    ->where('product_type', 'variable')
    ->select('id', 'name', 'product_type', 'sku')
    ->get();

if ($products->isEmpty()) {
    echo "<p class='error'>❌ No variable products found!</p>";
    echo "<p>You need to create a variable product first.</p>";
} else {
    echo "<p class='success'>✅ Found " . $products->count() . " variable product(s)</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>SKU</th></tr>";
    foreach ($products as $product) {
        echo "<tr>";
        echo "<td>{$product->id}</td>";
        echo "<td>{$product->name}</td>";
        echo "<td>{$product->product_type}</td>";
        echo "<td>{$product->sku}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check 2: Product Variations
echo "<h2>2. Product Variations</h2>";
if (!$products->isEmpty()) {
    foreach ($products as $product) {
        echo "<h3>Variations for: {$product->name} (ID: {$product->id})</h3>";
        
        $variations = DB::table('product_variations')
            ->where('product_id', $product->id)
            ->select('id', 'formatted_attributes', 'sku', 'stock_quantity', 'in_stock')
            ->get();
        
        if ($variations->isEmpty()) {
            echo "<p class='error'>❌ No variations found for this product!</p>";
            echo "<p>You need to add variations to this product.</p>";
        } else {
            echo "<p class='success'>✅ Found " . $variations->count() . " variation(s)</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Attributes</th><th>SKU</th><th>Stock</th><th>In Stock</th></tr>";
            foreach ($variations as $variation) {
                $attributes = json_decode($variation->formatted_attributes, true);
                $attrDisplay = is_array($attributes) ? json_encode($attributes, JSON_PRETTY_PRINT) : 'N/A';
                
                echo "<tr>";
                echo "<td>{$variation->id}</td>";
                echo "<td><pre>{$attrDisplay}</pre></td>";
                echo "<td>{$variation->sku}</td>";
                echo "<td>{$variation->stock_quantity}</td>";
                echo "<td>" . ($variation->in_stock ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
} else {
    echo "<p class='warning'>⚠️ No variable products to check variations for.</p>";
}

// Check 3: Current Cart Items for User ID 2
echo "<h2>3. Cart Items for User ID 2</h2>";
$cartItems = DB::table('shopping_cart_items')
    ->where('user_id', 2)
    ->select('id', 'product_id', 'product_variation_id', 'quantity', 'price', 'created_at')
    ->orderBy('created_at', 'desc')
    ->get();

if ($cartItems->isEmpty()) {
    echo "<p class='warning'>⚠️ No cart items found for user ID 2</p>";
} else {
    echo "<p>Found " . $cartItems->count() . " cart item(s)</p>";
    echo "<table>";
    echo "<tr><th>Cart ID</th><th>Product ID</th><th>Variation ID</th><th>Quantity</th><th>Price</th><th>Created</th></tr>";
    foreach ($cartItems as $item) {
        $hasVariation = !is_null($item->product_variation_id);
        $class = $hasVariation ? 'success' : 'error';
        
        echo "<tr>";
        echo "<td>{$item->id}</td>";
        echo "<td>{$item->product_id}</td>";
        echo "<td class='{$class}'>" . ($item->product_variation_id ?? 'NULL ❌') . "</td>";
        echo "<td>{$item->quantity}</td>";
        echo "<td>{$item->price}</td>";
        echo "<td>{$item->created_at}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check 4: Recent Invoices for User ID 2
echo "<h2>4. Recent Invoices for User ID 2</h2>";
$invoices = DB::table('proforma_invoices')
    ->where('user_id', 2)
    ->select('id', 'invoice_number', 'invoice_data', 'created_at')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($invoices->isEmpty()) {
    echo "<p class='warning'>⚠️ No invoices found for user ID 2</p>";
} else {
    echo "<p>Found " . $invoices->count() . " invoice(s)</p>";
    foreach ($invoices as $invoice) {
        echo "<h3>Invoice #{$invoice->invoice_number} (ID: {$invoice->id})</h3>";
        echo "<p>Created: {$invoice->created_at}</p>";
        
        $invoiceData = json_decode($invoice->invoice_data, true);
        if (isset($invoiceData['items']) && is_array($invoiceData['items'])) {
            echo "<table>";
            echo "<tr><th>Product</th><th>Variation ID</th><th>Attributes</th><th>SKU</th></tr>";
            foreach ($invoiceData['items'] as $item) {
                $hasVariation = isset($item['product_variation_id']) && !is_null($item['product_variation_id']);
                $class = $hasVariation ? 'success' : 'error';
                
                echo "<tr>";
                echo "<td>{$item['product_name']}</td>";
                echo "<td class='{$class}'>" . ($item['product_variation_id'] ?? 'NULL ❌') . "</td>";
                echo "<td>";
                if (isset($item['variation_attributes']) && is_array($item['variation_attributes'])) {
                    echo "<pre>" . json_encode($item['variation_attributes'], JSON_PRETTY_PRINT) . "</pre>";
                } else {
                    echo "<span class='error'>No attributes ❌</span>";
                }
                echo "</td>";
                echo "<td>" . ($item['variation_sku'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
}

// Summary and Recommendations
echo "<h2>5. Summary & Recommendations</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #2196F3;'>";

if ($products->isEmpty()) {
    echo "<p class='error'><strong>ISSUE:</strong> No variable products found in database.</p>";
    echo "<p><strong>ACTION:</strong> Create a variable product:</p>";
    echo "<ol>";
    echo "<li>Go to Admin → Products → Add Product</li>";
    echo "<li>Set Product Type to 'Variable'</li>";
    echo "<li>Add attributes (Size, Color, etc.)</li>";
    echo "<li>Generate variations</li>";
    echo "<li>Save product</li>";
    echo "</ol>";
} else {
    $hasVariations = false;
    foreach ($products as $product) {
        $varCount = DB::table('product_variations')->where('product_id', $product->id)->count();
        if ($varCount > 0) {
            $hasVariations = true;
            break;
        }
    }
    
    if (!$hasVariations) {
        echo "<p class='error'><strong>ISSUE:</strong> Variable products exist but have no variations.</p>";
        echo "<p><strong>ACTION:</strong> Add variations to your variable products:</p>";
        echo "<ol>";
        echo "<li>Go to Admin → Products → Edit Product</li>";
        echo "<li>Scroll to 'Product Variations' section</li>";
        echo "<li>Click 'Generate Variations'</li>";
        echo "<li>Set prices and stock for each variation</li>";
        echo "<li>Save product</li>";
        echo "</ol>";
    } else {
        echo "<p class='success'><strong>GOOD:</strong> You have variable products with variations!</p>";
        
        if (!$cartItems->isEmpty()) {
            $hasNullVariation = false;
            foreach ($cartItems as $item) {
                if (is_null($item->product_variation_id)) {
                    $hasNullVariation = true;
                    break;
                }
            }
            
            if ($hasNullVariation) {
                echo "<p class='error'><strong>ISSUE:</strong> Cart has items without variation_id.</p>";
                echo "<p><strong>ACTION:</strong> Clear cart and add products properly:</p>";
                echo "<ol>";
                echo "<li>Run this SQL: <code>DELETE FROM shopping_cart_items WHERE user_id = 2;</code></li>";
                echo "<li>Go to product page</li>";
                echo "<li><strong>SELECT variation options</strong> (Size, Color, etc.)</li>";
                echo "<li>Click 'Add to Cart'</li>";
                echo "<li>Generate new invoice</li>";
                echo "</ol>";
            } else {
                echo "<p class='success'><strong>GOOD:</strong> All cart items have variation IDs!</p>";
                echo "<p><strong>ACTION:</strong> Generate a new invoice and test.</p>";
            }
        } else {
            echo "<p class='warning'><strong>NEXT STEP:</strong> Add a product to cart:</p>";
            echo "<ol>";
            echo "<li>Go to product page</li>";
            echo "<li><strong>SELECT variation options</strong> (Size, Color, etc.)</li>";
            echo "<li>Click 'Add to Cart'</li>";
            echo "<li>Generate invoice</li>";
            echo "</ol>";
        }
    }
}

echo "</div>";

echo "<hr>";
echo "<p><strong>Need to clear cart?</strong> Run this SQL:</p>";
echo "<pre>DELETE FROM shopping_cart_items WHERE user_id = 2;</pre>";
echo "<p><strong>Need to delete test invoices?</strong> Run this SQL:</p>";
echo "<pre>DELETE FROM proforma_invoices WHERE user_id = 2;</pre>";
