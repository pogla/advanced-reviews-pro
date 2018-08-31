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
 * Helper functions
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro_Functions' ) ) {

	class Advanced_Reviews_Pro_Functions {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Transform minutes, hours, days to seconds
		 *
		 * @since 1.0.0
		 * @param $type
		 *
		 * @return float|int
		 */
		public static function get_seconds_from_units( $type ) {

			$units = 0;
			switch ( $type ) {
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

			return $units;
		}

		/**
		 * Update all the comments with meta, so the sorting by votes will work
		 *
		 * @since 1.0.0
		 */
		public static function update_comments_with_meta() {

			if ( isset( $_POST[ ARP_PREFIX . 'enable_votes_sorting_checkbox' ] ) ) {

				global $wpdb;
				$prefix = ARP_PREFIX;
				$sql    = "SELECT cm.comment_ID FROM $wpdb->comments cm LEFT JOIN $wpdb->commentmeta cmm ON cmm.comment_id = cm.comment_ID AND cmm.meta_key = '{$prefix}total_votes' WHERE cmm.meta_key is null AND comment_parent = 0 AND comment_type NOT IN ('order_note','webhook_delivery','action_log')";

				$results = $wpdb->get_results( $sql, ARRAY_A );

				if ( $results && isset( $results[0]['comment_ID'] ) ) {

					foreach ( $results as $comment ) {
						update_comment_meta( $comment['comment_ID'], ARP_PREFIX . 'total_votes', 0 );
					}
				}
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
if ( ! function_exists( 'advanced_reviews_pro_functions' ) ) {

	function advanced_reviews_pro_functions() {
		return Advanced_Reviews_Pro_Functions::instance();
	}
}
