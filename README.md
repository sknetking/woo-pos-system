# Frontend POS for WooCommerce

A powerful Point of Sale (POS) system for WooCommerce with frontend access, cart management, GST support, and WhatsApp integration. **Optimized for PDF Invoices & Packing Slips by WP Overnight.**

## Features

- **Frontend POS Page** - Accessible via `[frontend_pos]` shortcode
- **Product Search** - Autocomplete search with SKU scanning support
- **Cart Management** - Add/remove multiple items with quantities
- **Discount Support** - Apply flat amount discounts
- **GST Calculation** - Toggle GST with customizable rates
- **Stock Management** - Real-time stock validation and synchronization
- **Payment Processing** - Cash payment (COD) integration
- **PDF Invoices** - Full integration with PDF Invoices & Packing Slips by WP Overnight
- **WhatsApp Integration** - API support with manual fallback
- **Access Control** - Restricted to admin and shop_manager roles
- **Modern UI** - Responsive, professional interface

## Installation

1. Upload the `sk-woo-pos` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings in **Settings → POS Settings**

## Usage

### Basic Setup

1. Create a new page in WordPress
2. Add the shortcode: `[frontend_pos]`
3. Publish the page
4. Access the POS page (only visible to admin/shop_manager users)

### Product Search

- **SKU Scanning**: Scan or type exact SKU for instant product selection
- **Autocomplete**: Type product name or partial SKU for suggestions
- **Real-time**: Search results appear as you type

### Order Processing

1. **Customer Info**: Enter customer name and WhatsApp number
2. **Add Products**: Search and add products to cart
3. **Apply Discounts**: Enter flat discount amount if needed
4. **GST Settings**: Enable GST and set rate (default 18%)
5. **Create Order**: Process order and generate invoice

### Dynamic Invoice Links

Use the `[pos_invoice_link]` shortcode to display invoice links on any page:

**Basic Usage:**
```
[pos_invoice_link]
```

**With Custom Order ID:**
```
[pos_invoice_link order_id="123"]
```

**With Custom Text:**
```
[pos_invoice_link order_id="123" link_text="Download Your Invoice"]
```

**With Custom CSS Class:**
```
[pos_invoice_link order_id="123" link_text="View Invoice" class="btn btn-primary"]
```

**Smart Detection:**
- If no `order_id` is specified, it tries to get from URL parameter `?order_id=123`
- Falls back to current user's last order (if logged in)
- Works on thank you pages, order details pages, etc.

**Example Use Cases:**
- Thank you pages: `[pos_invoice_link]`
- Order confirmation emails: `[pos_invoice_link order_id="{order_id}"]`
- Customer dashboard: `[pos_invoice_link link_text="My Latest Invoice"]`

### WhatsApp Integration

#### API Setup (Optional)
1. Go to **Settings → POS Settings**
2. Enable "WhatsApp API"
3. Enter your API provider details:
   - API Key
   - API URL

#### Manual Fallback
If API is not configured, the system automatically generates WhatsApp share links.

## File Structure

```
sk-woo-pos/
├── sk-woo-pos.php          # Main plugin file
├── includes/
│   ├── pos-core.php        # Core functions and AJAX handlers
│   ├── pos-shortcode.php   # Frontend shortcode and UI
│   └── pos-settings.php    # Admin settings page
├── assets/
│   └── pos-styles.css      # Frontend styles
└── README.md               # Documentation
```

## Configuration Options

### WhatsApp API Settings
- **Enable WhatsApp API**: Toggle API integration
- **API Key**: Your WhatsApp API provider key
- **API URL**: WhatsApp API endpoint URL

### GST Settings
- **Enable GST**: Toggle GST calculation per order
- **GST Rate**: Customizable GST percentage (default: 18%)

### PDF Invoice Public Access (Important)
For customers to view invoices without login:

1. Go to **WooCommerce → PDF Invoices → Advanced → Settings**
2. Find **"Document link access type"**
3. Set to **"Full"** (allows public access)
4. Save settings

This enables customers to view invoices via WhatsApp links without WordPress accounts.

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+
- **PDF Invoices & Packing Slips for WooCommerce** by WP Overnight (recommended)

## Recommended Plugins

- **PDF Invoices & Packing Slips for WooCommerce** by WP Overnight - For PDF invoice generation (https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/)
- Any WhatsApp API provider for automated messaging

## Security

- Access restricted to admin and shop_manager roles
- All input sanitized and validated
- Stock validation prevents overselling
- Nonce-based security for public invoice access

## Support

For support and feature requests, please contact the plugin author.

## License

This plugin is licensed under the GPL v2 or later.
