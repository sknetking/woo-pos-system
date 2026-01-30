<?php
/**
 * POS Shortcode
 */

if ( ! defined('ABSPATH') ) exit;

add_shortcode('frontend_pos', function () {

	if ( ! pos_user_can_access() ) {
		return '<p>Access denied.</p>';
	}

	ob_start();
	?>
<style>
/* Main Container */
.pos-container {
    display: flex;
    gap: 20px;
    max-width: 1400px;
    margin: 20px auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    min-height: calc(100vh - 40px);
    padding: 0 20px;
    box-sizing: border-box;
}

/* Columns - Dynamic Layout */
.pos-products-column {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    min-height: calc(100vh - 40px);
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-sizing: border-box;
}

.pos-cart-column {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    min-height: calc(100vh - 40px);
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-sizing: border-box;
}

/* Desktop Layout */
@media (min-width: 1025px) {
    <?php $desktop_layout=get_option('pos_desktop_layout', '60-40');
    list($products_width, $cart_width)=explode('-', $desktop_layout);

    ?>.pos-products-column {
        flex: 0 0 <?php echo $products_width;
        ?>%;
        max-width: <?php echo $products_width;
        ?>%;
    }

    .pos-cart-column {
        flex: 0 0 <?php echo $cart_width;
        ?>%;
        max-width: <?php echo $cart_width;
        ?>%;
    }
}

.pos-column-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e9ecef;
}

.pos-column-title {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pos-column-title::before {
    content: '';
    width: 4px;
    height: 24px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 2px;
}

/* Search Container */
.pos-search-container {
    position: relative;
    margin-bottom: 20px;
}

.pos-search-input {
    width: 100%;
    padding: 15px 15px 15px 50px;
    font-size: 16px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    box-sizing: border-box;
}

.pos-search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 18px;
    pointer-events: none;
}

.pos-search-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15);
    transform: translateY(-1px);
}

/* Products Grid */
.pos-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    flex: 1;
    overflow-y: auto;
    padding: 5px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 1);
    min-height: 400px;
    box-sizing: border-box;
    height: 250px !important;
}

.pos-products-grid::-webkit-scrollbar {
    width: 6px;
}

.pos-products-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.pos-products-grid::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.pos-products-grid::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Product Cards */
.pos-product-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    box-sizing: border-box;
}

.pos-product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #007bff, #0056b3);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.pos-product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #007bff;
}

.pos-product-card:hover::before {
    transform: scaleX(1);
}

/* Product Image */
.pos-product-image {
    width: 100px;
    height: 100px;
    margin: 0 auto 15px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.pos-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
    transition: transform 0.3s ease;
}

.pos-product-card:hover .pos-product-image img {
    transform: scale(1.05);
}

.pos-product-name {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 6px;
    line-height: 1.3;
    color: #2c3e50;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pos-product-price {
    color: #007bff;
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 4px;
}

.pos-product-sku {
    font-size: 11px;
    color: #6c757d;
    background: #f8f9fa;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
}

/* Top Selling Indicators */
.pos-top-selling-indicator {
    position: absolute;
    top: 8px;
    right: 8px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 12px;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(238, 90, 36, 0.3);
    animation: pulse 2s infinite;
}

.pos-sales-badge {
    font-size: 10px;
    color: #ff6b6b;
    font-weight: 600;
    margin-top: 4px;
    background: rgba(255, 107, 107, 0.1);
    padding: 2px 6px;
    border-radius: 8px;
    display: inline-block;
}

.pos-product-card.top-selling {
    border-color: #ff6b6b;
    box-shadow: 0 4px 16px rgba(255, 107, 107, 0.2);
}

.pos-product-card.top-selling::before {
    background: linear-gradient(90deg, #ff6b6b, #ee5a24);
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }

    100% {
        transform: scale(1);
    }
}

