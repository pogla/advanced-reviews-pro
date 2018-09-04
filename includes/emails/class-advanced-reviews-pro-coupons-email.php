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
			$this->title = __( 'Review Coupons', 'advanced-reviews-pro' );
			/* translators: %1$s: Link */
			$this->description = sprintf( __( 'Review coupons email. Edit settings %1$shere%2$s.', 'advanced-reviews-pro' ), '<a href="' . admin_url( 'admin.php?page=arp_tab3_options' ) . '">', '</a>' );

			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
		}

		/**
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @param int $user_id
		 * @param $product
		 * @param $comment_id
		 *
		 * @return void
		 * @since 1.0.0
		 *
		 */
		public function trigger( $user_id, $product, $comment_id ) {

			// bail if no order ID is present
			if ( ! $user_id ) {
				return;
			}

			// setup order object
			$this->object    = get_userdata( $user_id );
			$this->recipient = $this->object->user_email;
			$coupon          = $this->get_or_generate_coupon( $comment_id, $this->recipient );

			if ( ! $this->is_enabled() || ! $this->recipient || ! $this->can_send_email( $user_id, 'user' ) ) {
				return;
			}

			// Replacements
			$this->placeholders['{site_title}']             = arp_get_option( ARP_PREFIX . 'shop_name_text', 1, get_bloginfo( 'name' ) );
			$this->placeholders['{user_first_name}']        = $this->object->first_name;
			$this->placeholders['{user_last_name}']         = $this->object->last_name;
			$this->placeholders['{user_full_name}']         = $this->object->first_name . ' ' . $this->object->last_name;
			$this->placeholders['{user_display_name}']      = $this->object->display_name;
			$this->placeholders['{reviewed_product_name}']  = get_the_title( $product );
			$this->placeholders['{coupon_expiration_date}'] = $coupon['coupon_expiration_date'];
			$this->placeholders['{coupon}']                 = $coupon['coupon_code'];

			$this->heading = $this->format_string( arp_get_option( ARP_PREFIX . 'review_coupon_email_heading_text', 3 ) );
			$this->subject = $this->format_string( arp_get_option( ARP_PREFIX . 'review_coupon_email_subject_text', 3 ) );

			update_user_meta( $user_id, '_' . ARP_PREFIX . 'user_last_sent_email', current_time( 'timestamp' ) );

			do_action_ref_array( 'arp_before_send_review_coupon_email', array( &$this ) );

			// Woohoo, send the email!
			$this->send( $this->recipient, $this->subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );

			do_action( 'arp_after_send_review_coupon_email', $user_id, $product, $comment_id );
		}

		/**
		 * Trigger email after reminder order review
		 *
		 * @since 1.0.0
		 * @param $order_id
		 */
		public function trigger_order_review_coupon( $order_id ) {

			if ( ! $order_id ) {
				return;
			}

			$this->object    = wc_get_order( $order_id );
			$this->recipient = $this->object->get_billing_email();
			$coupon          = $this->get_or_generate_coupon( $order_id, $this->recipient, 'order' );

			if ( ! $this->is_enabled() || ! $this->recipient || ! $this->can_send_email( $order_id ) ) {
				return;
			}

			$this->heading   = $this->format_string( arp_get_option( ARP_PREFIX . 'review_coupon_email_heading_text', 3 ) );
			$this->subject   = $this->format_string( arp_get_option( ARP_PREFIX . 'review_coupon_email_subject_text', 3 ) );
			$this->recipient = $this->object->get_billing_email();
			$full_name       = $this->object->get_billing_first_name() . ' ' . $this->object->get_billing_last_name();

			$this->placeholders['{site_title}']             = arp_get_option( ARP_PREFIX . 'shop_name_text', 1, get_bloginfo( 'name' ) );
			$this->placeholders['{user_first_name}']        = $this->object->get_billing_first_name();
			$this->placeholders['{user_last_name}']         = $this->object->get_billing_last_name();
			$this->placeholders['{user_full_name}']         = $full_name;
			$this->placeholders['{user_display_name}']      = $full_name;
			$this->placeholders['{reviewed_product_name}']  = __( 'Ordered products', 'advanced-reviews-pro' );
			$this->placeholders['{coupon_expiration_date}'] = $coupon['coupon_expiration_date'];
			$this->placeholders['{coupon}']                 = $coupon['coupon_code'];

			update_post_meta( $order_id, '_' . ARP_PREFIX . 'order_last_sent_email', current_time( 'timestamp' ) );

			do_action_ref_array( 'arp_before_send_review_order_coupon_email', array( &$this ) );

			// Woohoo, send the email!
			$this->send( $this->recipient, $this->subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );

			do_action( 'arp_after_send_review_order_coupon_email', $order_id );

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

			include plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'public/templates/emails/review-email.php';

			return ob_get_clean();
		}

		/**
		 * Get coupon code. Maybe generate coupon.
		 *
		 * @since 1.0.0
		 * @param $id
		 * @param $user_email
		 * @param string $type
		 *
		 * @return array
		 */
		public static function get_or_generate_coupon( $id, $user_email, $type = 'review' ) {

			$coupon_type = arp_get_option( ARP_PREFIX . 'coupon_type_select', 3 );
			if ( 'existing_coupon' === $coupon_type ) {

				$coupon_id = arp_get_option( ARP_PREFIX . 'existing_coupon_select', 3 );

				if ( $coupon_id ) {
					return apply_filters( 'arp_get_existing_coupon', array(
						'coupon_code'            => wc_get_coupon_code_by_id( $coupon_id ),
						'coupon_expiration_date' => get_post_meta( $coupon_id, 'expiry_date', true ),
					) );
				}
			} elseif ( 'generate_coupon' === $coupon_type ) {

				$discount_type       = arp_get_option( ARP_PREFIX . 'generate_coupon_discount_type_select', 3, 'percent' );
				$coupon_amount       = arp_get_option( ARP_PREFIX . 'generate_coupon_amount_text', 3, 0 );
				$allow_free_shipping = arp_get_option( ARP_PREFIX . 'generate_coupon_allow_free_shipping_checkbox', 3, 'no' );
				$validity            = arp_get_option( ARP_PREFIX . 'generate_coupon_validity_text', 3 );
				$min_spend           = arp_get_option( ARP_PREFIX . 'generate_coupon_min_text', 3, '' );
				$max_spend           = arp_get_option( ARP_PREFIX . 'generate_coupon_max_text', 3, '' );
				$individual_use      = arp_get_option( ARP_PREFIX . 'generate_coupon_individual_use_only_checkbox', 3, 'no' );
				$exclude_sale_items  = arp_get_option( ARP_PREFIX . 'generate_coupon_exclude_sale_items_checkbox', 3 );
				$limit_products      = arp_get_option( ARP_PREFIX . 'generate_coupon_products_select', 3 );
				$exclude_products    = arp_get_option( ARP_PREFIX . 'generate_coupon_exclude_products_select', 3 );
				$limit_cats          = arp_get_option( ARP_PREFIX . 'generate_coupon_product_categories_select', 3 );
				$exclude_cats        = arp_get_option( ARP_PREFIX . 'generate_coupon_exclude_product_categories_select', 3 );
				$allowed_emails      = arp_get_option( ARP_PREFIX . 'generate_coupon_email_restrict_text', 3 );
				$usage_limit         = arp_get_option( ARP_PREFIX . 'generate_coupon_usage_restrict_text', 3 );
				$usage_x_limit       = arp_get_option( ARP_PREFIX . 'generate_coupon_limit_usage_x_items_text', 3 );
				$format              = arp_get_option( ARP_PREFIX . 'generate_coupon_format_text', 3 );

				// TODO: check if coupon exists and repeat
				preg_match_all( '/{(.*?)}/', $format, $matches );

				$matches = $matches[0];

				foreach ( $matches as $key => $match ) {
					if ( false !== strpos( $match, 'RANDOM' ) ) {
						$matches[ $key ] = substr( str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ), 0, preg_replace( '/[^0-9]/', '', $match ) );
					} else {
						$matches[ $key ] = str_replace( '{', '', $match );
						$matches[ $key ] = str_replace( '}', '', $matches[ $key ] );
					}
				}

				$coupon_id = wp_insert_post(
					array(
						'post_title'   => sanitize_title( implode( '', $matches ) ),
						'post_content' => '',
						'post_status'  => 'publish',
						'post_author'  => 1,
						'post_type'    => 'shop_coupon',
					)
				);

				if ( $coupon_id ) {
					if ( $allowed_emails ) {
						$allowed_emails = explode( ',', $allowed_emails );

						foreach ( $allowed_emails as $key => $allowed_email ) {
							if ( '{BUYER_EMAIL}' === $allowed_email ) {
								$allowed_emails[ $key ] = $user_email;
								break;
							}
						}
						update_post_meta( $coupon_id, 'customer_email', $allowed_emails );
					}

					if ( $min_spend ) {
						update_post_meta( $coupon_id, 'minimum_amount', floatval( $min_spend ) );
					}

					if ( $max_spend ) {
						update_post_meta( $coupon_id, 'maximum_amount', floatval( $max_spend ) );
					}

					if ( $usage_limit ) {
						update_post_meta( $coupon_id, 'usage_limit', absint( $usage_limit ) );
					}

					if ( $usage_x_limit ) {
						update_post_meta( $coupon_id, 'limit_usage_to_x_items', absint( $usage_x_limit ) );
					}

					if ( $limit_products ) {
						update_post_meta( $coupon_id, 'product_ids', implode( ',', $limit_products ) );
					}

					if ( $exclude_products ) {
						update_post_meta( $coupon_id, 'exclude_product_ids', implode( ',', $exclude_products ) );
					}

					if ( $limit_cats ) {
						update_post_meta( $coupon_id, 'product_categories', implode( ',', $limit_cats ) );
					}

					if ( $exclude_cats ) {
						update_post_meta( $coupon_id, 'exclude_product_categories', implode( ',', $exclude_cats ) );
					}

					if ( $validity ) {
						$expiry_date = date( 'Y-m-d', current_time( 'timestamp', 0 ) + ( 24 * 60 * 60 * $validity ) );
						update_post_meta( $coupon_id, 'expiry_date', $expiry_date );
						update_post_meta( $coupon_id, 'date_expires', strtotime( $expiry_date ) );
					}

					update_post_meta( $coupon_id, 'individual_use', 'on' === $individual_use ? 'yes' : '' );
					update_post_meta( $coupon_id, 'free_shipping', 'on' === $allow_free_shipping ? 'yes' : '' );
					update_post_meta( $coupon_id, 'exclude_sale_items', 'on' === $exclude_sale_items ? 'yes' : '' );
					update_post_meta( $coupon_id, 'discount_type', $discount_type );
					update_post_meta( $coupon_id, 'coupon_amount', floatval( $coupon_amount ) );
					update_post_meta( $coupon_id, ARP_PREFIX . 'auto_generated', true );

					if ( 'review' === $type ) {
						update_post_meta( $coupon_id, ARP_PREFIX . 'generated_from_comment_id', $id );
					} elseif ( 'order' === $type ) {
						update_post_meta( $coupon_id, ARP_PREFIX . 'generated_from_order_id', $id );
					}

					return apply_filters( 'arp_get_generated_coupon', array(
						'coupon_code'            => wc_get_coupon_code_by_id( $coupon_id ),
						'coupon_expiration_date' => isset( $expiry_date ) ? $expiry_date : __( 'Never', 'advanced-reviews-pro' ),
					) );
				}
			}

			return apply_filters( 'arp_get_empty_coupon', array(
				'coupon_code'            => __( 'No Coupon', 'advanced-reviews-pro' ),
				'coupon_expiration_date' => '',
			) );
		}

		/**
		 * Class Instance
		 *
		 * @static
		 * @since  1.0.0
		 *
		 * @return object instance
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
