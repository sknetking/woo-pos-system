<?php
/**
 * POS Core Functions
 */

if ( ! defined('ABSPATH') ) exit;

/*--------------------------------------------------------------
ACCESS CONTROL
--------------------------------------------------------------*/
function pos_user_can_access() {
	return current_user_can('manage_woocommerce') || current_user_can('shop_manager');
}

/*--------------------------------------------------------------
PRODUCT SEARCH WITH AUTOCOMPLETE
--------------------------------------------------------------*/
add_action('wp_ajax_pos_product_search', function () {
	// Verify nonce
	if (!wp_verify_nonce($_POST['nonce'], 'pos_nonce')) {
		wp_die('Security check failed');
	}
	
	$term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
	$products_per_page = get_option('pos_products_per_page', 20);
	
	// If no search term, get top selling products
	if (empty($term)) {
		$args = [
			'post_type' => 'product',
			'posts_per_page' => -1, // Get all products for pagination
			'post_status' => 'publish',
			'meta_query' => [
				[
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '='
				]
			],
			'meta_key' => 'total_sales',
			'orderby' => 'meta_value_num',
			'order' => 'DESC'
		];
	} else {
		// First try exact SKU match
		$args = [
			'post_type' => 'product',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => '_sku',
					'value' => $term,
					'compare' => 'LIKE'
				],
				[
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '='
				]
			]
		];
		
		$q = new WP_Query($args);
		$data = [];
		
		// If no exact SKU match, try partial search
		if ($q->post_count === 0) {
			$args = [
				'post_type' => 'product',
				's' => $term,
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'meta_query' => [
					[
						'key' => '_stock_status',
						'value' => 'instock',
						'compare' => '='
					]
				]
			];
		}
	}
	
	$q = new WP_Query($args);
	$data = [];

	foreach ($q->posts as $p) {
		$product = wc_get_product($p->ID);
		if (!$product) continue;
		
		$stock_qty = $product->get_stock_quantity();
		$stock_status = $product->get_stock_status();
		
		// Get product thumbnail
		$image_id = $product->get_image_id();
		$image_url = wp_get_attachment_image_url($image_id, [100, 100]);
		if (!$image_url) {
			$image_url = wc_placeholder_img_src([100, 100]);
		}
		
		$data[] = [
			'id' => $product->get_id(),
			'name' => $product->get_name(),
			'sku' => $product->get_sku() ?: 'N/A',
			'price' => wc_get_price_to_display($product),
			'stock_qty' => $stock_qty,
			'stock_status' => $stock_status,
			'manage_stock' => $product->get_manage_stock(),
			'total_sales' => get_post_meta($product->get_id(), 'total_sales', true) ?: 0,
			'image' => $image_url,
			'label' => $product->get_name() . ' (' . ($product->get_sku() ?: 'N/A') . ')',
			'value' => $product->get_id()
		];
	}

	wp_send_json($data);
});

add_action('wp_ajax_nopriv_pos_product_search', function () {
	wp_send_json_error('Access denied');
});

