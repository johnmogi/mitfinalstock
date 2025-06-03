jQuery(document).ready(function($) {
    // Log to console for debugging
    console.group('=== Mitnafun Order Admin: Checkout Debug ===');
    console.log('Debug script loaded successfully');
    
    // Function to get stock information via AJAX
    function getProductStockInfo(productId) {
        return new Promise((resolve) => {
            $.ajax({
                url: mitnafunCheckout.ajax_url,
                type: 'POST',
                data: {
                    action: 'mitnafun_get_product_stock',
                    product_id: productId,
                    nonce: mitnafunCheckout.nonce
                },
                success: function(response) {
                    if (response.success) {
                        resolve(response.data);
                    } else {
                        console.warn('Error getting stock for product', productId, response);
                        resolve(null);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error getting stock:', error);
                    resolve(null);
                }
            });
        });
    }
    
    // Process cart items
    async function processCartItems() {
        if (typeof wc_cart_fragments === 'undefined' || !wc_cart_fragments.cart?.cart_contents) {
            console.warn('WooCommerce cart data not available');
            return;
        }

        console.group('=== Cart Items with Stock Info ===');
        
        const cartItems = Object.values(wc_cart_fragments.cart.cart_contents);
        
        for (const item of cartItems) {
            const productId = item.product_id;
            const productName = item.data?.title || `Product ${productId}`;
            const quantity = item.quantity;
            const rentalDates = item.rental_dates || 'Not set';
            
            console.group(`📦 ${productName} (ID: ${productId})`);
            console.log('Quantity in cart:', quantity);
            
            // Log rental dates if available
            if (rentalDates !== 'Not set') {
                if (Array.isArray(rentalDates)) {
                    console.log('Rental Dates:', rentalDates.join(', '));
                    console.log('Rental Duration:', rentalDates.length, 'days');
                } else {
                    console.log('Rental Dates:', rentalDates);
                }
            }
            
            try {
                // Get stock information
                const stockInfo = await getProductStockInfo(productId);
                
                if (stockInfo) {
                    console.group('Stock Information:');
                    console.log('Total Stock (_initial_stock):', stockInfo.initial_stock || 'Not set');
                    console.log('WooCommerce Stock:', stockInfo.wc_stock || 'Not managed');
                    console.log('Backorders Allowed:', stockInfo.backorders_allowed ? 'Yes' : 'No');
                    console.log('Stock Status:', stockInfo.stock_status || 'N/A');
                    
                    // Calculate available stock
                    if (stockInfo.initial_stock !== undefined) {
                        const available = stockInfo.initial_stock - (stockInfo.held_stock || 0);
                        console.log('Available Stock:', available);
                        
                        // Check if current cart quantity exceeds available stock
                        if (quantity > available) {
                            console.warn('⚠️ Quantity in cart exceeds available stock!');
                        }
                    }
                    
                    console.groupEnd();
                }
                
                // Log variation data if this is a variable product
                if (item.variation_id) {
                    console.log('Variation ID:', item.variation_id);
                    if (item.variation) {
                        console.log('Selected Variations:', item.variation);
                    }
                }
                
            } catch (error) {
                console.error('Error processing product:', error);
            }
            
            console.groupEnd(); // End product group
        }
        
        console.groupEnd(); // End cart items group
    }
    
    // Initialize
    if (typeof wc_cart_fragments !== 'undefined') {
        processCartItems();
    } else {
        console.warn('WooCommerce cart fragments not found');
    }
    
    // Log when the order review updates
    $(document.body).on('updated_checkout', function() {
        console.log('Order review updated - refreshing cart data...');
        processCartItems();
    });
    
    console.groupEnd(); // End main group
});