/* Custom Item Header Button */
.pos-custom-item-header-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.pos-custom-item-header-btn:hover {
    background: linear-gradient(135deg, #218838, #1e7e34);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.pos-custom-btn-icon {
    font-size: 16px;
    animation: pulse 2s infinite;
}

.pos-custom-btn-text {
    font-weight: 600;
}

/* Custom Item Cart Styles */
.pos-cart-item.custom-item {
    background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
    border: 2px solid #28a745;
    display: block;
}

.pos-custom-name-input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    background: white;
    box-sizing: border-box;
}

.pos-custom-name-input:focus {
    outline: none;
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.pos-custom-price-row {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pos-price-label {
    font-weight: 700;
    color: #28a745;
    font-size: 30px;
}

.pos-custom-price-input {
    width: 80px !important;
    padding: 4px 8px !important;
    border: 2px solid #28a745;
    border-radius: 6px;
    font-size: 16px !important;
    font-weight: 700;
    color: #28a745;
    background: white;
    text-align: right;
    margin-left: 2px;
}

.pos-custom-price-input:focus {
    outline: none;
    border-color: #218838;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
}

.pos-quantity-label {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
}

/* Cart Section */
.pos-cart-items {
    flex: 1;
    overflow-y: auto;
    margin-bottom: 24px;
    padding-right: 8px;
    min-height: 200px;
    max-height: 300px;
}

.pos-cart-items::-webkit-scrollbar {
    width: 6px;
}

.pos-cart-items::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.pos-cart-items::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.pos-cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    margin-bottom: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.pos-cart-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.pos-cart-item-info {
    flex: 1;
}

.pos-cart-item-name {
    font-weight: 600;
    margin-bottom: 6px;
    color: #2c3e50;
    font-size: 15px;
}

.pos-cart-item-price {
    color: #05af13;
    font-size: 16px;
    font-weight: 700;
}

.pos-quantity-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pos-quantity-btn {
    width: 32px;
    height: 32px;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: #495057;
}

.pos-quantity-btn:hover {
    background: #007bff;
    border-color: #007bff;
    color: white;
    transform: scale(1.1);
}

.pos-quantity {
    min-width: 40px;
    text-align: center;
    font-weight: 700;
    font-size: 16px;
    color: #2c3e50;
}

.pos-remove-btn {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-left: 8px;
}

.pos-remove-btn:hover {
    background: linear-gradient(135deg, #c82333, #bd2130);
    transform: scale(1.05);
}

/* Customer Info */
.pos-customer-info {
    margin-bottom: 24px;
}

.pos-input {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    margin-bottom: 15px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
    box-sizing: border-box;
}

.pos-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15);
    transform: translateY(-1px);
}

.pos-input::placeholder {
    color: #adb5bd;
}

/* Discount Section */
.pos-discount-section {
    margin-bottom: 24px;
}

/* Totals */
.pos-totals {
    margin-bottom: 24px;
    color: #fff;
}

.pos-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    font-size: 16px;
    font-weight: 500;
}

.pos-total-row.grand-total {
    border-top: 2px solid #e9ecef;
    margin-top: 12px;
    padding-top: 16px;
    font-size: 18px;
    font-weight: 700;
    color: #007bff;
}

/* Checkout Button */
.pos-checkout-btn {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    box-sizing: border-box;
}

