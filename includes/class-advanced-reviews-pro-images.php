<?php

/**
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 */

/**
 * Recaptcha on WooCommerce reviews
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro_Images' ) ) {

	class Advanced_Reviews_Pro_Images {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Total images allowed.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $total_images_allowed
		 */
		private $total_images_allowed;

		/**
		 * Max image size.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $max_image_size
		 */
		private $max_image_size;

		/**
		 * Allowed file types.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array    $allowed_types
		 */
		private $allowed_types;

		/**
		 * @since    1.0.0
		 */
		public function __construct() {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			$this->total_images_allowed = arp_get_option( ARP_PREFIX . 'total_imgs_number' );
			$this->max_image_size       = arp_get_option( ARP_PREFIX . 'size_imgs_number' );
			$this->allowed_types        = apply_filters( 'arp_allowed_file_types', array( 'png', 'gif', 'jpg', 'jpeg' ) );

			if ( ! $this->total_images_allowed ) {
				$this->total_images_allowed = 3;
			}
			if ( ! $this->max_image_size ) {
				$this->max_image_size = 2;
			}
		}

		/**
		 * @param $comment_form
		 * @since    1.0.0
		 *
		 * @return mixed
		 */
		public function review_fields_attachment( $comment_form ) {

			// Return if not product
			if ( ! is_product() ) {
				return $comment_form;
			}

			$post_id = get_the_ID();

			$comment_form['comment_field'] .= '<p><label for="comment_image_' . $post_id . '">';
			/* translators: %1$d: number of images, %2$s: max images to upload, %3$s: file extensions */
			$comment_form['comment_field'] .= sprintf( __( 'Upload up to %1$d images for your review <br><span style="font-size: 0.8rem;opacity: .8;font-weight: normal;">(Allowed image size is %2$s MB. Allowed image types are: %3$s.)</span>', 'advanced-reviews-pro' ), $this->total_images_allowed, $this->max_image_size, implode( ', ', $this->allowed_types ) );
			$comment_form['comment_field'] .= '</label><input type="file" multiple="multiple" name="review_image_' . $post_id . '[]" id="review_image" />';
			$comment_form['comment_field'] .= '</p>';

			return $comment_form;
		}

		/**
		 * Handles media uploads
		 *
		 * @param $comment_id
		 * @param $comment
		 *
		 * @since    1.0.0
		 */
		public function save_review_images( $comment_id, $comment ) {

			$product_id = get_comment( $comment_id )->comment_post_ID;
			$product    = wc_get_product( $product_id );

			// Return if not product review
			if ( ! is_a( $product, 'WC_Product' ) || 0 !== intval( $comment->comment_parent ) ) {
				return;
			}

			$post_id          = $comment->comment_post_ID;
			$comment_image_id = 'review_image_' . $post_id;

			if ( isset( $_FILES[ $comment_image_id ] ) && is_array( $_FILES[ $comment_image_id ]['name'] ) ) {
				$files_count = count( $_FILES[ $comment_image_id ]['name'] );

				if ( ! $_FILES[ $comment_image_id ]['size'][0] ) {
					return;
				}

				// Delete comment if too many files
				if ( $files_count > $this->total_images_allowed ) {
					$this->error_comment_files( $comment_id, 'Too many images!' );
				}

				for ( $i = 0; $i < $files_count; $i++ ) {

					if ( ( 1048576 * $this->max_image_size ) < $_FILES[ $comment_image_id ]['size'][ $i ] ) {
						$this->error_comment_files( $comment_id, __( 'File size is too large!', 'advanced-reviews-pro' ) );
					}

					$file_name_parts = explode( '.', $_FILES[ $comment_image_id ]['name'][ $i ] );
					$file_ext        = $file_name_parts[ count( $file_name_parts ) - 1 ];

					if ( ! in_array( strtolower( trim( $file_ext ) ), $this->allowed_types, true ) ) {
						$this->error_comment_files( $comment_id, __( 'Wrong file extension!', 'advanced-reviews-pro' ) );
					}
				}

				$images = array();
				for ( $i = 0; $i < $files_count; $i++ ) {

					$comment_image_file = wp_upload_bits( $comment_id . '-' . $i . '-' . $_FILES[ $comment_image_id ]['name'][ $i ], '', file_get_contents( $_FILES[ $comment_image_id ]['tmp_name'][ $i ] ) );
					$attachment_id      = media_sideload_image( $comment_image_file['url'], $post_id, null, 'id' );
					if ( ! is_wp_error( $attachment_id ) ) {
						$images[] = $attachment_id;
					} else {
						$this->error_comment_files( $comment_id, __( 'Error uploading file.', 'advanced-reviews-pro' ) );
					}
				}

				add_comment_meta( $comment_id, ARP_PREFIX . 'review_images', $images );

			}
		}

		/**
		 * Displays review images
		 *
		 * @since 1.0.0
		 * @param $comments
		 *
		 * @return mixed
		 */
		public function display_review_image( $comments ) {

			// Return if not product review
			if ( ! is_product() ) {
				return $comments;
			}

			if ( count( $comments ) > 0 ) {

				foreach ( $comments as $comment ) {

					// Only product reviews
					if ( 0 !== intval( $comment->comment_parent ) ) {
						continue;
					}

					$pics       = get_comment_meta( $comment->comment_ID, ARP_PREFIX . 'review_images', true );
					$total_pics = count( $pics );

					if ( $pics && $total_pics > 0 ) {

						$comment->comment_content .= '<p class="arv-comment-image-text">' . ( 1 === $total_pics ? __( 'Uploaded image:', 'advanced-reviews-pro' ) : __( 'Uploaded images:', 'advanced-reviews-pro' ) ) . '</p>';
						$comment->comment_content .= '<div class="arv-comment-images">';
						for ( $i = 0; $i < $total_pics; $i++ ) {
							$img_meta                  = wp_get_attachment_metadata( $pics[ $i ] );
							$full_img_src              = wp_get_attachment_url( $pics[ $i ] );
							$shop_img                  = wp_get_attachment_image_src( $pics[ $i ] );
							$comment->comment_content .= '<div class="arv-comment-image">';
							/* translators: #%1$d: image name */
							$comment->comment_content .= '<img data-natural-width="' . $img_meta['width'] . '" data-natural-height="' . $img_meta['height'] . '" data-full-src="' . $full_img_src . '" src="' . $shop_img[0] . '" alt="' . sprintf( __( 'Image #%1$d from ', 'advanced-reviews-pro' ), $i + 1 ) . $comment->comment_author . '">';
							$comment->comment_content .= '</div>';
						}
						$comment->comment_content .= '<div style="clear:both;"></div></div>';

						$comment->comment_content = apply_filters( 'arp_comment_images_display', $comment->comment_content, $comment );
					}
				}
			}
			return $comments;
		}

		/**
		 * Triggers error with message and removes the comment
		 *
		 * @param $comment_id
		 * @param $error_msg
		 *
		 * @since    1.0.0
		 */
		private function error_comment_files( $comment_id, $error_msg ) {
			wp_delete_comment( $comment_id, true );
			wp_die( esc_attr( $error_msg ), __( 'Comment Submission Failure', 'advanced-reviews-pro' ), array( 'back_link' => true ) ); // WPCS XSS ok.
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
if ( ! function_exists( 'advanced_reviews_pro_images' ) ) {

	function advanced_reviews_pro_images() {
		return Advanced_Reviews_Pro_Images::instance();
	}
}
