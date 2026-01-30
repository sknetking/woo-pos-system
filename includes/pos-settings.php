<?php
/**
 * POS Settings Page
 */

if ( ! defined('ABSPATH') ) exit;

add_action('admin_menu', function() {
	add_options_page(
		'POS Settings',
		'POS Settings',
		'manage_woocommerce',
		'pos-settings',
		'pos_settings_page'
	);
});

// AJAX handler for GST settings update
add_action('wp_ajax_pos_update_gst_settings', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pos_settings_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Access denied');
    }
    
    // Update GST settings
    $gst_enabled = isset($_POST['gst_enabled']) ? (bool)$_POST['gst_enabled'] : false;
    $gst_rate = isset($_POST['gst_rate']) ? floatval($_POST['gst_rate']) : 18;
    
    update_option('pos_gst_enabled', $gst_enabled);
    update_option('pos_gst_rate', $gst_rate);
    
    wp_send_json_success(['message' => 'GST settings updated successfully']);
});

function pos_settings_page() {
	if (isset($_POST['submit'])) {
		update_option('pos_whatsapp_api_enabled', isset($_POST['whatsapp_api_enabled']));
		update_option('pos_whatsapp_api_key', sanitize_text_field($_POST['whatsapp_api_key']));
		update_option('pos_whatsapp_api_url', esc_url_raw($_POST['whatsapp_api_url']));
		update_option('pos_products_per_page', intval($_POST['products_per_page']));
		update_option('pos_gst_enabled', isset($_POST['gst_enabled']));
		update_option('pos_gst_rate', floatval($_POST['gst_rate']));
		update_option('pos_desktop_layout', sanitize_text_field($_POST['desktop_layout']));
		update_option('pos_tablet_layout', sanitize_text_field($_POST['tablet_layout']));
		update_option('pos_mobile_layout', sanitize_text_field($_POST['mobile_layout']));
		echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
	}
	
	$enabled = get_option('pos_whatsapp_api_enabled', false);
	$api_key = get_option('pos_whatsapp_api_key', '');
	$api_url = get_option('pos_whatsapp_api_url', '');
	$products_per_page = get_option('pos_products_per_page', 20);
	$gst_enabled = get_option('pos_gst_enabled', true);
	$gst_rate = get_option('pos_gst_rate', 18);
	$desktop_layout = get_option('pos_desktop_layout', '60-40');
	$tablet_layout = get_option('pos_tablet_layout', '50-50');
	$mobile_layout = get_option('pos_mobile_layout', '100-100');
	?>
<div class="wrap">
    <h1>POS Settings</h1>

    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row">WhatsApp API</th>
                <td>
                    <label>
                        <input type="checkbox" name="whatsapp_api_enabled" <?php checked($enabled); ?>>
                        Enable WhatsApp API
                    </label>
                    <p class="description">When enabled, WhatsApp messages will be sent automatically via API after
                        order completion. Otherwise falls back to manual links.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">API Key</th>
                <td>
                    <input type="text" name="whatsapp_api_key" value="<?php echo esc_attr($api_key); ?>"
                        class="regular-text">
                    <p class="description">Your WhatsApp API provider key</p>
                </td>
            </tr>
            <tr>
                <th scope="row">API URL</th>
                <td>
                    <input type="url" name="whatsapp_api_url" value="<?php echo esc_attr($api_url); ?>"
                        class="regular-text">
                    <p class="description">WhatsApp API endpoint URL</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Products Per Page</th>
                <td>
                    <input type="number" name="products_per_page" value="<?php echo esc_attr($products_per_page); ?>"
                        class="small-text" min="6" max="100" step="1">
                    <p class="description">Number of products to show per page in POS (default: 20)</p>
                </td>
            </tr>
            <tr>
                <th scope="row">GST Settings</th>
                <td>
                    <label>
                        <input type="checkbox" name="gst_enabled" <?php checked($gst_enabled); ?>>
                        Enable GST Calculation
                    </label>
                    <p class="description">When enabled, GST will be automatically calculated and added to orders.</p>

                    <div style="margin-top: 10px;">
                        <label for="gst_rate">GST Rate (%):</label>
                        <input type="number" id="gst_rate" name="gst_rate" value="<?php echo esc_attr($gst_rate); ?>"
                            class="small-text" min="0" max="50" step="0.1" style="margin-left: 10px;">
                        <p class="description">Default GST rate for all orders (default: 18%)</p>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">Column Layout</th>
                <td>
                    <div style="margin-bottom: 15px;">
                        <label for="desktop_layout"><strong>Desktop Layout:</strong></label>
                        <select id="desktop_layout" name="desktop_layout" style="margin-left: 10px;">
                            <option value="60-40" <?php selected($desktop_layout, '60-40'); ?>>Products 60% - POS 40%</option>
                            <option value="50-50" <?php selected($desktop_layout, '50-50'); ?>>Products 50% - POS 50%</option>
                            <option value="70-30" <?php selected($desktop_layout, '70-30'); ?>>Products 70% - POS 30%</option>
                            <option value="40-60" <?php selected($desktop_layout, '40-60'); ?>>Products 40% - POS 60%</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="tablet_layout"><strong>Tablet Layout:</strong></label>
                        <select id="tablet_layout" name="tablet_layout" style="margin-left: 10px;">
                            <option value="50-50" <?php selected($tablet_layout, '50-50'); ?>>Products 50% - POS 50%</option>
                            <option value="60-40" <?php selected($tablet_layout, '60-40'); ?>>Products 60% - POS 40%</option>
                            <option value="40-60" <?php selected($tablet_layout, '40-60'); ?>>Products 40% - POS 60%</option>
                            <option value="100-100" <?php selected($tablet_layout, '100-100'); ?>>Stacked (100% each)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="mobile_layout"><strong>Mobile Layout:</strong></label>
                        <select id="mobile_layout" name="mobile_layout" style="margin-left: 10px;">
                            <option value="100-100" <?php selected($mobile_layout, '100-100'); ?>>Stacked (100% each)</option>
                            <option value="50-50" <?php selected($mobile_layout, '50-50'); ?>>Products 50% - POS 50%</option>
                            <option value="60-40" <?php selected($mobile_layout, '60-40'); ?>>Products 60% - POS 40%</option>
                            <option value="40-60" <?php selected($mobile_layout, '40-60'); ?>>Products 40% - POS 60%</option>
                        </select>
                    </div>
                    
                    <p class="description">Choose column layout for different screen sizes. Default: Desktop 60-40, Tablet 50-50, Mobile Stacked.</p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <!-- Shortcode Information -->
    <h2>📄 Shortcodes</h2>
    <div class="notice notice-info" style="margin: 20px 0;">
        <h3>Available Shortcodes</h3>

        <p><strong>Main POS Interface:</strong></p>
        <code>[frontend_pos]</code>
        <p>Displays the complete Point of Sale interface with products, cart, and checkout.</p>

        <p><strong>Invoice Link Display:</strong></p>
        <code>[pos_invoice_link]</code>
        <p>Shows invoice link for current order or specific order ID.</p>

        <p><strong>How it works:</strong> Create a WordPress page, add the shortcode, and the POS will appear. Only
            visible to admin/shop_manager users.</p>
    </div>

    <!-- WhatsApp API Setup -->
    <h2>📱 WhatsApp API Setup</h2>
    <div class="notice notice-info" style="margin: 20px 0;">
        <p><strong>Quick Setup:</strong></p>
        <ol>
            <li>Choose provider: <a href="https://www.twilio.com/whatsapp" target="_blank">Twilio</a>, <a
                    href="https://developers.facebook.com/docs/whatsapp" target="_blank">WhatsApp Business API</a>, or
                <a href="https://www.wati.io" target="_blank">WATI</a>
            </li>
            <li>Get API Key and URL from provider</li>
            <li>Enter in settings above and enable WhatsApp API</li>
        </ol>

        <p><strong>API Format:</strong> POST request with JSON data, returns success/error status.</p>
    </div>

    <!-- PDF Invoice Configuration -->
    <h2>📋 PDF Invoice Setup</h2>
    <div class="notice notice-info" style="margin: 20px 0;">
        <p><strong>For public invoice access:</strong> Go to <strong>WooCommerce → PDF Invoices → Advanced → Settings →
                Document link access type</strong> and set to <strong>"Full"</strong>. And <b>Pretty document links</b>
            Checked!</p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle GST checkbox toggle
    $('input[name="gst_enabled"]').on('change', function() {
        var isEnabled = $(this).is(':checked');
        var gstRate = $('input[name="gst_rate"]').val();

        // Save GST settings via AJAX
        $.post(ajaxurl, {
            action: 'pos_update_gst_settings',
            gst_enabled: isEnabled,
            gst_rate: gstRate,
            nonce: '<?php echo wp_create_nonce("pos_settings_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                // Show success message
                var message = isEnabled ? 'GST enabled successfully!' :
                    'GST disabled successfully!';
                $('form').prepend('<div class="notice notice-success is-dismissible"><p>' +
                    message + '</p></div>');

                // Auto-hide after 3 seconds
                setTimeout(function() {
                    $('.notice-success').fadeOut();
                }, 3000);
            }
        });
    });

    // Handle GST rate change
    $('input[name="gst_rate"]').on('input', function() {
        var isEnabled = $('input[name="gst_enabled"]').is(':checked');
        var gstRate = $(this).val();

        // Save GST rate via AJAX
        $.post(ajaxurl, {
            action: 'pos_update_gst_settings',
            gst_enabled: isEnabled,
            gst_rate: gstRate,
            nonce: '<?php echo wp_create_nonce("pos_settings_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                // Show success message
                $('form').prepend(
                    '<div class="notice notice-success is-dismissible"><p>GST rate updated to ' +
                    gstRate + '%!</p></div>');

                // Auto-hide after 3 seconds
                setTimeout(function() {
                    $('.notice-success').fadeOut();
                }, 3000);
            }
        });
    });
});
</script>
<?php
}