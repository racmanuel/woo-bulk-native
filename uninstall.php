<?php
/**
 * Rutina de desinstalación del plugin.
 *
 * Solo elimina datos temporales generados por el limpiador de categorías.
 * No borra opciones de WooCommerce ni datos de productos.
 *
 * @package WC_Bulk_Tools
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_transient( 'wcbt_cleanable_ids' );
