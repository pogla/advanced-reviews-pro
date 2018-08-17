<?php

/**
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Handle review coupons emails
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro_WC_Review_Email' ) ) {

	abstract class Advanced_Reviews_Pro_WC_Review_Email extends WC_Email {

		/**
		 * @since    1.0.0
		 */
		public function __construct() {

			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();

			$this->customer_email = true;
			$this->enabled        = 'yes';
			$this->email_type     = 'html';
		}

		/**
		 * Generate email headers
		 *
		 * @since 1.0.0
		 * @return mixed|string
		 */
		public function get_headers() {


			$reply_address = arp_get_option( ARP_PREFIX . 'reply_to_email_text', 1, get_bloginfo( 'admin_email' ) );
			$reply_name    = arp_get_option( ARP_PREFIX . 'reply_to_name_text', 1 );
			$bbc_address   = arp_get_option( ARP_PREFIX . 'bbc_email_text', 1 );
			$from_address  = arp_get_option( ARP_PREFIX . 'from_email_text', 1, get_bloginfo( 'admin_email' ) );
			$from_name     = arp_get_option( ARP_PREFIX . 'from_name_text', 1 );

			// If custom from email address is set
			if ( $from_address ) {
				add_filter( 'woocommerce_email_from_address', function () {
					$from_address = arp_get_option( ARP_PREFIX . 'from_email_text', 1, get_bloginfo( 'admin_email' ) );
					return $from_address;
				}, 99 );
			}

			// If custom from name is set
			if ( $from_name ) {
				add_filter( 'woocommerce_email_from_name', function () {
					$from_name = arp_get_option( ARP_PREFIX . 'from_name_text', 1 );
					return $from_name;
				}, 99 );
			}

			$header  = 'Content-Type: ' . $this->get_content_type() . '\r\n';
			$header .= 'From: ' . $from_name . ' <' . $from_address . '>\r\n';
			$header .= 'Reply-to: ' . $reply_name . ' <' . $reply_address . '>\r\n';

			if ( $bbc_address ) {
				$header .= 'Bcc: ' . implode( ',', $bbc_address ) . "\r\n";
			}

			return apply_filters( 'arp_woocommerce_reminder_email_headers', $header, $this->id, $this->object );
		}

		/**
		 * Initialize Settings Form Fields
		 *
		 * @since 0.1
		 */
		public function init_form_fields() {
			$this->form_fields = array();
		}
	}
}
