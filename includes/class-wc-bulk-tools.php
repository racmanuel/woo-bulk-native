<?php
/**
 * Main plugin class. Loads dependencies and centralizes hooks.
 *
 * @package WC_Bulk_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin orchestrator.
 */
final class WC_Bulk_Tools {

	/**
	 * Singleton instance.
	 *
	 * @var WC_Bulk_Tools|null
	 */
	protected static $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return WC_Bulk_Tools
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->define_hooks();
	}

	/**
	 * Loads the plugin feature classes.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once WC_BULK_TOOLS_PATH . 'includes/class-wc-bulk-tools-category-search.php';
		require_once WC_BULK_TOOLS_PATH . 'includes/class-wc-bulk-tools-category-cleaner.php';
	}

	/**
	 * Registers the plugin's global hooks.
	 *
	 * @return void
	 */
	private function define_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'init_features' ) );
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wc-bulk-tools', false, dirname( WC_BULK_TOOLS_BASENAME ) . '/languages' );
	}

	/**
	 * Initializes the features if WooCommerce is active.
	 *
	 * @return void
	 */
	public function init_features() {
		if ( ! $this->is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'missing_woocommerce_notice' ) );
			return;
		}

		WC_Bulk_Tools_Category_Search::instance()->register_hooks();
		WC_Bulk_Tools_Category_Cleaner::instance()->register_hooks();
	}

	/**
	 * Checks whether WooCommerce is active.
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Displays a notice if WooCommerce is not active.
	 *
	 * @return void
	 */
	public function missing_woocommerce_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'WC Bulk Edit & Category Tools requiere que WooCommerce esté instalado y activo.', 'wc-bulk-tools' ); ?></p>
		</div>
		<?php
	}
}
