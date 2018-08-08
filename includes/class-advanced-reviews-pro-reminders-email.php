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
 * Handle review reminders
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Review_Reminder_Email' ) ) {

	class WC_Review_Reminder_Email extends WC_Email {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->id    = 'wc_review_reminder';
			$this->title = 'Review Reminder';
			/* translators: %1$s: Link */
			$this->description = sprintf( __( 'Review reminder email. Edit settings %1$shere%2$s.' ), '<a href="' . admin_url( 'admin.php?page=arp_options' ) . '">', '</a>' );

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
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @since 1.0.0
		 * @param int $order_id
		 *
		 * @return void
		 */
		public function trigger( $order_id ) {

			// bail if no order ID is present
			if ( ! $order_id ) {
				return;
			}

			// setup order object
			$this->object = new WC_Order( $order_id );
			$order_items  = Advanced_Reviews_Pro_Reminders::get_limited_ordered_products( $this->object->get_items() );

			if ( empty( $order_items ) ) {
				return;
			}

			// Replacements
			$this->placeholders['{order_date}']          = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
			$this->placeholders['{order_id}']            = $this->object->get_order_number();
			$this->placeholders['{site_title}']          = arp_get_option( ARP_PREFIX . 'shop_name_text', 1, get_bloginfo( 'name' ) );
			$this->placeholders['{customer_first_name}'] = $this->object->get_billing_first_name();
			$this->placeholders['{customer_last_name}']  = $this->object->get_billing_last_name();
			$this->placeholders['{customer_full_name}']  = $this->object->get_billing_first_name() . ' ' . $this->object->get_billing_last_name();
			$this->placeholders['{list_of_products}']    = $this->get_links_ordered_items( $order_items );
			$this->placeholders['{review_link}']         = $this->get_links_ordered_items( $order_items, true );

			$this->heading   = $this->format_string( arp_get_option( ARP_PREFIX . 'email_heading_text', 2 ) );
			$this->recipient = $this->object->get_billing_email();
			$this->subject   = $this->format_string( arp_get_option( ARP_PREFIX . 'email_subject_text', 2 ) );

			if ( ! $this->is_enabled() || ! $this->recipient ) {
				return;
			}

			// Woohoo, send the email!
			$this->send( $this->recipient, $this->subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * get_content_html function.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_content_html() {

			ob_start();

			$order         = $this->object;
			$email_heading = $this->get_heading();
			$plain_text    = false;
			$email         = $this;
			$body          = $this->format_string( arp_get_option( ARP_PREFIX . 'email_body_text', 2 ) );

			include plugin_dir_path( dirname( __FILE__ ) ) . '/public/templates/emails/review-reminder.php';

			return ob_get_clean();
		}

		/**
		 * Generates product links
		 *
		 * @param $order_items
		 * @param bool $is_single
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_links_ordered_items( $order_items, $is_single = false ) {

			$output = '';
			foreach ( $order_items as $order_item ) {
				/* @var $order_item WC_Order_Item */

				$product_id = $order_item->get_product_id();

				if ( $is_single ) {
					$output = add_query_arg( 'arp-add-reviews', $this->object->get_id(), get_permalink( $product_id ) );
					break;
				}

				$link    = add_query_arg( 'arp-add-review', 'true', get_permalink( $product_id ) ) . '#tab-reviews';
				$output .= '<li><a href="' . $link . '">' . $order_item->get_name() . '</a></li>';
			}

			if ( $is_single ) {
				return $output;
			} else {
				return '<ul>' . $output . '</ul>';
			}
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
if ( ! function_exists( 'wc_review_reminder_email' ) ) {

	function wc_review_reminder_email() {
		return WC_Review_Reminder_Email::instance();
	}
}
