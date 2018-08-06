<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/public
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Advanced_Reviews_Pro_Public' ) ) {

	class Advanced_Reviews_Pro_Public {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string    $plugin_name       The name of the plugin.
		 * @param      string    $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version     = $version;
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Advanced_Reviews_Pro_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Advanced_Reviews_Pro_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/advanced-reviews-pro-public.css', array(), $this->version, 'all' );
		}

		/**
		 * Register the JavaScript for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Advanced_Reviews_Pro_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Advanced_Reviews_Pro_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			$ajax_nonce = wp_create_nonce( 'arp-public-js-nonce' );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/advanced-reviews-pro-public.js', array( 'jquery' ), $this->version, false );

			wp_localize_script( $this->plugin_name, 'wp_vars', array(
				'security' => $ajax_nonce,
			) );

			if ( is_product() && 'on' === arp_get_option( ARP_PREFIX . 'enable_recaptcha_checkbox' ) ) {

				if ( arp_get_option( ARP_PREFIX . 'recaptcha_site_key_text' ) ) {

					wp_enqueue_script( ARP_PREFIX . 'recaptcha_api', 'https://www.google.com/recaptcha/api.js?onload=onloadCallback', array( $this->plugin_name ), $this->version, true );
				}
			}
		}

		/**
		 * Class Instance
		 *
		 * @static
		 *
		 * @param $plugin_name
		 * @param $version
		 *
		 * @return object instance
		 *
		 * @since  1.0.0
		 */
		public static function instance( $plugin_name, $version ) {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $plugin_name, $version );
			}

			return self::$_instance;
		}

	}
}

/**
 * Instance of plugin
 *
 * @return object
 * @since  1.0.0
 */
if ( ! function_exists( 'advanced_reviews_pro_public' ) ) {

	function advanced_reviews_pro_public( $plugin_name, $version ) {
		return Advanced_Reviews_Pro_Public::instance( $plugin_name, $version );
	}
}

