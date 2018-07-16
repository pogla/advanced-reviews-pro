<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Handle recaptcha on WooCommerce reviews
 *namediv
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Advanced_Reviews_Pro_Summary' ) ) {

	class Advanced_Reviews_Pro_Summary {

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
		 * Review score max.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $review_score_max
		 */
		private $review_score_max;

		/**
		 * @since    1.0.0
		 *
		 * @param $review_score_max int
		 */
		public function __construct( $review_score_max ) {

			$this->review_score_max = $review_score_max;
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
				$meta_query_args = self::get_meta_query_by_score( absint( $filter_score ), $this->review_score_max );
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

			for ( $i = 1; $i <= $this->review_score_max; $i++ ) {

				$args = array(
					'post_status' => 'publish',
					'post_type'   => 'product',
					'status'      => 'approve',
					'post_id'     => get_the_ID(),
					'parent'      => 0,
					'meta_query'  => self::get_meta_query_by_score( $i, $this->review_score_max ),
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
		 * @param $score_max
		 *
		 * @return array
		 */
		public static function get_meta_query_by_score( $score, $score_max ) {

			$step_size   = 5 / $score_max;
			$start_range = 1 === $score ? 0 : ( ( $score - ( 1 / 2 ) ) * $step_size );
			$end_range   = $score_max === $score ? 5 : ( ( ( 1 / 2 ) + $score ) * $step_size );

			return array(
				'relation' => 'AND',
				array(
					'key'     => 'rating',
					'value'   => $start_range,
					'compare' => '>',
					'type'    => 'string',
				),
				array(
					'key'     => 'rating',
					'value'   => $end_range,
					'compare' => '<=',
					'type'    => 'string',
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
		public static function instance( $review_score_max ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $review_score_max );
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
	function advanced_reviews_pro_summary( $review_score_max ) {
		return Advanced_Reviews_Pro_Summary::instance( $review_score_max );
	}
}