.pos-checkout-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.pos-checkout-btn:hover {
    background: linear-gradient(135deg, #218838, #1e7e34);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.pos-checkout-btn:hover::before {
    left: 100%;
}

.pos-checkout-btn:disabled {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.pos-checkout-btn:disabled::before {
    display: none;
}

/* Pagination */
.pos-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e9ecef;
}

.pos-page-btn {
    padding: 8px 14px;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    color: #495057;
}

.pos-page-btn:hover {
    background: #f8f9fa;
    border-color: #007bff;
    color: #007bff;
    transform: translateY(-1px);
}

.pos-page-btn.active {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-color: #007bff;
    transform: scale(1.05);
}

/* Loading and Empty States */
.pos-loading {
    text-align: center;
    padding: 40px;
    color: #6c757d;
    font-size: 16px;
}

.pos-loading::before {
    content: '⏳';
    display: block;
    font-size: 32px;
    margin-bottom: 12px;
}

.pos-empty {
    text-align: center;
    padding: 40px;
    color: #6c757d;
    font-size: 16px;
}

.pos-empty::before {
    content: '📦';
    display: block;
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

/* Responsive Design - Strategic Use of Percentages */
@media (max-width: 1024px) {
    <?php $tablet_layout=get_option('pos_tablet_layout', '50-50');
    list($products_width, $cart_width)=explode('-', $tablet_layout);

    if ($tablet_layout==='100-100') {
        ?>.pos-container {
            flex-direction: column;
            gap: 20px;
            margin: 20px;
            min-height: auto;
        }

        .pos-products-column,
        .pos-cart-column {
            min-height: auto;
            padding: 20px;
            flex: 1;
            max-width: 100%;
        }

        <?php
    }

    else {
        ?>.pos-products-column {
            flex: 0 0 <?php echo $products_width;
            ?>%;
            max-width: <?php echo $products_width;
            ?>%;
        }

        .pos-cart-column {
            flex: 0 0 <?php echo $cart_width;
            ?>%;
            max-width: <?php echo $cart_width;
            ?>%;
        }

        <?php
    }

    ?>.pos-products-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
    }

    .pos-cart-items {
        max-height: 250px;
    }
}

@media (max-width: 768px) {
    <?php $mobile_layout=get_option('pos_mobile_layout', '100-100');
    list($products_width, $cart_width)=explode('-', $mobile_layout);

    if ($mobile_layout==='100-100') {
        ?>.pos-container {
            flex-direction: column;
            gap: 15px;
            margin: 15px;
            min-height: auto;
        }

        .pos-products-column,
        .pos-cart-column {
            padding: 16px;
            flex: 1;
            max-width: 100%;
        }

        <?php
    }

    else {
        ?>.pos-container {
            margin: 15px;
            gap: 15px;
        }

        .pos-products-column {
            flex: 0 0 <?php echo $products_width;
            ?>%;
            max-width: <?php echo $products_width;
            ?>%;
        }

        .pos-cart-column {
            flex: 0 0 <?php echo $cart_width;
            ?>%;
            max-width: <?php echo $cart_width;
            ?>%;
        }

        <?php
    }

    ?>.pos-products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }

    .pos-product-card {
        padding: 16px;
    }

    .pos-product-image {
        width: 80px;
        height: 80px;
    }

    .pos-product-name {
        font-size: 13px;
    }

    .pos-product-price {
        font-size: 16px;
    }

    .pos-column-title {
        font-size: 20px;
    }

    .pos-checkout-btn {
        padding: 16px;
        font-size: 16px;
    }

    .pos-cart-items {
        max-height: 200px;
    }

    .pos-input {
        padding: 12px;
        font-size: 15px;
    }
}

@media (max-width: 480px) {
    .pos-container {
        margin: 10px;
        gap: 10px;
    }

    .pos-products-column,
    .pos-cart-column {
        padding: 12px;
    }

    .pos-products-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }

    .pos-product-card {
        padding: 12px;
    }

    .pos-product-image {
        width: 60px;
        height: 60px;
    }

    .pos-product-name {
        font-size: 12px;
    }

    .pos-product-price {
        font-size: 14px;
    }

    .pos-product-sku {
        font-size: 10px;
    }

    .pos-input {
        padding: 10px;
        font-size: 14px;
    }

    .pos-cart-item {
        padding: 12px;
    }

    .pos-quantity-btn {
        width: 28px;
        height: 28px;
        font-size: 14px;
    }

    .pos-column-title {
        font-size: 18px;
    }

    .pos-checkout-btn {
        padding: 14px;
        font-size: 15px;
    }

    .pos-cart-items {
        max-height: 180px;
    }
}

