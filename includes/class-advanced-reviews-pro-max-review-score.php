<?php

/**
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Changes max stars for reviews
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro_Max_Review_Score' ) ) {

	class Advanced_Reviews_Pro_Max_Review_Score {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Max score.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $max_score Max review score.
		 */
		private $max_score;

		/**
		 * @since    1.0.0
		 *
		 * @param string $max_score
		 */
		public function __construct( $max_score ) {
			$this->max_score = $max_score;
		}

		/**
		 * Insert current max review score
		 *
		 * @since    1.0.0
		 *
		 * @param $comment_id
		 */
		public function insert_current_review_score( $comment_id ) {

			$score = absint( esc_attr( $_POST['arp-rating'] ) );
			update_comment_meta( $comment_id, 'rating', round( ( $score / $this->max_score ) * 5, 2, PHP_ROUND_HALF_UP ) );
		}

		/**
		 * Makes sure rating is saved to database with decimals
		 *
		 * @since 1.0.0
		 *
		 * @param $data
		 *
		 * @return mixed
		 */
		public function save_comment_admin( $data ) {

			// Not allowed, return regular value without updating meta.
			if ( ! isset( $_POST['woocommerce_meta_nonce'], $_POST['rating'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // WPCS: input var ok, sanitization ok.
				return $data;
			}

			if ( $_POST['rating'] > 5 || $_POST['rating'] < 0 ) { // WPCS: input var ok.
				return $data;
			}

			$comment_id = $data['comment_ID'];

			update_comment_meta( $comment_id, 'rating', round( wp_unslash( $_POST['rating'] ), 2, PHP_ROUND_HALF_UP ) ); // WPCS: input var ok.

			// Return regular value after updating.
			return $data;

		}

		/**
		 * Changes the number of stars in comment form
		 *
		 * @since 1.0.0
		 *
		 * @param $comment_form
		 *
		 * @return mixed
		 */
		public function custom_review_stars( $comment_form ) {

			$custom_comment_form = '';
			if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {

				$custom_comment_form .= '<div class="comment-form-rating"><div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) . '</label>';
				$custom_comment_form .= '<select name="arp-rating" id="arp-rating" aria-required="true" required>';
				for ( $i = 0; $i <= $this->max_score; $i++ ) {
					$custom_comment_form .= '<option value="' . ( 0 === $i ? '' : $i ) . '">' . $i . '</option>';
				}
				$custom_comment_form .= '</select>';
				$custom_comment_form .= '</div>';
			}

			$comment_form['comment_field'] = $custom_comment_form . '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" required></textarea></p>';

			return $comment_form;
		}

		/**
		 * Modify ratings html
		 *
		 * @since    1.0.0
		 *
		 * @param $html
		 * @param $rating
		 * @param $count
		 *
		 * @return string
		 */
		public function woocommerce_get_star_rating_html( $html, $rating, $count ) {

			global $post;
			global $wp_query;

			if ( ! $post ) {
				return $html;
			}

			$comment_id = get_comment_ID();

			$rating_db = get_comment_meta( $comment_id, 'rating', true );

			ob_start();

			if ( is_product() && ! $comment_id ) {
				// Overall rating on single product
				?>

				<style>

					.woocommerce-product-rating .star-rating {
						width: <?php echo esc_attr( $this->max_score * 1.08 ); ?>em;
					}

					.woocommerce-product-rating .star-rating::before {
						content: '<?php echo esc_attr( str_repeat( '\73', $this->max_score ) ); ?>';
					}

					.woocommerce-product-rating .star-rating > span {
						width: <?php echo esc_attr( 100 * ( $rating / 5 ) ); ?>% !important;
					}

					.woocommerce-product-rating .star-rating > span:before {
						content: '<?php echo esc_attr( str_repeat( '\53', $this->max_score ) ); ?>';
					}

				</style>
				<?php

			} elseif ( is_product() && $wp_query->queried_object_id === $post->ID ) {
				// Ratings on single product
				?>

				<style>

					#comment-<?php echo esc_attr( $comment_id ); ?> .star-rating {
						width: <?php echo esc_attr( $this->max_score * 1.08 ); ?>em;
					}

					#comment-<?php echo esc_attr( $comment_id ); ?> .star-rating:before {
						content: '<?php echo esc_attr( str_repeat( '\73', $this->max_score ) ); ?>';
					}

					#comment-<?php echo esc_attr( $comment_id ); ?> .star-rating > span {
						width: <?php echo esc_attr( 100 * ( self::get_closest_full_int_score( $rating_db, $this->max_score ) / $this->max_score ) ); ?>% !important;
					}

					#comment-<?php echo esc_attr( $comment_id ); ?> .star-rating > span:before {
						content: '<?php echo esc_attr( str_repeat( '\53', $this->max_score ) ); ?>';
					}

				</style>
				<?php
			} elseif ( ( is_product() && $wp_query->queried_object_id !== $post->ID ) || ! is_product() ) {
				// If not single product or if related / upsell

				$rating_db = $rating;

				?>

				<style>

					.post-<?php echo esc_attr( $post->ID ); ?> .woocommerce-LoopProduct-link .star-rating {
						width: <?php echo esc_attr( $this->max_score * 1.08 ); ?>em;
					}

					.post-<?php echo esc_attr( $post->ID ); ?> .woocommerce-LoopProduct-link .star-rating:before {
						content: '<?php echo esc_attr( str_repeat( '\73', $this->max_score ) ); ?>';
					}

					.post-<?php echo esc_attr( $post->ID ); ?> .woocommerce-LoopProduct-link .star-rating > span {
						width: <?php echo esc_attr( 100 * ( $rating / 5 ) ); ?>% !important;
					}

					.post-<?php echo esc_attr( $post->ID ); ?> .woocommerce-LoopProduct-link .star-rating > span:before {
						content: '<?php echo esc_attr( str_repeat( '\53', $this->max_score ) ); ?>';
					}

					<?php if ( $this->max_score > 7 ) { ?>

					.woocommerce-LoopProduct-link .star-rating {
						max-width: 100%;
						font-size: .8em;
					}

					<?php } ?>

				</style>

				<?php

			}

			$styles = ob_get_clean();

			return $styles . $this->replace_html_review_score( $html, $rating_db, $this->max_score );
		}

		/**
		 * Replace ratings in html with new score and max score
		 * Only works if rating markup has not been changed
		 *
		 * @since    1.0.0
		 *
		 * @param $html
		 * @param $new_score
		 * @param $max_score
		 *
		 * @return mixed
		 */
		private static function replace_html_review_score( $html, $new_score, $max_score ) {

			if ( ! $new_score ) {
				return $html;
			}

			$new_score     = self::get_closest_full_int_score( $new_score, $max_score );
			$exploded_html = explode( '<strong class="rating">', $html );
			$exploded_html = explode( '</strong>', $exploded_html[1] );
			$search        = "<strong class=\"rating\">{$exploded_html[0]}</strong>";
			$replace       = "<strong class=\"rating\">{$new_score}</strong>";
			$html          = str_replace( $search, $replace, $html );
			$html          = str_replace( ' out of 5', ' out of ' . $max_score, $html );

			return $html;
		}

		/**
		 * Get closest full number rating
		 *
		 * @since 1.0.0
		 *
		 * @param $score
		 * @param $score_max
		 *
		 * @return int
		 */
		public static function get_closest_full_int_score( $score, $score_max ) {

			$step_size = 5 / $score_max;
			$score_max = absint( $score_max );

			for ( $i = 1; $i <= $score_max; $i++ ) {
				$start_range = 1 === $i ? 0 : ( ( $i - ( 1 / 2 ) ) * $step_size );
				$end_range   = $score_max === $i ? 5 : ( ( ( 1 / 2 ) + $i ) * $step_size );

				if ( $score > $start_range && $score <= $end_range ) {
					return $i;
				}
			}

			return $score_max;
		}

		/**
		 *  When changing max review ratings, we clean up or repopulate comment meta
		 *
		 * @since 1.0.0
		 *
		 */
		public static function check_if_new_max_rating_selected() {

			if ( isset( $_POST[ ARP_PREFIX . 'max_review_score_number' ] ) ) {

				$review_score_prev = absint( arp_get_option( ARP_PREFIX . 'max_review_score_number' ) );
				$review_score_new  = absint( $_POST['arp_max_review_score_number'] );

				if ( $review_score_prev !== $review_score_new && 5 === $review_score_new ) {
					self::cleanup_decimal_ratings();
				} elseif ( $review_score_prev !== $review_score_new && 5 === $review_score_prev ) {
					self::restore_decimal_ratings();
				}
			}
		}

		/**
		 * Restores up all the decimal ratings that were saved on plugin deactivation
		 *
		 * @since 1.0.0
		 */
		public static function restore_decimal_ratings() {

			global $wpdb;
			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT meta_key,meta_value,comment_id FROM $wpdb->commentmeta WHERE meta_key = 'arp_old_rating' OR meta_key = 'rating'", array() )
			);

			$structured_results = array();

			foreach ( $results as $result ) {
				$structured_results[ $result->comment_id ][ $result->meta_key ] = $result->meta_value;
			}

			foreach ( $structured_results as $comment_id => $result ) {
				if ( ! isset( $result['arp_old_rating'] ) ) {
					continue;
				}

				if ( strval( absint( round( $result['arp_old_rating'], 0, PHP_ROUND_HALF_UP ) ) ) === strval( $result['rating'] ) ) {
					update_comment_meta( $comment_id, 'rating', $result['arp_old_rating'] );
					delete_comment_meta( $comment_id, 'arp_old_rating' );
				}
			}
		}

		/**
		 * Cleans up all the decimal ratings
		 *
		 * @since 1.0.0
		 */
		public static function cleanup_decimal_ratings() {

			global $wpdb;

			// Get decimal rating numbers
			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT meta_value,comment_id FROM $wpdb->commentmeta WHERE meta_key = 'rating' AND meta_value LIKE '%.%'", array() )
			);

			if ( $results ) {
				foreach ( $results as $result ) {
					// Remember rating if decimal
					if ( strval( absint( round( $result->meta_value, 0, PHP_ROUND_HALF_UP ) ) ) !== strval( $result->meta_value ) ) {
						update_comment_meta( $result->comment_id, 'arp_old_rating', $result->meta_value );
					}
					update_comment_meta( $result->comment_id, 'rating', round( $result->meta_value, 0, PHP_ROUND_HALF_UP ) );
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
		 *
		 * @param $max_score
		 */
		public static function instance( $max_score ) {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $max_score );
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
if ( ! function_exists( 'advanced_reviews_pro_max_review_score' ) ) {

	function advanced_reviews_pro_max_review_score( $max_score ) {
		return Advanced_Reviews_Pro_Max_Review_Score::instance( $max_score );
	}
}

