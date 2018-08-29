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

defined( 'ABSPATH' ) || exit;

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
		 * Saves images on comment save
		 *
		 * @param $comment_id
		 * @since 1.0.0
		 */
		public function save_images_edit_comment( $comment_id ) {

			if ( ! isset( $_POST['arp-selected-imgs'] ) ) {
				return;
			}

			$selected_images = $_POST['arp-selected-imgs'];

			if ( $selected_images ) {
				update_comment_meta( $comment_id, ARP_PREFIX . 'review_images', explode( ',', $selected_images ) );
			}
		}

		/**
		 * Saves videos on comment save
		 *
		 * @param $comment_id
		 * @since 1.0.0
		 */
		public function save_videos_edit_comment( $comment_id ) {

			if ( ! isset( $_POST['arp-selected-videos'] ) ) {
				return;
			}

			$selected_videos = $_POST['arp-selected-videos'];

			if ( $selected_videos ) {
				update_comment_meta( $comment_id, ARP_PREFIX . 'review_videos', explode( ',', $selected_videos ) );
			}
		}

		/**
		 * Saves total votes on comment save
		 *
		 * @param $comment_id
		 * @since 1.0.0
		 */
		public function save_total_votes_edit_comment( $comment_id ) {

			if ( ! isset( $_POST['arp-total-votes'] ) ) {
				return;
			}

			$total_votes = $_POST['arp-total-votes'];

			if ( $total_votes ) {
				update_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', $total_votes );
			} else {
				update_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', 0 );
			}
		}

		/**
		 * Adds meta box for images on edit review screen
		 *
		 * @since 1.0.0
		 */
		public function add_images_meta_box() {

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( 'comment' === $screen_id && isset( $_GET['c'] ) && metadata_exists( 'comment', $_GET['c'], 'rating' ) ) {
				add_meta_box( 'woocommerce-review-images', __( 'Images', 'advanced-reviews-pro' ), array( $this, 'comment_images_meta_box_html' ), 'comment', 'normal', 'low' );
			}
		}

		/**
		 * Adds meta box for videos on edit review screen
		 *
		 * @since 1.0.0
		 */
		public function add_videos_meta_box() {

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( 'comment' === $screen_id && isset( $_GET['c'] ) && metadata_exists( 'comment', $_GET['c'], 'rating' ) ) {
				add_meta_box( 'woocommerce-review-videos', __( 'Videos', 'advanced-reviews-pro' ), array( $this, 'comment_videos_meta_box_html' ), 'comment', 'normal', 'low' );
			}
		}

		/**
		 * Adds meta box for videos on edit review screen
		 *
		 * @since 1.0.0
		 */
		public function add_total_votes_meta_box() {

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( 'comment' === $screen_id && isset( $_GET['c'] ) && metadata_exists( 'comment', $_GET['c'], 'rating' ) ) {
				add_meta_box( 'woocommerce-review-total-votes', __( 'Total Votes', 'advanced-reviews-pro' ), array( $this, 'comment_total_votes_meta_box_html' ), 'comment', 'normal', 'low' );
			}
		}

		/**
		 * HTML for meta box for images
		 *
		 * @since 1.0.0
		 */
		public function comment_images_meta_box_html() {

			$comment_id = $_GET['c'];

			$pics = get_comment_meta( $comment_id, ARP_PREFIX . 'review_images', true );
			$pics = $pics ? $pics : array();

			echo '<a href="javascript:" class="arp-insert-media button" data-type="image">' . __( 'Add Media', 'advanced-reviews-pro' ) . '</a><br><br>'; // WPCS: XSS ok.
			echo '<input type="hidden" name="arp-selected-imgs" id="arp-selected-imgs" value="' . implode( ',', $pics ) . '">'; // WPCS: XSS ok.

			echo '<div id="selected-images">';

			if ( $pics ) {
				foreach ( $pics as $pic ) {
					echo wp_get_attachment_image( $pic, 'shop_thumbnail' );
				}
			}

			echo '</div>';

		}

		/**
		 * HTML for meta box for videos
		 *
		 * @since 1.0.0
		 */
		public function comment_videos_meta_box_html() {

			$comment_id = $_GET['c'];

			$videos = get_comment_meta( $comment_id, ARP_PREFIX . 'review_videos', true );
			$videos = $videos ? $videos : array();

			echo '<a href="javascript:" class="arp-insert-media button" data-type="video">' . __( 'Add Media', 'advanced-reviews-pro' ) . '</a><br><br>'; // WPCS: XSS ok.
			echo '<input type="hidden" name="arp-selected-videos" id="arp-selected-videos" value="' . implode( ',', $videos ) . '">'; // WPCS: XSS ok.

			echo '<div id="selected-videos">';

			if ( $videos ) {
				foreach ( $videos as $video ) {
					echo '<video src="' . wp_get_attachment_url( $video ) . '" class="arp-video-preview"></video>'; // WPCS: XSS ok.
				}
			}

			echo '</div>';
		}

		/**
		 * HTML for meta box for total votes
		 *
		 * @since 1.0.0
		 */
		public function comment_total_votes_meta_box_html() {

			$comment_id = $_GET['c'];

			$total_votes = get_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', true );

			echo '<input type="number" name="arp-total-votes" value="' . $total_votes . '">'; // WPCS: XSS ok.
		}

		/**
		 * Add submenu page
		 *
		 * @since    1.0.0
		 */
		public function add_rating_submenu() {
			add_submenu_page( 'edit-comments.php', __( 'Add Review', 'advanced-reviews-pro' ), __( 'Add Review', 'advanced-reviews-pro' ), 'manage_options', ARP_PREFIX . 'add-custom-rating', array( $this, 'output_add_comment' ) );
		}

		/**
		 * Output add-comment screen
		 *
		 * @since    1.0.0
		 */
		public function output_add_comment() {

			$users = get_users();

			$products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'orderby'        => 'ID',
					'order'          => 'ASC',
				)
			);

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/advanced-reviews-pro-admin-add-comment.php';
		}

		/**
		 * Submits a new manual comment
		 *
		 * @since 1.0.0
		 */
		public function submit_new_comment() {

			if ( ! isset( $_POST['add_rating_nonce'] ) || ! wp_verify_nonce( $_POST['add_rating_nonce'], 'add_rating_action' ) ) {
				return;
			}

			$selected_rating = $_POST['selected-rating'];

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
			$total_votes      = $_POST['total-votes'];
			$selected_images  = $_POST['arp-selected-imgs'];
			$selected_videos  = $_POST['arp-selected-videos'];

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

				if ( $selected_videos ) {
					update_comment_meta( $review_id, ARP_PREFIX . 'review_videos', explode( ',', $selected_videos ) );
				}

				if ( $total_votes ) {
					update_comment_meta( $review_id, ARP_PREFIX . 'total_votes', $total_votes );
				} else {
					update_comment_meta( $review_id, ARP_PREFIX . 'total_votes', 0 );
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
		public function arp_get_files() {

			if ( isset( $_POST['ids'] ) ) {

				$ids = explode( ',', $_POST['ids'] );

				if ( 'image' === $_POST['type'] ) {

					$images = array();

					foreach ( $ids as $id ) {
						$images[] = wp_get_attachment_image( $id, 'shop_thumbnail' );
					}

					wp_send_json_success(
						array(
							'images' => $images,
						)
					);
				}

				if ( 'video' === $_POST['type'] ) {

					$videos = array();

					foreach ( $ids as $id ) {
						$videos[] = '<video src="' . wp_get_attachment_url( $id ) . '" class="arp-video-preview"></video>';
					}

					wp_send_json_success(
						array(
							'images' => $videos,
						)
					);
				}
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
