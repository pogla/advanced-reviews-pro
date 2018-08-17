<?php

/**
 * Coupons generation
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro_Coupons' ) ) {

	class Advanced_Reviews_Pro_Coupons {

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

		/**
		 * Add a new WC email for coupons
		 *
		 * @param $email_classes
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function add_review_coupons_woocommerce_email( $email_classes ) {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/emails/class-advanced-reviews-pro-email.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/emails/class-advanced-reviews-pro-coupons-email.php';

			// add the email class to the list of email classes that WooCommerce loads
			$email_classes['WC_Review_Coupons_Email'] = wc_review_coupons_email();

			return $email_classes;
		}

		/**
		 * Trigger email
		 *
		 * @param $location
		 * @since 1.0.0
		 *
		 * @return string $location
		 */
		public function send_coupon_after_review( $location ) {

			$product_id = intval( $_POST['comment_post_ID'] );
			$comment_id = intval( $_POST['comment_ID'] );
			$user_id    = get_current_user_id();

			// Redirect if not product or user not logged in
			if ( ! is_a( wc_get_product( $product_id ), 'WC_Product' ) || ! $user_id ) {
				return $location;
			}

			global $woocommerce;
			$reminder_email = $woocommerce->mailer()->emails['WC_Review_Coupons_Email'];
			$reminder_email->trigger( $user_id, $product_id, $comment_id );

			return $location;
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
if ( ! function_exists( 'advanced_reviews_pro_coupons' ) ) {

	function advanced_reviews_pro_coupons() {
		return Advanced_Reviews_Pro_Coupons::instance();
	}
}
