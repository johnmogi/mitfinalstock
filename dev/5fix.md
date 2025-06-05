# Stock Display Solution Documentation

## Admin Menu Shortcut

### Problem
Needed a quick access link to the Mitnafun Orders admin page from the WordPress admin sidebar.

### Solution
Added a top-level admin menu item that appears right after the Dashboard:

```php
// Add Mitnafun Orders to admin menu
add_action('admin_menu', 'add_mitnafun_admin_menu_link');
function add_mitnafun_admin_menu_link() {
    if (current_user_can('manage_woocommerce')) {
        add_menu_page(
            'Mitnafun Orders',
            'Mitnafun Orders',
            'manage_woocommerce',
            'admin.php?page=mitnafun-order-admin',
            '',
            'dart',
            2
        );
    }
}
```

### Key Features
- Appears as a top-level menu item in the WordPress admin sidebar
- Positioned right after the Dashboard (position 2)
- Uses the cart icon for visual consistency with WooCommerce
- Only visible to users with 'manage_woocommerce' capability
- Directly links to the existing order admin page

### Implementation Notes
- Added to theme's functions.php file
- No database changes required
- Automatically appears for all users with appropriate permissions
- Maintains existing functionality while adding quick access

---

## Stock Display Solution

## Stock Display Overview
Implemented a solution to display stock information for rental products, showing both initial stock and current availability. The solution includes a debug panel that appears on the frontend to help with testing and verification.

## Key Components

### 1. Stock Data Retrieval

#### `get_initial_stock($product_id)`
- **Purpose**: Retrieves the initial stock value for a product
- **Location**: `mitnafun-order-admin.php`
- **Query**:
  ```php
  get_post_meta($product_id, '_initial_stock', true);
  ```
- **Returns**: Integer value of initial stock or false if not set

#### `get_available_stock($product_id)`
- **Purpose**: Gets current available stock from WooCommerce
- **Location**: `mitnafun-order-admin.php`
- **Query**:
  ```php
  $product = wc_get_product($product_id);
  return $product->get_stock_quantity();
  ```
- **Returns**: Integer value of available stock

### 2. Frontend Display

#### Debug Panel HTML
```html
<div id="stock-debug-info" 
     style="position: fixed; 
            bottom: 0px; 
            right: 0px; 
            background: rgba(0, 0, 0, 0.7); 
            color: white; 
            padding: 10px; 
            font-size: 12px; 
            z-index: 9999; 
            border-top-left-radius: 5px;">
    Stock: {current} (Initial: {initial}) | Avail: {available} | {status} | Count: {reserved}
</div>
```

### 3. JavaScript Implementation

#### Initialization
```javascript
jQuery(document).ready(function($) {
    // Get product ID from the page
    const productId = getProductIdFromPage();
    
    if (productId) {
        updateStockDisplay(productId);
    }
});
```

#### Stock Update Function
```javascript
function updateStockDisplay(productId) {
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'get_stock_data',
            product_id: productId,
            nonce: mitnafun_vars.nonce
        },
        success: function(response) {
            if (response.success) {
                updateDebugPanel(response.data);
            }
        }
    });
}
```

### 4. AJAX Handler

#### PHP (mitnafun-order-admin.php)
```php
add_action('wp_ajax_get_stock_data', 'handle_stock_data_request');
add_action('wp_ajax_nopriv_get_stock_data', 'handle_stock_data_request');

function handle_stock_data_request() {
    check_ajax_referer('mitnafun_nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
    }
    
    $initial_stock = get_initial_stock($product_id);
    $available_stock = get_available_stock($product_id);
    $reserved_count = get_reserved_count($product_id);
    
    wp_send_json_success([
        'initial' => $initial_stock,
        'available' => $available_stock,
        'reserved' => $reserved_count,
        'status' => $available_stock > 0 ? '✅ In Stock' : '❌ Out of Stock'
    ]);
}
```

## Implementation Notes

1. **Caching**: Consider implementing transient caching for stock data that updates infrequently
2. **Performance**: Queries are optimized to only retrieve necessary data
3. **Security**: All AJAX requests include nonce verification
4. **Compatibility**: Works with both simple and variable products
5. **Localization**: Ready for translation with proper text domains

## Testing

1. Navigate to any product page
2. The debug panel should appear in the bottom-right corner
3. Verify stock numbers match expected values
4. Test with different products and stock statuses

## Future Improvements

1. Add refresh button to manually update stock
2. Implement WebSocket for real-time updates
3. Add stock history tracking
4. Create admin interface for stock management

## Troubleshooting

- If stock doesn't update, check:
  - Product has the `_initial_stock` meta set
  - WooCommerce stock management is enabled
  - User has proper permissions
  - No JavaScript errors in console
  - AJAX requests are returning successfully



  // Get initial stock (custom meta)
  $initial_stock = (int) get_post_meta($product_id, '_initial_stock', true);
  
  // Get current WooCommerce stock
  $current_stock = (int) $product->get_stock_quantity();
  
  // Calculate reserved stock
  $reserved_stock = $initial_stock - $current_stock;
  
  // Get count of active reservations
  $reserved_count = $wpdb->get_var($wpdb->prepare("
      SELECT COUNT(DISTINCT oi.order_id)
      FROM {$wpdb->prefix}woocommerce_order_items oi
      JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim 
          ON oi.order_item_id = oim.order_item_id
      JOIN {$wpdb->prefix}wc_orders o 
          ON oi.order_id = o.id
      WHERE oim.meta_key = '_product_id' 
      AND oim.meta_value = %d
      AND o.status IN ('wc-processing', 'wc-pending', 'wc-on-hold')
  ", $product_id));