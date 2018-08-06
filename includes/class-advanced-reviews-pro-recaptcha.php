<?php

/**
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Handles recaptcha on WooCommerce reviews
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Advanced_Reviews_Pro_Recaptcha' ) ) {

	class Advanced_Reviews_Pro_Recaptcha {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * @since    1.0.0
		 */
		public function __construct() {
		}

		public function output_captcha( $submit_field ) {

			if ( is_product() ) {

				$site_key = arp_get_option( ARP_PREFIX . 'recaptcha_site_key_text' );

				if ( $site_key ) {
					echo sprintf( '<div class="g-recaptcha" data-sitekey="%s"></div>', esc_attr( $site_key ) );
				}
			}

			return $submit_field;
		}

		/**
		 * Validates captcha on review submit
		 *
		 * @throws Exception
		 */
		public function validate_captcha() {

			if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_recaptcha_checkbox' ) ) {

				$secret_key = arp_get_option( ARP_PREFIX . 'recaptcha_secret_key_text' );

				if ( $secret_key ) {

					if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
						$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
					}

					$post_data = http_build_query(
						array(
							'secret'   => $secret_key,
							'remoteip' => $_SERVER['REMOTE_ADDR'],
							'response' => $_POST['g-recaptcha-response'],
						)
					);

					$opts = array(
						'http' => array(
							'method'  => 'POST',
							'header'  => 'Content-type: application/x-www-form-urlencoded',
							'content' => $post_data,
						),
					);

					$context  = stream_context_create( $opts );
					$response = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $context );
					$result   = json_decode( $response );

					if ( true !== $result->success ) {
						wp_die( '<p>reCAPTCHA is not valid!</p>', esc_attr( __( 'Comment Submission Failure' ) ), array( 'back_link' => true ) );
					}
				}
			}
		}

		/**
		 * Class Instance
		 *
		 * @static
		 * @return object instance
		 *
		 * @since  1.0.0
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
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
if ( ! function_exists( 'advanced_reviews_pro_recaptcha' ) ) {

	function advanced_reviews_pro_recaptcha() {
		return Advanced_Reviews_Pro_Recaptcha::instance();
	}
}

