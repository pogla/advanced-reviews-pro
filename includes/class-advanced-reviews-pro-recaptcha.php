<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Handle recaptcha on WooCommerce reviews
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */
class Advanced_Reviews_Pro_Recaptcha {

	/**
	 * Prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $prefix    Prefix for cmb2 fields.
	 */
	private $prefix = 'arp_';

	/**
	 * @since    1.0.0
	 */
	public function __construct() {
	}

	public function output_captcha( $submit_field ) {

		if ( is_product() ) {

			$site_key = arp_get_option( $this->prefix . 'recaptcha_site_key_text' );

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

		if ( 'on' === arp_get_option( $this->prefix . 'enable_recaptcha_checkbox' ) ) {

			$secret_key = arp_get_option( $this->prefix . 'recaptcha_secret_key_text' );

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

}
