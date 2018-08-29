<?php

/**
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Reviews summary per rating graph.
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro_Summary' ) ) {

	class Advanced_Reviews_Pro_Summary {

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
		 * Register query var for filtering by ratings
		 *
		 * @since 1.0.0
		 *
		 * @param $vars
		 *
		 * @return array
		 */
		public function add_query_vars( $vars ) {
			$vars[] = 'arp-rating';
			return $vars;
		}

		/**
		 * Include summary by ratings template
		 *
		 * @since 1.0.0
		 *
		 * @param $args
		 *
		 * @return mixed
		 */
		public function add_summary( $args ) {

			$comments_counts = $this->get_count_reviews_by_score();

			require plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/advanced-reviews-pro-admin-summary.php';

			return $args;
		}

		/**
		 * Filter Comment Query by rating
		 *
		 * @since 1.0.0
		 *
		 * @param $q
		 */
		public function parse_comment_query( $q ) {

			global $is_comment_summary;

			$filter_score = get_query_var( 'arp-rating', false );

			if ( is_product() && $filter_score && true !== $is_comment_summary ) {
				$meta_query_args = self::get_meta_query_by_score( absint( $filter_score ) );
				if ( ! $q->query_vars['meta_query'] ) {
					$q->query_vars['meta_query'] = $meta_query_args;
				} else {
					$q->query_vars['meta_query'][] = $meta_query_args[0];
					$q->query_vars['meta_query'][] = $meta_query_args[1];
				}
			}
		}

		/**
		 * Get review counts by ratings
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function get_count_reviews_by_score() {

			global $is_comment_summary;

			$is_comment_summary = true;
			$grouped_by_score   = array();
			$total_reviews      = 0;

			for ( $i = 1; $i <= 5; $i++ ) {

				$args = array(
					'post_status' => 'publish',
					'post_type'   => 'product',
					'status'      => 'approve',
					'post_id'     => get_the_ID(),
					'parent'      => 0,
					'meta_query'  => self::get_meta_query_by_score( $i ),
				);

				$comments_count = count( get_comments( $args ) );

				$total_reviews += $comments_count;

				$grouped_by_score['ratings'][ $i ] = $comments_count;

			}

			$grouped_by_score['total'] = $total_reviews;
			$is_comment_summary        = false;

			return $grouped_by_score;
		}

		/**
		 * Generates meta_query args
		 *
		 * @since 1.0.0
		 *
		 * @param $score
		 *
		 * @return array
		 */
		public static function get_meta_query_by_score( $score ) {

			return array(
				'relation' => 'AND',
				array(
					'key'     => 'rating',
					'value'   => $score,
					'compare' => '=',
					'type'    => 'numeric',
				),
			);
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
if ( ! function_exists( 'advanced_reviews_pro_summary' ) ) {

	function advanced_reviews_pro_summary() {
		return Advanced_Reviews_Pro_Summary::instance();
	}
}

