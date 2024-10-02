<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://villatheme.com
 * @since      1.0.0
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify\includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify\includes
 * @author     Villatheme <support@villatheme.com>
 */
class Vi_W2s_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'w2s-migrate-woo-to-shopify',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

		if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
			include_once VIW2S_DIR_PATH . 'includes/support.php';
		}

		$environment = new \VillaTheme_Require_Environment( [
				'plugin_name'     => 'W2S - Migrate WooCommerce to Shopify',
				'php_version'     => '7.0',
				'wp_version'      => '5.0',
				'wc_version'      => '7.0',
				'require_plugins' => [
					[
						'slug' => 'woocommerce',
						'name' => 'WooCommerce',
					],
				]
			]
		);

		if ( $environment->has_error() ) {
			return;
		}


		if ( class_exists( 'VillaTheme_Support' ) ) {
			new VillaTheme_Support(
				array(
					'support'    => 'https://villatheme.com/supports/',
					'docs'       => 'https://docs.villatheme.com/?item=w2s',
					'review'     => 'https://wordpress.org/plugins/w2s-migrate-woo-to-shopify/#reviews',
					'pro_url'    => 'https://1.envato.market/vnr5Nj',
					'css'        => VIW2S_CSS,
					'image'      => '',
					'slug'       => 'w2s-migrate-woo-to-shopify',
					'menu_slug'  => 'vi-w2s-woo-to-shopify',
					'version'    => VIW2S_VERSION,
					'survey_url' => 'https://script.google.com/macros/s/AKfycbyu6BopgJZE9zEL0ZhwbLTwixgRYlLIqTChhDqbgbnMzZeT6skLNISi82-duRNmQA32/exec'
				)
			);
		}
	}


}
