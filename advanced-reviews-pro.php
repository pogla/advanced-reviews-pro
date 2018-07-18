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
 * Plugin URI:        https://maticpogladic.com/
 * Description:       Advenced WooCommerce Reviews Functionality.
 * Version:           1.0.0
 * Author:            Matic Pogladič
 * Author URI:        https://maticpogladic.com/
 * Text Domain:       advanced-reviews-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

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

register_activation_hook( __FILE__, 'activate_advanced_reviews_pro' );
register_deactivation_hook( __FILE__, 'deactivate_advanced_reviews_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-advanced-reviews-pro.php';

/**
 * Require cmb2 library
 */
require plugin_dir_path( __FILE__ ) . 'vendor/cmb2/init.php';
require plugin_dir_path( __FILE__ ) . 'vendor/cmb2-conditionals/cmb2-conditionals.php';
require plugin_dir_path( __FILE__ ) . 'vendor/cmb2-select2/cmb-field-select2.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_advanced_reviews_pro() {

	$plugin = advanced_reviews_pro();
	$plugin->run();
}
run_advanced_reviews_pro();


