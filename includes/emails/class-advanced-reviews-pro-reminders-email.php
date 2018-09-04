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

	class WC_Review_Reminder_Email extends Advanced_Reviews_Pro_WC_Review_Email {

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
			$this->description = sprintf( __( 'Review reminder email. Edit settings %1$shere%2$s.' ), '<a href="' . admin_url( 'admin.php?page=arp_tab2_options' ) . '">', '</a>' );

			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
		}

		/**
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @since 1.0.0
		 * @param int $order_id
		 * @param bool $force
		 *
		 * @return void
		 */
		public function trigger( $order_id, $force = false ) {

			// bail if no order ID is present
			if ( ! $order_id ) {
				return;
			}

			// setup order object
			$this->object    = new WC_Order( $order_id );
			$order_items     = Advanced_Reviews_Pro_Reminders::get_limited_ordered_products( $this->object->get_items() );
			$this->recipient = $this->object->get_billing_email();
			$can_send        = $force ? $force : $this->can_send_email( $order_id );

			if ( ! $this->is_enabled() || ! $this->recipient || empty( $order_items ) || ! $can_send ) {
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

			$this->heading = $this->format_string( arp_get_option( ARP_PREFIX . 'email_heading_text', 2 ) );
			$this->subject = $this->format_string( arp_get_option( ARP_PREFIX . 'email_subject_text', 2 ) );

			update_post_meta( $order_id, '_' . ARP_PREFIX . 'order_last_sent_email', current_time( 'timestamp' ) );

			do_action_ref_array( 'arp_before_send_reminder_email', array( &$this ) );

			// Woohoo, send the email!
			$this->send( $this->recipient, $this->subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );

			do_action( 'arp_after_send_reminder_email', $order_id );
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

			include plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'public/templates/emails/review-email.php';

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
					$output = add_query_arg( 'arp-add-reviews', $this->object->get_id(), get_permalink( $product_id ) ) . '#reviews';
					break;
				}

				$link    = add_query_arg( 'arp-add-review', 'true', get_permalink( $product_id ) ) . '#reviews';
				$output .= '<li><a href="' . $link . '">' . $order_item->get_name() . '</a></li>';
			}

			$output = $is_single ? $output : '<ul>' . $output . '</ul>';

			return apply_filters( 'arp_get_links_ordered_items', $output, $is_single, $order_items );
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
