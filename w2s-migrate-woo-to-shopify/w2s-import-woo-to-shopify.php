<?php
/**
 * Plugin Name:       W2S - Migrate WooCommerce to Shopify
 * Plugin URI:        https://villatheme.com/extensions/w2s-migrate-woocommerce-to-shopify/
 * Description:       Migrate all products and categories from WooCommerce to Shopify
 * Version:           1.2.0
 * Author:            Villatheme
 * Author URI:        https://villatheme.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       w2s-migrate-woo-to-shopify
 * Copyright 2021 - 2024 VillaTheme.com. All rights reserved.
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.5.2
 * WC requires at least: 7.0.0
 * WC tested up to: 8.9.0
 * Requires PHP: 7.0
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//compatible with 'High-Performance order storage (COT)'
add_action( 'before_woocommerce_init', function(){
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
});
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'w2s-migrate-woocommerce-to-shopify/w2s-migrate-woocommerce-to-shopify.php' ) ) {
	return;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'VIW2S_VERSION', '1.2.0' );
define( 'VIW2S_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'VIW2S_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'VIW2S_CSS', VIW2S_DIR_URL . 'assets/css/' );
define( 'VIW2S_ADMIN_CSS', VIW2S_DIR_URL . 'admin/css/' );
define( 'VIW2S_ADMIN_JS', VIW2S_DIR_URL . 'admin/js/' );
define( 'VIW2S_BASE_NAME', plugin_basename( __FILE__ ) );

require_once VIW2S_DIR_PATH . 'includes/support.php';

if ( ! defined( 'VIW2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_CACHE' ) ) {
	define( 'VIW2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_CACHE', WP_CONTENT_DIR . "/cache/import-woocommerce-to-shopify/" );//use the same cache folder with free version
}
if ( is_file( plugin_dir_path( __FILE__ ) . 'autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'autoload.php';
}

if ( is_file( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}



/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-viw2s-activator.php
 */
function activate_viw2s() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vi-w2s-activator.php';
	VI_IMPORT_WOOCOMMERCE_TO_SHOPIFY_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-viw2s-deactivator.php
 */
function deactivate_viw2s() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vi-w2s-deactivator.php';
	VI_IMPORT_WOOCOMMERCE_TO_SHOPIFY_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_viw2s' );
register_deactivation_hook( __FILE__, 'deactivate_viw2s' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-vi-w2s.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_viw2s_import() {
	if ( is_plugin_active( 'w2s-migrate-woocommerce-to-shopify\w2s-migrate-woocommerce-to-shopify.php' ) ) {
		return;
	}
	$plugin = new Vi_W2s();
	$plugin->run();

}

run_viw2s_import();
