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
				add_filter(
					'woocommerce_email_from_address', function () {
						$from_address = arp_get_option( ARP_PREFIX . 'from_email_text', 1, get_bloginfo( 'admin_email' ) );
						return $from_address;
					}, 99
				);
			}

			// If custom from name is set
			if ( $from_name ) {
				add_filter(
					'woocommerce_email_from_name', function () {
						$from_name = arp_get_option( ARP_PREFIX . 'from_name_text', 1 );
						return $from_name;
					}, 99
				);
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

		/**
		 * Validates if we can send email
		 *
		 * @since 1.0.0
		 * @param $id
		 * @param string $type
		 *
		 * @return bool
		 */
		public function can_send_email( $id, $type = 'order' ) {

			// If emails are limited
			if ( 'on' !== arp_get_option( ARP_PREFIX . 'limit_emails_per_user_checkbox' ) ) {
				return true;
			}

			// If forced review emails
			if ( 'user' === $type && 'on' === arp_get_option( ARP_PREFIX . 'force_unlimited_review_emails_checkbox' ) ) {
				return true;
			}

			$seconds_limit = intval( arp_get_option( ARP_PREFIX . 'emails_limit_text' ) ) * 24 * 60 * 60;

			if ( 0 >= $seconds_limit ) {
				return true;
			}

			$last_sent      = 0;
			$meta_key_order = '_' . ARP_PREFIX . 'order_last_sent_email';

			if ( 'order' === $type ) {

				$email       = get_post_meta( $id, '_billing_email', true );
				$customer_id = get_post_meta( $id, '_customer_user', true );

				if ( $customer_id ) {
					$user_info = get_userdata( $customer_id );
				}
			} elseif ( 'user' === $type ) {
				$user_info = get_userdata( $id );
				$email     = $user_info->user_email;
			}

			global $wpdb;
			$orders_sent = $wpdb->get_results( $wpdb->prepare( "SELECT MAX(meta_value) FROM {$wpdb->prefix}postmeta WHERE meta_key = %s AND post_id IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_billing_email' AND meta_value = %s)", array( $meta_key_order, $email ) ) );
			foreach ( $orders_sent[0] as $result ) {
				if ( ! empty( $result ) ) {
					$last_sent = intval( $result );
				}
			}

			if ( isset( $user_info ) ) {
				$user_last_sent = get_user_meta( $user_info->ID, '_' . ARP_PREFIX . 'user_last_sent_email', true );

				if ( $user_last_sent && intval( $user_last_sent ) > $last_sent ) {
					$last_sent = $user_last_sent;
				}
			}

			$start_time = current_time( 'timestamp' ) - $seconds_limit;

			if ( $last_sent && $last_sent > $start_time ) {
				return false;
			}

			return true;
		}
	}
}
