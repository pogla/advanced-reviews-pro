<?php

/**
 * The file that defines class for manual reviews
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Manual WooCommerce reviews
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Advanced_Reviews_Pro_Manual' ) ) {

	class Advanced_Reviews_Pro_Manual {

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
		 * Add submenu page
		 *
		 * @since    1.0.0
		 */
		public function add_rating_submenu() {
			add_submenu_page( 'edit-comments.php', 'Add Rating', 'Add rating', 'manage_options', ARP_PREFIX . 'add-custom-rating', array( $this, 'output_add_comment' ) );
		}

		/**
		 * Output add-comment screen
		 *
		 * @since    1.0.0
		 */
		public function output_add_comment() {

			$users            = get_users();
			$review_score_max = absint( arp_get_option( ARP_PREFIX . 'max_review_score_number' ) );
			if ( ! $review_score_max ) {
				$review_score_max = 5;
			}

			$products = get_posts( array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			) );

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/advanced-reviews-pro-admin-add-comment.php';
		}

		/**
		 * Submits a new comment
		 */
		public function submit_new_comment() {

			if ( ! isset( $_POST['add_rating_nonce'] ) || ! wp_verify_nonce( $_POST['add_rating_nonce'], 'add_rating_action' ) ) {
				return;
			}

			$review_score_max = absint( arp_get_option( ARP_PREFIX . 'max_review_score_number' ) );
			if ( ! $review_score_max ) {
				$review_score_max = 5;
			}

			// Transform to 1-5 rating system
			$selected_rating = ( $_POST['selected-rating'] / $review_score_max ) * 5;

			$selected_user = $_POST['selected-user'];
			if ( 'guest' === $selected_user ) {
				$author_name  = $_POST['author-name'];
				$author_email = $_POST['author-email'];
				$author_url   = $_POST['newcomment_author_url'];
			} else {
				$user         = get_user_by( 'id', $selected_user );
				$author_name  = $user->first_name . ' ' . $user->last_name;
				$author_email = $user->user_email;
				$author_url   = $user->user_url;
				if ( empty( $author_name ) ) {
					$author_name = $user->display_name;
				}
			}

			$comment_content  = $_POST['comment-content'];
			$selected_product = $_POST['selected-product'];
			$selected_date    = $_POST['comment_date'];
			$selected_images  = $_POST['arp-selected-imgs'];

			if ( ! $selected_date ) {
				$selected_date     = date( 'Y-m-d G-i-s' );
				$selected_date_gmt = new DateTime( 'now' );
			} else {
				$selected_date_gmt = new DateTime( $selected_date );
			}

			$time_zone_off = get_option( 'gmt_offset' );
			$selected_date_gmt->modify( "-{$time_zone_off} hours" );

			$comment_data = array(
				'comment_author'       => $author_name,
				'comment_author_email' => $author_email,
				'comment_author_url'   => $author_url,
				'comment_content'      => $comment_content,
				'comment_post_ID'      => $selected_product,
				'comment_type'         => '',
				'comment_approved'     => 1,
				'user_id'              => 'guest' === $selected_user ? '' : $selected_user,
				'comment_date'         => $selected_date,
				'comment_date_gmt'     => date( 'Y-m-d G-i-s', $selected_date_gmt->getTimestamp() ),
			);

			$review_id = wp_insert_comment( $comment_data );

			if ( $review_id ) {

				update_comment_meta( $review_id, 'rating', $selected_rating );
				update_comment_meta( $review_id, 'verified', 0 );

				if ( $selected_images ) {
					update_comment_meta( $review_id, ARP_PREFIX . 'review_images', explode( ',', $selected_images ) );
				}

				$_POST['arp-added-comment'] = true;
				$_POST['arp-review-id']     = $review_id;

			} else {
				$_POST['arp-added-comment-error'] = true;
			}
		}

		/**
		 * AJAX call, returns images from id-s
		 *
		 * @since 1.0.0
		 */
		public function arp_get_images() {

			if( isset( $_POST['ids'] ) ){

				$ids    = explode( ',', $_POST['ids'] );
				$images = array();

				foreach ( $ids as $id ) {
					$images[] = wp_get_attachment_image( $id, 'shop_thumbnail' );
				}

				wp_send_json_success( array(
					'images' => $images,
				) );
			} else {
				wp_send_json_error();
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
if ( ! function_exists( 'advanced_reviews_pro_manual' ) ) {

	function advanced_reviews_pro_manual() {
		return Advanced_Reviews_Pro_Manual::instance();
	}
}
