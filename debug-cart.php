<?php
/**
 * Plugin Name: WooCommerce Cart Debug Helper
 * Description: Adds debugging tools for WooCommerce cart functionality
 * Version: 1.0.0
 * Author: MIT Support
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WC_Cart_Debug_Helper {
    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Log add to cart requests
        add_action('wp_ajax_woocommerce_add_to_cart', array($this, 'log_add_to_cart'), 5);
        add_action('wp_ajax_nopriv_woocommerce_add_to_cart', array($this, 'log_add_to_cart'), 5);
        
        // Log cart validation
        add_filter('woocommerce_add_to_cart_validation', array($this, 'log_cart_validation'), 10, 5);
        
        // Log cart fragments updates
        add_filter('woocommerce_add_to_cart_fragments', array($this, 'log_cart_fragments'), 9999);
        
        // Add debug info to the page
        add_action('wp_footer', array($this, 'output_debug_info'), 9999);
    }

    public function enqueue_scripts() {
        if (!is_admin()) {
            // Enqueue the debug script
            wp_enqueue_script(
                'wc-cart-debug',
                plugins_url('assets/js/cart-debug.js', __FILE__),
                array('jquery'),
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/cart-debug.js'),
                true
            );
            
            // Enqueue the add to cart handler
            wp_enqueue_script(
                'wc-add-to-cart-handler',
                plugins_url('assets/js/add-to-cart-handler.js', __FILE__),
                array('jquery', 'wc-add-to-cart'),
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/add-to-cart-handler.js'),
                true
            );
            
            // Enqueue the styles
            wp_enqueue_style(
                'wc-add-to-cart-styles',
                plugins_url('assets/css/add-to-cart-messages.css', __FILE__),
                array(),
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/add-to-cart-messages.css')
            );
            
            // Localize script with debug data
            wp_localize_script('wc-cart-debug', 'wcCartDebug', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'debug' => true
            ));
            
            // Localize script with WooCommerce parameters
            wp_localize_script('wc-add-to-cart-handler', 'wc_add_to_cart_params', array(
                'ajax_url' => WC()->ajax_url(),
                'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
                'i18n_view_cart' => esc_attr__('View cart', 'woocommerce'),
                'cart_url' => apply_filters('woocommerce_add_to_cart_redirect', wc_get_cart_url(), null),
                'is_cart' => is_cart(),
                'cart_redirect_after_add' => 'no' // Force disable redirect after add to cart
            ));
        }
    }
    
    public function log_add_to_cart() {
        $this->log('Add to cart request: ' . print_r($_REQUEST, true));
    }
    
    public function log_cart_validation($passed, $product_id, $quantity, $variation_id = 0, $variations = array()) {
        $this->log(sprintf(
            'Cart validation - Product ID: %d, Variation ID: %d, Quantity: %d, Passed: %s',
            $product_id,
            $variation_id,
            $quantity,
            $passed ? 'Yes' : 'No'
        ));
        
        if (isset($_POST['rental_start_date']) || isset($_POST['rental_end_date'])) {
            $this->log('Rental dates - Start: ' . ($_POST['rental_start_date'] ?? 'not set') . 
                      ', End: ' . ($_POST['rental_end_date'] ?? 'not set'));
        }
        
        return $passed;
    }
    
    public function log_cart_fragments($fragments) {
        $this->log('Cart fragments updated');
        $this->log('Cart contents: ' . print_r(WC()->cart->get_cart_contents(), true));
        return $fragments;
    }
    
    public function output_debug_info() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        echo '<div id="cart-debug-info" style="position:fixed;bottom:0;left:0;right:0;background:#fff;z-index:9999;padding:10px;border-top:2px solid #ccc;max-height:200px;overflow:auto;">';
        echo '<h3>Cart Debug Info</h3>';
        
        if (WC()->cart) {
            echo '<div><strong>Cart Contents:</strong> ' . print_r(WC()->cart->get_cart_contents(), true) . '</div>';
            echo '<div><strong>Cart Total:</strong> ' . WC()->cart->get_cart_total() . '</div>';
            echo '<div><strong>Cart is Empty:</strong> ' . (WC()->cart->is_empty() ? 'Yes' : 'No') . '</div>';
        } else {
            echo '<div>Cart not initialized</div>';
        }
        
        if (!empty($_POST)) {
            echo '<div><strong>Last POST data:</strong> ' . print_r(array_map('esc_html', $_POST), true) . '</div>';
        }
        
        echo '</div>';
    }
    
    private function log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}

// Initialize the plugin
function init_wc_cart_debug_helper() {
    if (class_exists('WooCommerce')) {
        WC_Cart_Debug_Helper::get_instance();
    }
}
add_action('plugins_loaded', 'init_wc_cart_debug_helper');
