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

if ( ! class_exists( 'Advanced_Reviews_Pro_Reminders' ) ) {

	class Advanced_Reviews_Pro_Reminders {

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
		}

		/**
		 * Add a new WC email for reminders
		 *
		 * @param $email_classes
		 *
		 * @return mixed
		 */
		public function add_review_reminder_woocommerce_email( $email_classes ) {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-reminders-email.php';

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

		public function handle_multiple_reviews_visit() {
			
			if ( 'true' === get_query_var( 'arp-add-reviews' ) ) {
				// TODO
			}
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

			$sending_delay      = arp_get_option( $this->prefix . 'sending_delay_text', 2 );
			$sending_delay_unit = arp_get_option( $this->prefix . 'sending_delay_unit_text', 2 );

			if ( ! $sending_delay ) {
				return;
			}

			$units = 0;
			switch ( $sending_delay_unit ) {
				case 'minutes':
					$units = 60;
					break;
				case 'hours':
					$units = 60 * 60;
					break;
				case 'days':
					$units = 60 * 60 * 60;
					break;
			}

			wp_schedule_single_event( time() + ( $sending_delay * $units ), 'send_reminder_review_email_event', array( $order_id ) );

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

			global $theorder;

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
		 */
		public function send_reminder_review_email( $order_id ) {

			$order = wc_get_order( $order_id );

			global $woocommerce;
			$reminder_email = $woocommerce->mailer()->emails['WC_Review_Reminder_Email'];
			$reminder_email->trigger( $order_id, $order );
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
if ( ! function_exists( 'advanced_reviews_pro_reminders' ) ) {

	function advanced_reviews_pro_reminders() {
		return Advanced_Reviews_Pro_Reminders::instance();
	}
}
