<?php
/**
 * Plugin Name: Frontend POS for WooCommerce
 * Description: Admin-only frontend POS with cart, discounts, cash handling, PDF invoice & WhatsApp support. Optimized for PDF Invoices & Packing Slips by WP Overnight.
 * Author Name: SK NetKing
 * Version: 1.0.0
 * Text Domain: sk-woo-pos
 */

if ( ! defined('ABSPATH') ) exit;

// Plugin constants
define('SK_POS_VERSION', '1.0.0');
define('SK_POS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SK_POS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load core files
require_once SK_POS_PLUGIN_DIR . 'includes/pos-core.php';
require_once SK_POS_PLUGIN_DIR . 'includes/pos-shortcode.php';
require_once SK_POS_PLUGIN_DIR . 'includes/pos-settings.php';

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', function() {
    if (is_page() && has_shortcode(get_post()->post_content, 'frontend_pos')) {
        wp_enqueue_style('sk-pos-styles', SK_POS_PLUGIN_URL . 'assets/pos-styles.css', [], SK_POS_VERSION);
        
        // Localize script for AJAX URL
        wp_localize_script('jquery', 'pos_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pos_nonce')
        ]);
    }
});

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default options
    if (!get_option('pos_whatsapp_api_enabled')) {
        update_option('pos_whatsapp_api_enabled', false);
    }
    if (!get_option('pos_products_per_page')) {
        update_option('pos_products_per_page', 20);
    }
    if (!get_option('pos_gst_enabled')) {
        update_option('pos_gst_enabled', true);
    }
    if (!get_option('pos_gst_rate')) {
        update_option('pos_gst_rate', 18);
    }
    if (!get_option('pos_desktop_layout')) {
        update_option('pos_desktop_layout', '60-40');
    }
    if (!get_option('pos_tablet_layout')) {
        update_option('pos_tablet_layout', '50-50');
    }
    if (!get_option('pos_mobile_layout')) {
        update_option('pos_mobile_layout', '100-100');
    }
    
    // Create custom item product
    create_pos_custom_item_product();
    
    // Enable debug mode temporarily for troubleshooting
    update_option('pos_debug_mode', true);
});

// Create custom item product function
function create_pos_custom_item_product() {
    // Check if custom product already exists
    $existing_product_id = get_option('pos_custom_item_product_id');
    if ($existing_product_id && get_post($existing_product_id)) {
        return; // Product already exists
    }
    
    // Check if product exists by title using WP_Query
    $existing_query = new WP_Query([
        'post_type' => 'product',
        'title' => 'Store Custom Item',
        'posts_per_page' => 1,
        'fields' => 'ids'
    ]);
    
    if ($existing_query->have_posts()) {
        $existing_product_id = $existing_query->posts[0];
        update_option('pos_custom_item_product_id', $existing_product_id);
        return;
    }
    
    // Create new product
    $product_data = [
        'post_title'    => 'Store Custom Item',
        'post_content'  => 'Hidden custom item for POS miscellaneous sales',
        'post_status'   => 'publish',
        'post_type'     => 'product',
        'post_author'   => get_current_user_id()
    ];
    
    $product_id = wp_insert_post($product_data);
    
    if ($product_id && !is_wp_error($product_id)) {
        // Set product meta
        update_post_meta($product_id, '_sku', 'POS-CUSTOM-ITEM');
        update_post_meta($product_id, '_price', 0);
        update_post_meta($product_id, '_regular_price', 0);
        update_post_meta($product_id, '_manage_stock', 'no');
        update_post_meta($product_id, '_stock_status', 'instock');
        update_post_meta($product_id, '_virtual', 'yes');
        update_post_meta($product_id, '_downloadable', 'yes');
        update_post_meta($product_id, '_visibility', 'hidden');
        update_post_meta($product_id, '_product_attributes', []);
        update_post_meta($product_id, '_sold_individually', 'no');
        
        // Save product ID
        update_option('pos_custom_item_product_id', $product_id);
        
        // Log creation (comment out to prevent output)
        // error_log('POS Custom Item Product created with ID: ' . $product_id);
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Cleanup if needed
});


// Helper function to disable debug mode
add_action('init', function() {
    // Disable debug mode for production
    update_option('pos_debug_mode', false);
});