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
 * Handle coupons for reviews
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Review_Coupons_Email' ) ) {

	class WC_Review_Coupons_Email extends Advanced_Reviews_Pro_WC_Review_Email {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->id    = 'wc_review_coupon';
			$this->title = 'Review Coupons';
			/* translators: %1$s: Link */
			$this->description = sprintf( __( 'Review coupons email. Edit settings %1$shere%2$s.' ), '<a href="' . admin_url( 'admin.php?page=arp_tab3_options' ) . '">', '</a>' );

			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
		}

		/**
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @param int $user_id
		 * @param $product
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger( $user_id, $product ) {

			// bail if no order ID is present
			if ( ! $user_id ) {
				return;
			}

			// setup order object
			$this->object = get_userdata( $user_id );

			// Replacements
			$this->placeholders['{site_title}']            = arp_get_option( ARP_PREFIX . 'shop_name_text', 1, get_bloginfo( 'name' ) );
			$this->placeholders['{user_first_name}']       = $this->object->first_name;
			$this->placeholders['{user_last_name}']        = $this->object->last_name;
			$this->placeholders['{user_full_name}']        = $this->object->first_name . ' ' . $this->object->last_name;
			$this->placeholders['{user_display_name}']     = $this->object->display_name;
			$this->placeholders['{reviewed_product_name}'] = get_the_title( $product );
			$this->placeholders['{coupon}']                = self::get_coupon_code();

			$this->heading   = $this->format_string( arp_get_option( ARP_PREFIX . 'review_coupon_email_heading_text', 3 ) );
			$this->recipient = $this->object->user_email;
			$this->subject   = $this->format_string( arp_get_option( ARP_PREFIX . 'review_coupon_email_subject_text', 3 ) );

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

			$email_heading = $this->get_heading();
			$plain_text    = false;
			$email         = $this;
			$body          = $this->format_string( arp_get_option( ARP_PREFIX . 'review_coupon_email_body_text', 3 ) );

			include plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'public/templates/emails/review-reminder.php';

			return ob_get_clean();
		}

		private static function get_coupon_code() {

			$coupon_type = arp_get_option( ARP_PREFIX . 'coupon_type_select', 3 );
			if ( 'existing_coupon' === $coupon_type ) {

				$coupon = arp_get_option( ARP_PREFIX . 'existing_coupon_select', 3 );
				return ! $coupon ? __( 'No Coupon', 'advanced-reviews-pro' ) : wc_get_coupon_code_by_id( $coupon );

			} elseif ( 'generate_coupon' === $coupon_type ) {

				$discount_type       = arp_get_option( ARP_PREFIX . 'generate_coupon_discount_type_select', 3 );
				$coupon_amount       = arp_get_option( ARP_PREFIX . 'generate_coupon_amount_text', 3 );
				$allow_free_shipping = arp_get_option( ARP_PREFIX . 'generate_coupon_allow_free_shipping_checkbox', 3 );
				$validity            = arp_get_option( ARP_PREFIX . 'generate_coupon_validity_text', 3 );
				$validity_unit       = arp_get_option( ARP_PREFIX . 'generate_coupon_validity_unit_select', 3 );
				$min_spend           = arp_get_option( ARP_PREFIX . 'generate_coupon_min_text', 3 );
				$max_spend           = arp_get_option( ARP_PREFIX . 'generate_coupon_max_text', 3 );
				$individual_use      = arp_get_option( ARP_PREFIX . 'generate_coupon_individual_use_only_checkbox', 3 );
				$exclude_sale_items  = arp_get_option( ARP_PREFIX . 'generate_coupon_exclude_sale_items_checkbox', 3 );
				$limit_products      = arp_get_option( ARP_PREFIX . 'generate_coupon_products_select', 3 );
				$exclude_products    = arp_get_option( ARP_PREFIX . 'generate_coupon_exclude_products_select', 3 );
				$limit_cats          = arp_get_option( ARP_PREFIX . 'generate_coupon_product_categories_select', 3 );
				$exclude_cats        = arp_get_option( ARP_PREFIX . 'generate_coupon_exclude_product_categories_select', 3 );

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
if ( ! function_exists( 'wc_review_coupons_email' ) ) {

	function wc_review_coupons_email() {
		return WC_Review_Coupons_Email::instance();
	}
}
