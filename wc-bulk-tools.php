<?php
/**
 * Plugin Name:       WC Bulk Edit & Category Tools
 * Plugin URI:        https://github.com/rcmanuel/wc-bulk-tools
 * Description:       Native WooCommerce tools: taxonomy checklist search (categories, brands, etc.) in Quick Edit and Bulk Edit, plus a default category cleaner for simple products.
 * Version:           1.0.2
 * Author:            rcmanuel
 * Author URI:        https://github.com/rcmanuel
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-bulk-tools
 * Domain Path:       /languages
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 5.0.0
 * WC tested up to:   9.0.0
 *
 * @package WC_Bulk_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_BULK_TOOLS_VERSION', '1.0.2' );
define( 'WC_BULK_TOOLS_FILE', __FILE__ );
define( 'WC_BULK_TOOLS_BASENAME', plugin_basename( __FILE__ ) );
define( 'WC_BULK_TOOLS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_BULK_TOOLS_URL', plugin_dir_url( __FILE__ ) );

require_once WC_BULK_TOOLS_PATH . 'includes/class-wc-bulk-tools.php';

/**
 * Returns the plugin's main instance.
 *
 * @return WC_Bulk_Tools
 */
function wc_bulk_tools() {
	return WC_Bulk_Tools::instance();
}

wc_bulk_tools();

/**
 * Declares compatibility with High-Performance Order Storage (HPOS).
 *
 * @return void
 */
function wc_bulk_tools_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_BULK_TOOLS_FILE, true );
	}
}
add_action( 'before_woocommerce_init', 'wc_bulk_tools_declare_hpos_compatibility' );
