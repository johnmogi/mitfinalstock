jQuery(document).ready(function($) {
    'use strict';

    // Override the add to cart form submission
    $(document).on('submit', 'form.cart', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        var $form = $(this);
        var $button = $form.find('.single_add_to_cart_button');
        
        // Disable button to prevent multiple clicks
        $button.prop('disabled', true).addClass('loading');
        
        // Get the form data
        var formData = $form.serialize();
        
        // Log the form data for debugging
        console.log('Form data:', formData);
        
        // Get the product ID from the button value
        var product_id = $button.val();
        
        // Get the quantity
        var quantity = $form.find('input.qty').val() || 1;
        
        // Get rental dates
        var rental_start_date = $form.find('input[name="rental_start_date"]').val();
        var rental_end_date = $form.find('input[name="rental_end_date"]').val();
        
        // Prepare the data for AJAX
        var data = {
            action: 'woocommerce_ajax_add_to_cart',
            product_id: product_id,
            product_sku: '',
            quantity: quantity,
            rental_start_date: rental_start_date,
            rental_end_date: rental_end_date,
            variation_id: 0,
            variation: []
        };
        
        // Add any additional form data
        $form.serializeArray().forEach(function(field) {
            if (field.name && !data[field.name]) {
                data[field.name] = field.value;
            }
        });
        
        // Make the AJAX request
        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: data,
            success: function(response) {
                if (response.error && response.product_url) {
                    // If there's an error, redirect to the product page
                    window.location = response.product_url;
                    return;
                }
                
                // If successful, update fragments
                if (response.fragments) {
                    $.each(response.fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                }
                
                // Update cart count
                if (response.cart_hash) {
                    $('.cart-contents-count').text(response.cart_hash);
                }
                
                // Show added to cart message
                if (response.message) {
                    // You might want to show a nice notification here
                    console.log('Product added to cart:', response.message);
                }
                
                // Re-enable the button
                $button.prop('disabled', false).removeClass('loading');
                
                // Optional: Show a success message
                showAddedToCartMessage('המוצר נוסף בהצלחה לסל הקניות');
                
            },
            error: function(xhr, status, error) {
                console.error('Error adding to cart:', error);
                // Re-enable the button on error
                $button.prop('disabled', false).removeClass('loading');
                
                // Show error message
                showAddedToCartMessage('אירעה שגיאה בהוספת המוצר לסל', 'error');
            }
        });
        
        return false;
    });
    
    // Function to show added to cart message
    function showAddedToCartMessage(message, type = 'success') {
        // Remove any existing messages
        $('.wc-ajax-message').remove();
        
        // Create message element
        var $message = $('<div class="woocommerce-message wc-ajax-message">' + message + '</div>');
        
        // Add appropriate class based on message type
        if (type === 'error') {
            $message.removeClass('woocommerce-message').addClass('woocommerce-error');
        }
        
        // Add close button
        $message.append('<span class="close-message">×</span>');
        
        // Add to page
        $message.prependTo('.woocommerce-notices-wrapper').hide().fadeIn(300);
        
        // Auto-hide after 5 seconds
        var messageTimer = setTimeout(function() {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Close on click
        $message.on('click', '.close-message', function() {
            clearTimeout(messageTimer);
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    
    // Handle direct clicks on add to cart buttons
    $(document).on('click', '.single_add_to_cart_button:not(.btn-redirect)', function(e) {
        e.preventDefault();
        $(this).closest('form').trigger('submit');
    });
    
    // Handle redirect buttons (for direct checkout)
    $(document).on('click', '.btn-redirect', function(e) {
        // This will allow the default form submission for redirect buttons
        // which will take the user to checkout
        return true;
    });
});
