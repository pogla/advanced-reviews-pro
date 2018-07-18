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
 *namediv
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Review_Reminder_Email' ) ) {

	class WC_Review_Reminder_Email extends WC_Email {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

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

			$from_address  = arp_get_option( $this->prefix . 'from_email_text', 1, get_bloginfo( 'admin_email' ) );
			$reply_name    = arp_get_option( $this->prefix . 'from_name_text', 1 );
			$reply_address = arp_get_option( $this->prefix . 'reply_to_email_text', 1, get_bloginfo( 'admin_email' ) );
			$bbc_address   = arp_get_option( $this->prefix . 'bbc_email_text', 1 );

			$header  = 'Content-Type: ' . $this->get_content_type() . "\r\n";
			$header .= 'From: ' . $from_address . "\r\n";
			$header .= 'Reply-to: ' . $reply_name . ' <' . $reply_address . ">\r\n";

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
		 */
		public function trigger( $order_id ) {

			// bail if no order ID is present
			if ( ! $order_id ) {
				return;
			}

			// setup order object
			$this->object = new WC_Order( $order_id );
			$order_items  = self::get_limited_ordered_products( $this->object->get_items(), $this->prefix );

			if ( empty( $order_items ) ) {
				return;
			}

			// Replacements
			$this->placeholders['{order_date}']          = date_i18n( woocommerce_date_format(), strtotime( $this->object->order_date ) );
			$this->placeholders['{order_id}']            = $this->object->get_order_number();
			$this->placeholders['{site_title}']          = arp_get_option( $this->prefix . 'shop_name_text', 1, get_bloginfo( 'name' ) );
			$this->placeholders['{customer_first_name}'] = $this->object->get_billing_first_name();
			$this->placeholders['{customer_last_name}']  = $this->object->get_billing_last_name();
			$this->placeholders['{customer_full_name}']  = $this->object->get_billing_first_name() . ' ' . $this->object->get_billing_last_name();
			$this->placeholders['{list_of_products}']    = $this->get_links_ordered_items( $order_items );
			$this->placeholders['{review_link}']         = $this->get_links_ordered_items( $order_items, true );

			$this->heading   = $this->format_string( arp_get_option( $this->prefix . 'email_heading_text', 2 ) );
			$this->recipient = $this->object->get_billing_email();
			$this->subject   = $this->format_string( arp_get_option( $this->prefix . 'email_subject_text', 2 ) );

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
			$body          = $this->format_string( arp_get_option( $this->prefix . 'email_body_text', 2 ) );

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
		 * Check which products are eligible
		 *
		 * @param $order_items
		 * @param $prefix
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_limited_ordered_products( $order_items, $prefix ) {

			$only_cats     = array_map( 'intval', arp_get_option( $prefix . 'sending_delay_cats_select', 2 ) );
			$only_tags     = array_map( 'intval', arp_get_option( $prefix . 'sending_delay_tags_select', 2 ) );
			$only_products = array_map( 'intval', arp_get_option( $prefix . 'sending_delay_products_select', 2 ) );

			// Check categories
			if ( $only_cats ) {

				$included_items = array();
				foreach ( $order_items as $order_item ) {

					$product_id   = $order_item->get_product_id();
					$product_cats = get_the_terms( $product_id, 'product_cat' );
					// Check if product has category
					foreach ( $product_cats as $product_cat ) {

						if ( in_array( $product_cat->term_id, $only_cats, true ) ) {
							$included_items[] = $order_item;
							break;
						}
					}
				}

				$order_items = $included_items;
			}

			if ( ! $order_items ) {
				return array();
			}

			// Check tags
			if ( $only_tags ) {

				$included_items = array();
				foreach ( $order_items as $order_item ) {

					$product_id   = $order_item->get_product_id();
					$product_tags = get_the_terms( $product_id, 'product_tag' );
					// Check if product has tag
					foreach ( $product_tags as $product_tag ) {

						if ( in_array( $product_tag->term_id, $only_tags, true ) ) {
							$included_items[] = $order_item;
							break;
						}
					}
				}

				$order_items = $included_items;
			}

			if ( ! $order_items ) {
				return array();
			}

			// Check products
			if ( $only_products ) {

				$included_items = array();
				foreach ( $order_items as $order_item ) {

					$product_id = $order_item->get_product_id();
					if ( in_array( $product_id, $only_products, true ) ) {
						$included_items[] = $order_item;
					}
				}

				$order_items = $included_items;
			}

			return $order_items;
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
