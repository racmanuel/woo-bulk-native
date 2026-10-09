<?php
/**
 * Clase principal del plugin. Carga dependencias y centraliza los hooks.
 *
 * @package WC_Bulk_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orquestador del plugin.
 */
final class WC_Bulk_Tools {

	/**
	 * Instancia única.
	 *
	 * @var WC_Bulk_Tools|null
	 */
	protected static $instance = null;

	/**
	 * Devuelve la instancia única.
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
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->define_hooks();
	}

	/**
	 * Carga las clases de las funcionalidades del plugin.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once WC_BULK_TOOLS_PATH . 'includes/class-wc-bulk-tools-category-search.php';
		require_once WC_BULK_TOOLS_PATH . 'includes/class-wc-bulk-tools-category-cleaner.php';
	}

	/**
	 * Registra los hooks globales del plugin.
	 *
	 * @return void
	 */
	private function define_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'init_features' ) );
	}

	/**
	 * Carga el textdomain del plugin.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wc-bulk-tools', false, dirname( WC_BULK_TOOLS_BASENAME ) . '/languages' );
	}

	/**
	 * Inicializa las funcionalidades si WooCommerce está activo.
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
	 * Indica si WooCommerce está activo.
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Muestra un aviso si WooCommerce no está activo.
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
