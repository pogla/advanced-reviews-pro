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

if ( ! class_exists( 'Advanced_Reviews_Pro_Reminders' ) ) {

	class Advanced_Reviews_Pro_Reminders {

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
		 * Add a new WC email for reminders
		 *
		 * @param $email_classes
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function add_review_reminder_woocommerce_email( $email_classes ) {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/emails/class-advanced-reviews-pro-email.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/emails/class-advanced-reviews-pro-reminders-email.php';

			// add the email class to the list of email classes that WooCommerce loads
			$email_classes['WC_Review_Reminder_Email'] = wc_review_reminder_email();

			return $email_classes;
		}

		/**
		 * Register query vars
		 *
		 * @param $vars
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function add_query_vars( $vars ) {
			$vars[] = 'arp-add-review';
			$vars[] = 'arp-add-reviews';
			return $vars;
		}

		/**
		 * @since 1.0.0
		 *
		 * When user visits with arp-add-reviews query var, remember all items to review in the session
		 */
		public function handle_multiple_reviews_visit_session() {

			$order_id             = get_query_var( 'arp-add-reviews' );
			$order                = wc_get_order( $order_id );
			$current_session_data = WC()->session->get( ARP_PREFIX . 'products-to-review' );

			if ( ! is_a( $order, 'WC_Order' ) || $order_id === $current_session_data['order_id'] ) {
				return;
			}

			$products    = array( 'order_id' => $order_id );
			$order_items = self::get_limited_ordered_products( $order->get_items() );

			if ( ! $order_items ) {
				return;
			}

			foreach ( $order_items as $order_item ) {
				$products['items'][] = $order_item->get_product_id();
			}

			WC()->session->set( ARP_PREFIX . 'products-to-review', $products );
		}

		/**
		 *
		 * Redirects to the next product to review. Only works with review pre-generated link.
		 *
		 * @param $location
		 * @since 1.0.0
		 *
		 * @return string $location
		 */
		public function redirect_after_review( $location ) {

			$product_id = intval( $_POST['comment_post_ID'] );
			$product    = wc_get_product( $product_id );

			if ( ! is_a( $product, 'WC_Product' ) ) {
				return $location;
			}

			$next_product_url = self::get_next_product_url_to_review( $product_id );

			if ( false === $next_product_url ) {
				return $location;
			} else {
				return $next_product_url . '#reviews';
			}
		}

		/**
		 * Get next product in line to review, remove the current one from the session
		 *
		 * @param $current_product_id
		 * @since 1.0.0
		 *
		 * @return false|string Url of the next product to review
		 */
		private static function get_next_product_url_to_review( $current_product_id ) {

			$current_session_data = WC()->session->get( ARP_PREFIX . 'products-to-review' );

			if ( $current_session_data && count( $current_session_data['items'] ) > 0 ) {

				$next_item_id = false;
				foreach ( $current_session_data['items'] as $key => $item ) {

					if ( $current_product_id === $item ) {
						unset( $current_session_data['items'][ $key ] );
					} elseif ( false === $next_item_id ) {
						$next_item_id = $item;
					}
				}

				WC()->session->set( ARP_PREFIX . 'products-to-review', $current_session_data );

				if ( $next_item_id ) {
					return apply_filters( 'arp_redirect_to_next_product', get_permalink( $next_item_id ), $next_item_id );
				}

				// If there is no next product to review
				if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_coupon_review_reminder_checkbox', 3 ) ) {
					global $woocommerce;
					$reminder_email = $woocommerce->mailer()->emails['WC_Review_Coupons_Email'];
					$reminder_email->trigger_order_review_coupon( $current_session_data['order_id'] );
				}
			}

			return false;
		}

		/**
		 * Adds a notice below the comment form. Only show on un-reviewed products from the active reviewing order
		 *
		 * @param $comment_form
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function add_review_reminder_comment_notice( $comment_form ) {

			$current_session_data = WC()->session->get( ARP_PREFIX . 'products-to-review' );

			if ( $current_session_data && count( $current_session_data['items'] ) > 0 && in_array( get_the_ID(), $current_session_data['items'], true ) ) {

				$comment_form['comment_field'] .= '<p><b>' . __( 'You are reviewing item from your order. After you leave a review, you will be redirected to the next item.', 'advanced-reviews-pro' ) . '</b></p>';
			}

			return $comment_form;
		}

		/**
		 * Check which products are eligible
		 *
		 * @param $order_items
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public static function get_limited_ordered_products( $order_items ) {

			$only_cats     = array_map( 'intval', arp_get_option( ARP_PREFIX . 'sending_delay_cats_select', 2 ) );
			$only_tags     = array_map( 'intval', arp_get_option( ARP_PREFIX . 'sending_delay_tags_select', 2 ) );
			$only_products = array_map( 'intval', arp_get_option( ARP_PREFIX . 'sending_delay_products_select', 2 ) );

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

			return apply_filters( 'arp_get_limited_ordered_products', $order_items );
		}

		/**
		 * Trigger email when order changes to status completed
		 *
		 * @param $order_id
		 * @since  1.0.0
		 */
		public function order_status_completed( $order_id ) {

			$is_send = get_post_meta( $order_id, '_review_reminder_sent', true );

			if ( $is_send ) {
				return;
			}

			$sending_delay      = arp_get_option( ARP_PREFIX . 'sending_delay_text', 2 );
			$sending_delay_unit = arp_get_option( ARP_PREFIX . 'sending_delay_unit_text', 2 );

			if ( ! $sending_delay ) {
				return;
			}

			wp_schedule_single_event( time() + ( $sending_delay * Advanced_Reviews_Pro_Functions::get_seconds_from_units( $sending_delay_unit ) ), 'send_reminder_review_email_event', array( $order_id ) );

			update_post_meta( $order_id, '_review_reminder_sent', true );
		}

		/**
		 * Add order action for review reminder
		 *
		 * @param $actions
		 * @since 1.0.0
		 *
		 * @return mixed
		 */
		public function add_reminder_order_action( $actions ) {

			// add "mark printed" custom action
			$actions['wc_review_reminder_action'] = __( 'Send review reminder', 'advanced-reviews-pro' );
			return $actions;
		}

		/**
		 * Process manual review reminder trigger
		 *
		 * @since 1.0.0
		 * @param WC_Order $order
		 */
		public function process_reminder_order_action( $order ) {

			$order_id = $order->get_id();

			$this->send_reminder_review_email( $order_id );

			// add the order note
			$message = __( 'Review reminder manually triggered.', 'advanced-reviews-pro' );
			$order->add_order_note( $message );

			update_post_meta( $order_id, '_review_reminder_sent', true );
		}

		/**
		 * Trigger review reminder email for order.
		 *
		 * @param $order_id
		 * @since  1.0.0
		 */
		public function send_reminder_review_email( $order_id ) {

			global $woocommerce;
			$reminder_email = $woocommerce->mailer()->emails['WC_Review_Reminder_Email'];
			$reminder_email->trigger( $order_id );
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
if ( ! function_exists( 'advanced_reviews_pro_reminders' ) ) {

	function advanced_reviews_pro_reminders() {
		return Advanced_Reviews_Pro_Reminders::instance();
	}
}