/*--------------------------------------------------------------
CREATE ORDER
--------------------------------------------------------------*/
add_action('wp_ajax_pos_create_order', function () {
	// Verify nonce
	if (!wp_verify_nonce($_POST['nonce'], 'pos_nonce')) {
		wp_die('Security check failed');
	}

	if ( ! pos_user_can_access() ) wp_die();

	try {
		// Stock validation
		foreach ($_POST['cart'] as $item) {
			$product = wc_get_product($item['id']);
			if ($product->get_manage_stock()) {
				$stock_qty = $product->get_stock_quantity();
				if ($stock_qty < (int)$item['qty']) {
					wp_send_json_error("Insufficient stock for {$product->get_name()}. Available: {$stock_qty}, Requested: {$item['qty']}");
				}
			}
		}

		$order = wc_create_order();

		$order->set_billing_first_name( sanitize_text_field($_POST['customer']) );
		$order->update_meta_data('_billing_whatsapp', sanitize_text_field($_POST['whatsapp']) );

		foreach ($_POST['cart'] as $item) {
			$product = wc_get_product($item['id']);
			$quantity = (int)$item['qty'];
			
			// Get custom product ID for comparison
			$custom_product_id = get_option('pos_custom_item_product_id', 0);
			$is_custom_product = ($item['id'] == $custom_product_id);
			
			if ($is_custom_product) {
				// CUSTOM ITEM: Use custom price and name
				$custom_price = isset($item['custom_price']) ? floatval($item['custom_price']) : 0;
				$custom_name = isset($item['custom_name']) ? sanitize_text_field($item['custom_name']) : 'Custom Item';
				
				// Set custom price on product
				$product->set_price($custom_price);
				$product->set_regular_price($custom_price);
				$product->set_sale_price($custom_price);
				
				// Add to order
				$order_item_id = $order->add_product($product, $quantity);
				$order_item = $order->get_item($order_item_id);
				
				if ($order_item) {
					$order_item->set_subtotal($custom_price * $quantity);
					$order_item->set_total($custom_price * $quantity);
					$order_item->set_name($custom_name);
					$order_item->update_meta_data('_custom_item_name', $custom_name);
					$order_item->update_meta_data('_custom_item_price', $custom_price);
					$order_item->update_meta_data('_is_custom_item', 'yes');
					$order_item->save();
				}
			} else {
				// REGULAR PRODUCT: Use original price, no overrides
				$order->add_product($product, $quantity);
			}
		}

		if (!empty($_POST['discount'])) {
			$discount_amount = floatval($_POST['discount']);
			if ($discount_amount > 0) {
				$fee = new WC_Order_Item_Fee();
				$fee->set_name('Discount');
				$fee->set_amount(-$discount_amount);
				$fee->set_total(-$discount_amount);
				$order->add_item($fee);
			}
		}

		if (!empty($_POST['gst_enabled']) && $_POST['gst_enabled'] == 'true') {
			$gst_rate = floatval($_POST['gst_rate']);
			if ($gst_rate > 0) {
				$subtotal = 0;
				$custom_product_id = get_option('pos_custom_item_product_id', 0);
				
				foreach ($_POST['cart'] as $item) {
					$is_custom_product = ($item['id'] == $custom_product_id);
					
					if ($is_custom_product && isset($item['custom_price'])) {
						// Custom item: use custom price
						$price = floatval($item['custom_price']);
					} else {
						// Regular product: use original price
						$product = wc_get_product($item['id']);
						$price = wc_get_price_to_display($product);
					}
					
					$subtotal += $price * (int)$item['qty'];
				}
				
				$discount = floatval($_POST['discount']) ?: 0;
				$taxable_amount = max(0, $subtotal - $discount);
				$gst_amount = $taxable_amount * ($gst_rate / 100);
				
				if ($gst_amount > 0) {
					$fee = new WC_Order_Item_Fee();
					$fee->set_name("GST ({$gst_rate}%)");
					$fee->set_amount($gst_amount);
					$fee->set_tax_status('taxable');
					$fee->set_total($gst_amount);
					$order->add_item($fee);
				}
			}
		}

		$order->set_payment_method('cod');
		$order->set_payment_method_title('Cash');
		$order->payment_complete();
		
		// Calculate totals to ensure fees are included
		$order->calculate_totals();
		
		$order->set_status('completed');
		$order->save();

		// Stock is automatically reduced by WooCommerce when order status is completed
			
		$invoice_url = pos_get_invoice_url($order);

		$wa = preg_replace('/\D/', '', $_POST['whatsapp']);
		
		// WhatsApp API integration
		$whatsapp_api_enabled = get_option('pos_whatsapp_api_enabled', false);
		$whatsapp_api_key = get_option('pos_whatsapp_api_key', '');
		$whatsapp_api_url = get_option('pos_whatsapp_api_url', '');
		
		$msg = "📋 *INVOICE RECEIPT*\n\n" .
		      "🛒 *Order #{$order->get_id()}*\n" .
		      "👤 Customer: " . $order->get_billing_first_name() . "\n" .
		      "📞 WhatsApp: " . $order->get_meta('_billing_whatsapp') . "\n" .
		      "💰 Total: ₹" . $order->get_total() . "\n" .
		      "💳 Payment: Paid\n\n" .
		      "📄 *View Full Invoice:*\n" .
		      $invoice_url . "\n\n" .
		      "Thank you for your purchase! 🙏";
		
		if ($whatsapp_api_enabled && !empty($whatsapp_api_key) && !empty($wa)) {
			// Use WhatsApp API
			$api_response = pos_send_whatsapp_api($wa, $msg, $whatsapp_api_key, $whatsapp_api_url);
			$wa_link = $api_response ? 'Message sent via API' : "https://wa.me/{$wa}?text=" . urlencode($msg);
		} else {
			// Fallback to manual WhatsApp link
			$wa_link = "https://wa.me/{$wa}?text=" . urlencode($msg);
		}

		// Clear any output buffers that might have errors
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		$response_data = [
			'order_id' => $order->get_id(),
			'invoice_url' => $invoice_url,
			'whatsapp_link' => $wa_link
		];
		
		wp_send_json_success($response_data);

	} catch (Exception $e) {
		// Log the error
		error_log('POS Order Creation Error: ' . $e->getMessage());
		error_log('Stack trace: ' . $e->getTraceAsString());
		
		// Clear any output buffers
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		wp_send_json_error('Order creation failed: ' . $e->getMessage());
	} catch (Error $e) {
		// Log the error
		error_log('POS Order Creation Fatal Error: ' . $e->getMessage());
		error_log('Stack trace: ' . $e->getTraceAsString());
		
		// Clear any output buffers
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		wp_send_json_error('System error occurred. Please check logs.');
	}
});

add_action('wp_ajax_nopriv_pos_create_order', function () {
	wp_send_json_error('Access denied');
});

/*--------------------------------------------------------------
WHATSAPP API FUNCTION
--------------------------------------------------------------*/
function pos_send_whatsapp_api($phone, $message, $api_key, $api_url) {
	if (empty($api_url) || empty($api_key)) return false;
	
	$data = [
		'phone' => $phone,
		'message' => $message,
		'apikey' => $api_key
	];
	
	$response = wp_remote_post($api_url, [
		'body' => $data,
		'timeout' => 30,
		'headers' => ['Content-Type' => 'application/json']
	]);
	
	if (is_wp_error($response)) return false;
	
	$body = wp_remote_retrieve_body($response);
	$result = json_decode($body, true);
	
	return isset($result['success']) && $result['success'] === true;
}

/*--------------------------------------------------------------
GET PDF INVOICE URL (WP OVERNIGHT PLUGIN)
--------------------------------------------------------------*/
function pos_get_invoice_url($order) {
	try {
		// Get fresh order instance to ensure we have the latest data
		$order = wc_get_order($order->get_id());
		$order_key = $order->get_order_key();

		// Construct the secure guest-access URL
		$invoice_url = site_url() . '/wcpdf/invoice/' . $order->get_id() . '/' . $order_key . '/pdf';
		
		return $invoice_url;
		
	} catch (Exception $e) {
		error_log('POS Invoice URL Error: ' . $e->getMessage());
		// Return admin URL as fallback
		return get_admin_url(null, 'post.php?post=' . $order->get_id() . '&action=edit');
	}
}