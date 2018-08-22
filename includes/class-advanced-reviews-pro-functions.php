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
		 * Update all the comments with meta, so the sorting will work
		 *
		 * @since 1.0.0
		 */
		public static function update_comments_with_meta() {

			if ( isset( $_POST[ ARP_PREFIX . 'enable_votes_sorting_checkbox' ] ) ) {

				$args = array(
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => ARP_PREFIX . 'total_votes',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'rating',
							'compare' => 'EXISTS',
						),
					),
				);

				$comments = get_comments( $args );

				if ( $comments ) {
					foreach ( $comments as $comment ) {
						update_comment_meta( $comment->comment_ID, ARP_PREFIX . 'total_votes', 0 );
					}
				}
			}
		}

		/**
		 * Add comment meta when comment created
		 *
		 * @since 1.0.0
		 * @param $comment_id
		 *
		 * @return mixed|void
		 */
		public function add_comment_post_meta( $comment_id ) {

			$product_id = get_comment( $comment_id )->comment_post_ID;
			$product    = wc_get_product( $product_id );

			if ( ! $product ) {
				return;
			}

			update_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', 0 );
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