/* Tablet Landscape */
@media (max-width: 1024px) and (orientation: landscape) {

    .pos-products-column,
    .pos-cart-column {
        min-height: 70vh;
    }

    .pos-cart-items {
        max-height: 300px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .pos-products-column {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .pos-cart-column {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
    }

    .pos-column-title {
        color: #ecf0f1;
    }

    .pos-input {
        background: #34495e;
        border-color: #4a5f7a;
        color: #ecf0f1;
    }

    .pos-product-card {
        background: #34495e;
        border-color: #4a5f7a;
        height: 250px;
    }

    .pos-product-name {
        color: #ecf0f1;
    }
}

/* Animation for cart items */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.pos-cart-item {
    animation: slideIn 0.3s ease;
}

/* Success and Error Messages */
.pos-success-message {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 16px;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
}

.pos-error-message {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 16px;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
}
</style>

<div class="pos-container">
    <!-- Products Column -->
    <div class="pos-products-column">
        <div class="pos-column-header">
            <h3 class="pos-column-title">Products</h3>
            <button class="pos-custom-item-header-btn" onclick="addCustomItem()">
                <span class="pos-custom-btn-icon">➕</span>
                <span class="pos-custom-btn-text">Custom Item</span>
            </button>
        </div>

        <div class="pos-search-container">
            <span class="pos-search-icon">🔍</span>
            <input type="text" id="pos-product-search" class="pos-search-input"
                placeholder="Search products (or browse top sellers below)...">
        </div>

        <div id="pos-products-grid" class="pos-products-grid">
            <div class="pos-loading">Loading products...</div>
        </div>

        <div id="pos-pagination" class="pos-pagination"></div>
    </div>

    <!-- Cart Column -->
    <div class="pos-cart-column">
        <div class="pos-column-header">
            <h3 class="pos-column-title">Point of Sale</h3>
        </div>

        <div class="pos-cart-items" id="pos-cart-items">
            <div class="pos-empty">No items in cart</div>
        </div>
        <div class="pos-customer-info">
            <input type="text" id="pos-customer" class="pos-input" placeholder="Customer Name">
            <input type="number" id="pos-whatsapp" class="pos-input" placeholder="WhatsApp Number (Required)">
        </div>

        <div class="pos-discount-section">
            <input type="number" id="pos-discount" class="pos-input" placeholder="Discount (₹)">
        </div>

        <div class="pos-totals">
            <div class="pos-total-row">
                <span>Subtotal:</span>
                <span id="pos-subtotal">₹0.00</span>
            </div>
            <?php if (get_option('pos_gst_enabled', true)): ?>
            <div class="pos-total-row" id="gst-row">
                <span>GST:</span>
                <span id="pos-gst-amount">₹0.00</span>
            </div>
            <?php endif; ?>
            <div class="pos-total-row grand-total">
                <span>Total:</span>
                <span id="pos-total">₹0.00</span>
            </div>
        </div>

        <button id="pos-checkout" class="pos-checkout-btn" disabled>
            Complete Sale
        </button>

        <div id="pos-result" style="margin-top: 20px;"></div>
    </div>
</div>

<script>
let posCart = [];
let posProducts = [];
let currentPage = 1;
let productsPerPage = <?php echo get_option('pos_products_per_page', 20); ?>;
let searchTimeout;

jQuery(function($) {

    // Load products on page load
    loadProducts();

    // Search functionality
    $('#pos-product-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadProducts($(this).val());
        }, 300);
    });

    // Load products function
    function loadProducts(searchTerm = '') {
        $('#pos-products-grid').html('<div class="pos-loading">Loading products...</div>');

        // Update header based on search
        if (searchTerm === '') {
            $('.pos-column-title').text('🔥 Top Selling Products');
        } else {
            $('.pos-column-title').text('🔍 Search Results');
        }

        $.post(pos_ajax.ajax_url, {
            action: 'pos_product_search',
            term: searchTerm,
            nonce: pos_ajax.nonce
        }, function(res) {
            if (res && res.length > 0) {
                posProducts = res;
                renderProducts();
            } else {
                $('#pos-products-grid').html('<div class="pos-empty">No products found</div>');
                $('#pos-pagination').empty();
            }
        });
    }

    // Add custom item function
    window.addCustomItem = function() {
        const customProductId = <?php echo get_option('pos_custom_item_product_id', 0); ?>;
        if (!customProductId) {
            alert('Custom item product not found. Please contact admin.');
            return;
        }

        const customProduct = {
            id: customProductId,
            name: 'Custom Item',
            sku: 'POS-CUSTOM-ITEM',
            price: 0,
            stock_qty: 999,
            stock_status: 'instock',
            manage_stock: false,
            total_sales: 0,
            image: '<?php echo wc_placeholder_img_src([100, 100]); ?>',
            label: 'Custom Item (Misc. Sale)',
            value: customProductId,
            is_custom: true
        };

        addToCart(customProduct);
    };

    // Render products with pagination
    function renderProducts() {
        const startIndex = (currentPage - 1) * productsPerPage;
        const endIndex = startIndex + productsPerPage;
        const paginatedProducts = posProducts.slice(startIndex, endIndex);

        let html = '';
        paginatedProducts.forEach(product => {
            const isTopSelling = product.total_sales > 0;
            const salesBadge = isTopSelling ?
                `<div class="pos-sales-badge">🔥 ${product.total_sales} sold</div>` : '';

            html += `
                <div class="pos-product-card ${isTopSelling ? 'top-selling' : ''}" data-product-id="${product.id}">
                    ${isTopSelling ? '<div class="pos-top-selling-indicator">⭐ Top Product</div>' : ''}
                    <div class="pos-product-image">
                        <img src="${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                    </div>
                    <div class="pos-product-name">${product.name}</div>
                    <div class="pos-product-price">₹${product.price}</div>
                    <div class="pos-product-sku">SKU: ${product.sku}</div>
                    ${salesBadge}
                </div>
            `;
        });

        $('#pos-products-grid').html(html);
        renderPagination();
    }

    // Render pagination
    function renderPagination() {
        const totalPages = Math.ceil(posProducts.length / productsPerPage);

        if (totalPages <= 1) {
            $('#pos-pagination').empty();
            return;
        }

        let html = '';

        // Previous button
        if (currentPage > 1) {
            html += `<button class="pos-page-btn" onclick="changePage(${currentPage - 1})">‹</button>`;
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            html += `<button class="pos-page-btn ${activeClass}" onclick="changePage(${i})">${i}</button>`;
        }

        // Next button
        if (currentPage < totalPages) {
            html += `<button class="pos-page-btn" onclick="changePage(${currentPage + 1})">›</button>`;
        }

        $('#pos-pagination').html(html);
    }

    // Change page function
    window.changePage = function(page) {
        currentPage = page;
        renderProducts();
    };

    // Add product to cart
    $(document).on('click', '.pos-product-card', function() {
        const productId = $(this).data('product-id');
        const product = posProducts.find(p => p.id == productId);

        if (product) {
            addToCart(product);
        }
    });

    // Add to cart function
    function addToCart(product) {
        const existingItem = posCart.find(item => item.id == product.id);

        if (existingItem) {
            existingItem.quantity++;
        } else {
            posCart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                sku: product.sku,
                is_custom: product.is_custom || false,
                custom_name: product.is_custom ? '' : null,
                custom_price: product.is_custom ? 0 : null
            });
        }

        renderCart();
        updateTotals();
    }

    // Render cart
    function renderCart() {
        if (posCart.length === 0) {
            $('#pos-cart-items').html('<div class="pos-empty">No items in cart</div>');
            $('#pos-checkout').prop('disabled', true);
            return;
        }

        let html = '';
        posCart.forEach((item, index) => {
            const isCustom = item.is_custom;
            const displayName = isCustom && item.custom_name ? item.custom_name : item.name;
            const displayPrice = isCustom && item.custom_price ? item.custom_price : item.price;

            if (isCustom) {
                // Custom item with editable fields
                html += `
                    <div class="pos-cart-item custom-item">
                        <div class="pos-cart-item-info">
                            <input type="text" class="pos-custom-name-input" value="${displayName}" 
                                placeholder="Item name" onchange="updateCustomItem(${index}, 'name', this.value)">
                            <div class="pos-custom-price-row">
                                <span class="pos-price-label">₹</span>
                                <input type="number" class="pos-custom-price-input" value="${displayPrice}" 
                                    placeholder="0" min="0" step="0.01" onchange="updateCustomItem(${index}, 'price', this.value)">
                                <span class="pos-quantity-label">× ${item.quantity}</span>
                            <div class="pos-quantity-controls">
                                <button class="pos-quantity-btn" onclick="updateQuantity(${index}, -1)">−</button>
                                <span class="pos-quantity">${item.quantity}</span>
                                <button class="pos-quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                                <button class="pos-remove-btn" onclick="removeFromCart(${index})">×</button>
                            </div>
                            
                                </div>
                        </div>
                        
                    </div>
                `;
            } else {
                // Regular product
                html += `
                    <div class="pos-cart-item">
                        <div class="pos-cart-item-info">
                            <div class="pos-cart-item-name">${item.name}</div>
                            <div class="pos-cart-item-price">₹${item.price.toFixed(2)} × ${item.quantity}</div>
                        </div>
                        <div class="pos-quantity-controls">
                            <button class="pos-quantity-btn" onclick="updateQuantity(${index}, -1)">−</button>
                            <span class="pos-quantity">${item.quantity}</span>
                            <button class="pos-quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                            <button class="pos-remove-btn" onclick="removeFromCart(${index})">×</button>
                        </div>
                    </div>
                `;
            }
        });

        $('#pos-cart-items').html(html);
        $('#pos-checkout').prop('disabled', false);
    }

    // Update quantity
    window.updateQuantity = function(index, change) {
        posCart[index].quantity += change;

        if (posCart[index].quantity <= 0) {
            posCart.splice(index, 1);
        }

        renderCart();
        updateTotals();
    };

    // Update custom item
    window.updateCustomItem = function(index, field, value) {
        if (field === 'name') {
            posCart[index].custom_name = value;
        } else if (field === 'price') {
            posCart[index].custom_price = parseFloat(value) || 0;
        }

        updateTotals();
    };

    // Remove from cart
    window.removeFromCart = function(index) {
        posCart.splice(index, 1);
        renderCart();
        updateTotals();
    };

    // Update totals
    function updateTotals() {
        let subtotal = 0;
        posCart.forEach(item => {
            const itemPrice = item.is_custom && item.custom_price ? item.custom_price : item.price;
            subtotal += itemPrice * item.quantity;
        });

        const discount = parseFloat($('#pos-discount').val()) || 0;
        const discountedSubtotal = Math.max(0, subtotal - discount);

        // Get GST settings from server
        const gstEnabled = <?php echo get_option('pos_gst_enabled', true) ? 'true' : 'false'; ?>;
        const gstRate = <?php echo get_option('pos_gst_rate', 18); ?>;

        let gstAmount = 0;
        if (gstEnabled) {
            gstAmount = discountedSubtotal * (gstRate / 100);
        }

        const total = discountedSubtotal + gstAmount;

        // Debug logging
        console.log('Update Totals Debug:');
        console.log('Cart items:', posCart);
        console.log('Subtotal:', subtotal);
        console.log('Discount:', discount);
        console.log('Discounted Subtotal:', discountedSubtotal);
        console.log('GST Enabled:', gstEnabled);
        console.log('GST Rate:', gstRate);
        console.log('GST Amount:', gstAmount);
        console.log('Total:', total);

        // Show original subtotal, then discount, then GST, then total
        $('#pos-subtotal').text('₹' + subtotal.toFixed(2));
        $('#pos-gst-amount').text('₹' + gstAmount.toFixed(2));
        $('#pos-total').text('₹' + total.toFixed(2));

        // Show discount amount if there is one
        if (discount > 0) {
            // Update discount display or show it separately
            if ($('#pos-discount-display').length === 0) {
                $('#pos-subtotal').parent().after(
                    '<div class="pos-total-row"><span>Discount:</span><span id="pos-discount-display">-₹' +
                    discount.toFixed(2) + '</span></div>');
            } else {
                $('#pos-discount-display').text('-₹' + discount.toFixed(2));
            }
        } else {
            $('#pos-discount-display').parent().remove();
        }
    }

    // Discount input
    $('#pos-discount').on('input', updateTotals);

    // Checkout
    $('#pos-checkout').on('click', function() {
        const customerName = $('#pos-customer').val().trim();
        const whatsappNumber = $('#pos-whatsapp').val().trim();

        if (!customerName) {
            $('#pos-result').html(
                '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 6px; margin-bottom: 10px;">Please enter customer name.</div>'
            );
            return;
        }

        if (!whatsappNumber) {
            $('#pos-result').html(
                '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 6px; margin-bottom: 10px;">Please enter WhatsApp number.</div>'
            );
            return;
        }

        const orderData = {
            action: 'pos_create_order',
            customer: customerName,
            whatsapp: whatsappNumber,
            discount: $('#pos-discount').val(),
            cart: posCart.map(item => ({
                id: item.id,
                name: item.name,
                qty: item.quantity,
                price: item.price,
                custom_name: item.custom_name || null,
                custom_price: item.custom_price || null
            })),
            gst_enabled: <?php echo get_option('pos_gst_enabled', true) ? 'true' : 'false'; ?>,
            gst_rate: <?php echo get_option('pos_gst_rate', 18); ?>,
            nonce: pos_ajax.nonce
        };

        $('#pos-checkout').prop('disabled', true).text('Processing...');

        $.post(pos_ajax.ajax_url, orderData, function(res) {
            $('#pos-checkout').prop('disabled', false).text('Complete Sale');

            if (res.success) {
                $('#pos-result').html(`
                    <div style="color: green; padding: 15px; background: #d4edda; border-radius: 6px; margin-bottom: 10px;">
                        <strong>Order #${res.data.order_id} completed successfully!</strong><br>
                        <a href="${res.data.invoice_url}" target="_blank" style="color: #007bff; text-decoration: none;">📄 View Invoice</a><br>
                        <a href="${res.data.whatsapp_link}" target="_blank" class="pos-whatsapp-link"><img src="<?php echo plugins_url('assets/whatsapp-icon.png', dirname(__FILE__)); ?>" alt="WhatsApp"> Send on WhatsApp</a>
                    </div>
                `);

                // Reset cart
                posCart = [];
                renderCart();
                updateTotals();
                $('#pos-customer, #pos-whatsapp, #pos-discount').val('');
            } else {
                $('#pos-result').html(
                    '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 6px; margin-bottom: 10px;">Error: ' +
                    res.data + '</div>');
            }
        }).fail(function() {
            $('#pos-checkout').prop('disabled', false).text('Complete Sale');
            $('#pos-result').html(
                '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 6px; margin-bottom: 10px;">System error occurred. Please check logs.</div>'
            );
        });
    });
});
</script>
<?php
	return ob_get_clean();
});

// Add shortcode for displaying invoice link dynamically
add_shortcode('pos_invoice_link', function($atts) {
    $atts = shortcode_atts([
        'order_id' => 0,
        'link_text' => 'View Invoice',
        'class' => 'pos-invoice-link'
    ], $atts);
    
    $order_id = intval($atts['order_id']);
    
    // If no order_id provided, try to get from URL or current user
    if ($order_id === 0) {
        // Try to get from URL parameter
        if (isset($_GET['order_id'])) {
            $order_id = intval($_GET['order_id']);
        }
        // Try to get from current user's last order
        elseif (is_user_logged_in()) {
            $customer_orders = wc_get_orders([
                'customer' => get_current_user_id(),
                'limit' => 1,
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            if (!empty($customer_orders)) {
                $order_id = $customer_orders[0]->get_id();
            }
        }
    }
    
    if ($order_id === 0) {
        return '<p>No order found.</p>';
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        return '<p>Order not found.</p>';
    }
    
    // Get the invoice URL using the main function
    $invoice_url = pos_get_invoice_url($order);
    
    return sprintf(
        '<a href="%s" target="_blank" class="%s">%s</a>',
        esc_url($invoice_url),
        esc_attr($atts['class']),
        esc_html($atts['link_text'])
    );
});