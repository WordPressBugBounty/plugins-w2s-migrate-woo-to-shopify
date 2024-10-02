<?php

/**
 * Fired during plugin activation
 *
 * @link       https://villatheme.com
 * @since      1.0.0
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify\includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify\includes
 * @author     Villatheme <support@villatheme.com>
 */
class VI_IMPORT_WOOCOMMERCE_TO_SHOPIFY_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wp_version;
		if ( version_compare( $wp_version, "4.4", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 4.4 or higher." );
		}


		if ( ! get_option( 'viw2s_params', false ) ) {
			$data = new VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA();
			$args_option = $data->get_default();
			add_option( 'viw2s_params', $args_option );

		}
	}

}
