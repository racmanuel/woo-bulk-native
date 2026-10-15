<?php
/**
 * Search tools for product Quick Edit and Bulk Edit.
 *
 * @package WC_Bulk_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds search filters to the taxonomy checklists and selected-product list
 * displayed by WooCommerce in Quick Edit and Bulk Edit.
 */
final class WC_Bulk_Tools_Category_Search {

	/**
	 * Singleton instance.
	 *
	 * @var WC_Bulk_Tools_Category_Search|null
	 */
	protected static $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return WC_Bulk_Tools_Category_Search
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
	private function __construct() {}

	/**
	 * Registers the feature hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueues styles and scripts only on the product list.
	 *
	 * @param string $hook_suffix Current screen hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( 'edit.php' !== $hook_suffix ) {
			return;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'wc-bulk-tools-search',
			WC_BULK_TOOLS_URL . 'assets/css/admin-search.css',
			array(),
			WC_BULK_TOOLS_VERSION
		);

		wp_enqueue_script(
			'wc-bulk-tools-search',
			WC_BULK_TOOLS_URL . 'assets/js/admin-search.js',
			array( 'jquery', 'inline-edit-post' ),
			WC_BULK_TOOLS_VERSION,
			true
		);

		wp_localize_script(
			'wc-bulk-tools-search',
			'wcBulkToolsSearch',
			array(
				'placeholderTpl'     => __( 'Buscar en %s...', 'wc-bulk-tools' ),
				'placeholderGeneric' => __( 'Buscar...', 'wc-bulk-tools' ),
				'clear'              => __( 'Limpiar', 'wc-bulk-tools' ),
				'noResults'          => __( 'Sin resultados', 'wc-bulk-tools' ),
				'results'            => __( '%d resultados', 'wc-bulk-tools' ),
				'descriptionTpl'     => __( 'Escribe para filtrar %s', 'wc-bulk-tools' ),
				'descriptionGeneric' => __( 'Escribe para filtrar', 'wc-bulk-tools' ),
				'bulkTitlesLabel'    => __( 'Buscar productos seleccionados', 'wc-bulk-tools' ),
				'bulkTitlesPlaceholder' => __( 'Buscar productos seleccionados...', 'wc-bulk-tools' ),
				'bulkTitlesDescription' => __( '%d productos seleccionados', 'wc-bulk-tools' ),
				'bulkTitlesNoResults' => __( 'No hay productos coincidentes', 'wc-bulk-tools' ),
			)
		);
	}
}
