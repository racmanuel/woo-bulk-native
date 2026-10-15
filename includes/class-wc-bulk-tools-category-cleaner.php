<?php
/**
 * WooCommerce default category cleaner for simple products.
 *
 * @package WC_Bulk_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes the default category from simple products that also have
 * other categories assigned.
 */
final class WC_Bulk_Tools_Category_Cleaner {

	/**
	 * Nonce action.
	 */
	const NONCE_ACTION = 'wcbt_cleaner_nonce';

	/**
	 * Transient key storing cleanable IDs.
	 */
	const TRANSIENT_KEY = 'wcbt_cleanable_ids';

	/**
	 * Admin page slug.
	 */
	const MENU_SLUG = 'wc-default-cat-cleaner';

	/**
	 * Products processed per batch.
	 */
	const BATCH_SIZE = 50;

	/**
	 * Singleton instance.
	 *
	 * @var WC_Bulk_Tools_Category_Cleaner|null
	 */
	protected static $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return WC_Bulk_Tools_Category_Cleaner
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
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'manage_posts_extra_tablenav', array( $this, 'add_cleaner_button' ) );
		add_action( 'wp_ajax_wcbt_cleaner_simulate', array( $this, 'ajax_simulate' ) );
		add_action( 'wp_ajax_wcbt_cleaner_batch', array( $this, 'ajax_batch_process' ) );
	}

	/**
	 * Registers the admin page under the Products menu.
	 *
	 * @return void
	 */
	public function register_admin_page() {
		add_submenu_page(
			'edit.php?post_type=product',
			esc_html__( 'Limpiar categoría por defecto', 'wc-bulk-tools' ),
			esc_html__( 'Limpiar categoría por defecto', 'wc-bulk-tools' ),
			'manage_woocommerce',
			self::MENU_SLUG,
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Renders the cleaner admin page.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'No tienes permisos para acceder a esta página.', 'wc-bulk-tools' ) );
		}

		$default_cat_id   = (int) get_option( 'default_product_cat', 0 );
		$default_cat      = $default_cat_id ? get_term( $default_cat_id, 'product_cat' ) : null;
		$default_cat_name = ( $default_cat && ! is_wp_error( $default_cat ) ) ? $default_cat->name : esc_html__( '(No detectada)', 'wc-bulk-tools' );
		$nonce            = wp_create_nonce( self::NONCE_ACTION );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Limpiar categoría por defecto de WooCommerce', 'wc-bulk-tools' ); ?></h1>

			<div id="wcbt-dcc-info" class="card" style="max-width: 800px; margin-top: 20px; padding: 16px;">
				<h2><?php esc_html_e( 'Categoría por defecto detectada', 'wc-bulk-tools' ); ?></h2>
				<table class="widefat striped" style="margin-top: 10px;">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Nombre:', 'wc-bulk-tools' ); ?></strong></td>
							<td><?php echo esc_html( $default_cat_name ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'ID:', 'wc-bulk-tools' ); ?></strong></td>
							<td><?php echo esc_html( $default_cat_id ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="wcbt-dcc-stats" class="card" style="max-width: 800px; margin-top: 20px; padding: 16px;">
				<h2><?php esc_html_e( 'Resultado de la simulación', 'wc-bulk-tools' ); ?></h2>
				<table class="widefat striped" style="margin-top: 10px;">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Productos encontrados (con categoría por defecto):', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-total-found">0</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Productos que se pueden limpiar:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-cleanable">0</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Productos que se conservarán sin cambios:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-keep">0</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="wcbt-dcc-actions" style="max-width: 800px; margin-top: 20px;">
				<button id="wcbt-dcc-simulate-btn" class="button button-secondary" data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Simular limpieza', 'wc-bulk-tools' ); ?>
				</button>
				<button id="wcbt-dcc-execute-btn" class="button button-primary" data-nonce="<?php echo esc_attr( $nonce ); ?>" disabled>
					<?php esc_html_e( 'Ejecutar limpieza', 'wc-bulk-tools' ); ?>
				</button>
			</div>

			<div id="wcbt-dcc-progress" class="card" style="max-width: 800px; margin-top: 20px; padding: 16px;">
				<h2><?php esc_html_e( 'Progreso', 'wc-bulk-tools' ); ?></h2>
				<div style="margin: 16px 0;">
					<div style="background: #e0e0e0; border-radius: 4px; height: 28px; overflow: hidden;">
						<div id="wcbt-dcc-progress-bar" style="background: #2271b1; width: 0%; height: 100%; border-radius: 4px; transition: width 0.3s;"></div>
					</div>
					<span id="wcbt-dcc-progress-text" style="display: inline-block; margin-top: 8px;">0%</span>
				</div>
				<table class="widefat striped" style="margin-top: 10px;">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Productos procesados:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-proc-processed">0</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Productos modificados:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-proc-modified">0</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Productos omitidos:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-proc-skipped">0</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div id="wcbt-dcc-result" class="card" style="max-width: 800px; margin-top: 20px; padding: 16px;">
				<h2><?php esc_html_e( 'Proceso completado correctamente', 'wc-bulk-tools' ); ?></h2>
				<table class="widefat striped" style="margin-top: 10px;">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Total procesados:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-result-processed">0</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Total modificados:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-result-modified">0</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Total omitidos:', 'wc-bulk-tools' ); ?></strong></td>
							<td id="wcbt-dcc-result-skipped">0</td>
						</tr>
					</tbody>
				</table>
				<p style="margin-top: 16px; font-weight: bold; color: #46b450;">
					<?php esc_html_e( '✔ Proceso completado correctamente', 'wc-bulk-tools' ); ?>
				</p>
			</div>

			<div id="wcbt-dcc-error" class="notice notice-error" style="max-width: 800px; margin-top: 20px; display: none;">
				<p id="wcbt-dcc-error-text"></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Adds an access button to the product list.
	 *
	 * @param string $which Toolbar position ('top' or 'bottom').
	 * @return void
	 */
	public function add_cleaner_button( $which ) {
		global $post_type;

		if ( 'product' !== $post_type || 'top' !== $which ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$url = admin_url( 'edit.php?post_type=product&page=' . self::MENU_SLUG );
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="button" style="margin-left: 6px;">
			<?php esc_html_e( 'Limpiar categoría por defecto', 'wc-bulk-tools' ); ?>
		</a>
		<?php
	}

	/**
	 * Enqueues scripts and styles only on the cleaner page.
	 *
	 * @param string $hook_suffix Current screen hook suffix.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( 'product_page_' . self::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		wp_enqueue_style(
			'wc-bulk-tools-cleaner',
			WC_BULK_TOOLS_URL . 'assets/css/admin-cleaner.css',
			array(),
			WC_BULK_TOOLS_VERSION
		);

		wp_enqueue_script(
			'wc-bulk-tools-cleaner',
			WC_BULK_TOOLS_URL . 'assets/js/admin-cleaner.js',
			array( 'jquery' ),
			WC_BULK_TOOLS_VERSION,
			true
		);

		wp_localize_script(
			'wc-bulk-tools-cleaner',
			'wcBulkToolsCleaner',
			array(
				'simulate'             => __( 'Simular limpieza', 'wc-bulk-tools' ),
				'simulating'           => __( 'Simulando…', 'wc-bulk-tools' ),
				'confirm'              => __( '¿Estás seguro de que deseas modificar las categorías de los productos? Esta acción eliminará la categoría por defecto de los productos que tengan otras categorías adicionales.', 'wc-bulk-tools' ),
				'errorSimulate'        => __( 'Error al simular.', 'wc-bulk-tools' ),
				'errorConnection'      => __( 'Error de conexión.', 'wc-bulk-tools' ),
				'errorBatch'           => __( 'Error al procesar lote.', 'wc-bulk-tools' ),
				'errorBatchConnection' => __( 'Error de conexión durante el procesamiento.', 'wc-bulk-tools' ),
			)
		);
	}

	/**
	 * AJAX: simulates the cleanup (counts only, makes no changes).
	 *
	 * Stores cleanable IDs in a transient so batch processing can iterate over
	 * them by index and avoid the offset drift that would occur when querying
	 * the database again after removing terms.
	 *
	 * @return void
	 */
	public function ajax_simulate() {
		$this->check_ajax_permissions();

		$default_cat_id = $this->get_default_category_id();

		if ( $default_cat_id <= 0 ) {
			wp_send_json_error( __( 'No se detectó una categoría por defecto de productos.', 'wc-bulk-tools' ) );
		}

		$products = $this->get_simple_products_with_default_cat( $default_cat_id );

		$total_found   = count( $products );
		$cleanable     = 0;
		$keep          = 0;
		$cleanable_ids = array();

		foreach ( $products as $product_id ) {
			$terms = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

			if ( in_array( $default_cat_id, $terms, true ) && count( $terms ) > 1 ) {
				$cleanable++;
				$cleanable_ids[] = $product_id;
			} else {
				$keep++;
			}
		}

		set_transient( self::TRANSIENT_KEY, $cleanable_ids, HOUR_IN_SECONDS );

		wp_send_json_success(
			array(
				'total_found' => $total_found,
				'cleanable'   => $cleanable,
				'keep'        => $keep,
			)
		);
	}

	/**
	 * AJAX: processes a product batch by reading transient IDs by index.
	 *
	 * @return void
	 */
	public function ajax_batch_process() {
		$this->check_ajax_permissions();

		$default_cat_id = $this->get_default_category_id();

		if ( $default_cat_id <= 0 ) {
			wp_send_json_error( __( 'No se detectó una categoría por defecto de productos.', 'wc-bulk-tools' ) );
		}

		$cleanable_ids = get_transient( self::TRANSIENT_KEY );

		if ( ! is_array( $cleanable_ids ) || empty( $cleanable_ids ) ) {
			delete_transient( self::TRANSIENT_KEY );
			wp_send_json_success( $this->empty_batch_response() );
		}

		$batch  = isset( $_POST['batch'] ) ? absint( $_POST['batch'] ) : 0;
		$offset = $batch * self::BATCH_SIZE;

		$batch_ids = array_slice( $cleanable_ids, $offset, self::BATCH_SIZE );

		if ( empty( $batch_ids ) ) {
			delete_transient( self::TRANSIENT_KEY );
			wp_send_json_success( $this->empty_batch_response() );
		}

		$batch_processed = 0;
		$batch_modified  = 0;
		$batch_skipped   = 0;

		foreach ( $batch_ids as $product_id ) {
			$batch_processed++;

			$terms = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

			if ( in_array( $default_cat_id, $terms, true ) && count( $terms ) > 1 ) {
				$removed = wp_remove_object_terms( $product_id, $default_cat_id, 'product_cat' );

				if ( $removed && ! is_wp_error( $removed ) ) {
					$batch_modified++;
					clean_object_term_cache( $product_id, 'product_cat' );
				} else {
					$batch_skipped++;
				}
			} else {
				$batch_skipped++;
			}
		}

		$next_offset = $offset + self::BATCH_SIZE;
		$has_more    = isset( $cleanable_ids[ $next_offset ] );

		if ( ! $has_more ) {
			delete_transient( self::TRANSIENT_KEY );
		}

		wp_send_json_success(
			array(
				'batch_processed' => $batch_processed,
				'batch_modified'  => $batch_modified,
				'batch_skipped'   => $batch_skipped,
				'done'            => ! $has_more,
			)
		);
	}

	/**
	 * Standard response when there is nothing left to process.
	 *
	 * @return array
	 */
	private function empty_batch_response() {
		return array(
			'batch_processed' => 0,
			'batch_modified'  => 0,
			'batch_skipped'   => 0,
			'done'            => true,
		);
	}

	/**
	 * Checks permissions and the nonce on AJAX requests.
	 *
	 * @return void
	 */
	private function check_ajax_permissions() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'No tienes permisos para realizar esta acción.', 'wc-bulk-tools' ) );
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? wp_unslash( $_POST['_wpnonce'] ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error( __( 'Nonce inválido.', 'wc-bulk-tools' ) );
		}
	}

	/**
	 * Returns the default product category ID.
	 *
	 * @return int
	 */
	private function get_default_category_id() {
		return (int) get_option( 'default_product_cat', 0 );
	}

	/**
	 * Gets the IDs of simple products that have the default category.
	 *
	 * @param int $default_cat_id Default category ID.
	 * @param int $limit          Maximum number of products to retrieve (-1 = all).
	 * @return int[] Product ID list.
	 */
	private function get_simple_products_with_default_cat( $default_cat_id, $limit = -1 ) {
		$products = wc_get_products(
			array(
				'type'   => 'simple',
				'status' => array( 'publish', 'private', 'draft', 'pending' ),
				'return' => 'ids',
				'limit'  => $limit,
			)
		);

		$result = array();

		foreach ( $products as $product_id ) {
			$terms = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

			if ( in_array( $default_cat_id, $terms, true ) ) {
				$result[] = $product_id;
			}
		}

		return $result;
	}
}
