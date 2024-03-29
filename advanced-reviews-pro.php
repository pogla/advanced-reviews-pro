<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://maticpogladic.com/
 * @since             1.0.0
 * @package           Advanced_Reviews_Pro
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Reviews Pro
 * Plugin URI:        https://maticpogladic.com/advanced-reviews-pro/
 * Description:       Advanced WooCommerce Reviews Functionality.
 * Version:           1.0
 * Author:            Matic Pogladič
 * Author URI:        https://maticpogladic.com/
 * Text Domain:       advanced-reviews-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

/**
 * Currently plugin version.
 * SemVer - https://semver.org
 */
define( 'ARP_VERSION', '1.0' );
define( 'ARP_PREFIX', 'arp_' );
define( 'ARP_NAME', 'Advanced Reviews Pro' );
define( 'ARP_MIN_PHP_VER', '5.6' );
define( 'ARP_MIN_WP_VER', '4.7' );
define( 'ARP_MIN_WC_VER', '3.0' );
define( 'ARP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-advanced-reviews-pro.php';

function arp_load_plugin() {

	$plugin = advanced_reviews_pro();

	if ( $plugin::check() ) {

		$plugin->init();

		register_activation_hook( __FILE__, 'activate_advanced_reviews_pro' );
		register_deactivation_hook( __FILE__, 'deactivate_advanced_reviews_pro' );

		/**
		 * Require cmb2 library and add-ons
		 */
		require plugin_dir_path( __FILE__ ) . 'vendor/cmb2/init.php';
		require plugin_dir_path( __FILE__ ) . 'vendor/cmb2-conditionals/cmb2-conditionals.php';
		require plugin_dir_path( __FILE__ ) . 'vendor/cmb2-select2/cmb-field-select2.php';

		$plugin->run();
	}
}
add_action( 'plugins_loaded', 'arp_load_plugin', 8 );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-advanced-reviews-pro-activator.php
 */
function activate_advanced_reviews_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-advanced-reviews-pro-activator.php';
	$activator = advanced_reviews_pro_activator();
	$activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-advanced-reviews-pro-deactivator.php
 */
function deactivate_advanced_reviews_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-advanced-reviews-pro-deactivator.php';
	$deactivator = advanced_reviews_pro_deactivator();
	$deactivator::deactivate();
}
