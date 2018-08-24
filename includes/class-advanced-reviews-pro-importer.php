<?php

/**
 * Review Importer
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

if ( ! class_exists( 'Advanced_Reviews_Pro_Importer' ) ) {

	class Advanced_Reviews_Pro_Importer {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * @var string $delimeter
		 * @since 1.0.0
		 */
		protected static $delimeter = ',';

		/**
		 * @var string $line
		 * @since 1.0.0
		 */
		protected $line = 1;

		/**
		 * @var array $header
		 * @since 1.0.0
		 */
		protected $header;

		/**
		 * @var array $current_row
		 * @since 1.0.0
		 */
		protected $current_row;

		/**
		 * @var array $errors
		 * @since 1.0.0
		 */
		protected $errors = array();

		/**
		 * @since    1.0.0
		 */
		public function __construct() {
		}

		/**
		 * Process import
		 *
		 * @since  1.0.0
		 */
		public function process_import() {

			check_admin_referer( ARP_PREFIX . 'process_import', ARP_PREFIX . 'process_import_nonce' );

			$file_id = arp_get_option( ARP_PREFIX . 'import_reviews_file_id', 4 );

			if ( ! $file_id ) {
				$this->errors[] = __( 'File does not exist.', 'advanced-reviews-pro' );
			} else {
				$file_path = get_attached_file( $file_id );

				$handle = fopen( $file_path, 'r' );

				if ( false !== $handle ) {

					$header = fgetcsv( $handle, 0, self::$delimeter );
					$this->header = array_map( 'self::sanitize_array', $header );

					$this->current_row = fgetcsv( $handle, 0, self::$delimeter );
					$this->import_review();
					$this->line++;

					while ( false !== $this->current_row ) {

						$this->current_row = fgetcsv( $handle, 0, self::$delimeter );

						if ( $this->current_row ) {
							$this->import_review();
						}
						$this->line++;
					}
				} else {
					$this->errors[] = __( 'File cannot be processed.', 'advanced-reviews-pro' );
				}
			}

			if ( ! empty( $this->errors ) ) {
				set_transient( get_current_user_id() . '_arp_errors', $this->errors, 300 );
			} else {
				set_transient( get_current_user_id() . '_arp_success', true, 300 );
			}

			wp_safe_redirect( admin_url( 'admin.php?page=arp_tab4_options' ) );
			die();
		}

		/**
		 * Import review from current row
		 *
		 * @since  1.0.0
		 *
		 * @return bool
		 */
		protected function import_review() {

			if ( count( $this->current_row ) !== count( $this->header ) ) {
				$this->errors[] = __( 'Wrong number of total values on line: ', 'advanced-reviews-pro' ) . $this->line;
				return false;
			}

			$product_id = $this->get_value( 'product-id' );
			$product    = wc_get_product( $product_id );

			if ( 'WC_Product_Variation' === get_class( $product ) ) {
				$product_id = $product->get_parent_id();
			}

			if ( ! $product_id ) {
				$this->errors[] = __( 'Wrong product ID on line: ', 'advanced-reviews-pro' ) . $this->line;
				return false;
			}

			$rating = $this->get_value( 'rating' );

			if ( ! ( floatval( $rating ) >= 1 && floatval( $rating ) <= 5 ) ) {
				$this->errors[] = __( 'Wrong rating on line: ', 'advanced-reviews-pro' ) . $this->line;
				return false;
			}

			$date = $this->get_value( 'date' );

			if ( empty( $date ) ) {
				$date = current_time( 'mysql' );
			} else {
				$date = date( 'Y-m-d G-i-s', strtotime( $date ) );
			}

			$total_votes = $this->get_value( 'total-votes' );

			if ( ! $total_votes ) {
				$total_votes = 0;
			}

			$images = $this->get_value( 'images' );

			if ( $images ) {
				$images = explode( ',', $images );

				foreach ( $images as $key => $image ) {
					if ( ! is_numeric( $image ) ) {
						// Download image to media lib
						$image_id = $this->upload_image( $image );
						if ( $image_id ) {
							$images[ $key ] = $image_id;
						} else {
							unset( $images[ $key ] );
						}
					}
				}
			}

			$author_id = $this->get_value( 'author-id' );

			if ( ! $author_id || ! is_numeric( $author_id ) ) {
				$author_id = 0;
			}

			$comment      = $this->get_value( 'comment' );
			$author_name  = $this->get_value( 'author-name' );
			$author_email = $this->get_value( 'author-email' );
			$author_url   = $this->get_value( 'author-url' );

			$comment_id = wp_insert_comment( apply_filters( 'arp_import_insert_comment', array(
				'comment_post_ID'      => $product_id,
				'comment_author'       => $author_name,
				'comment_author_email' => $author_email,
				'comment_author_url'   => $author_url,
				'comment_content'      => $comment,
				'comment_type'         => '',
				'comment_parent'       => 0,
				'user_id'              => $author_id,
				'comment_date'         => $date,
				'comment_approved'     => 1,
			), $this->current_row ) );

			if ( ! $comment_id ) {
				$this->errors[] = __( 'Failed to import comment on line: ', 'advanced-reviews-pro' ) . $this->line;
				return false;
			}

			update_comment_meta( $comment_id, ARP_PREFIX . 'review_images', $images );
			update_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', $total_votes );

			do_action( 'arp_after_imported_comment', $comment_id );

			return true;
		}

		/**
		 * Uploads image from url
		 *
		 * @since  1.0.0
		 *
		 * @param $url
		 *
		 * @return bool|int
		 */
		protected function upload_image( $url ) {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			$src = media_sideload_image( $url, null, null, 'src' );

			// convert the url to image id
			$image_id = attachment_url_to_postid( $src );

			if ( $image_id ) {
				do_action( 'arp_after_uploaded_image_import', $image_id );
				return $image_id;
			} else {
				$this->errors[] = __( 'File upload failed on line: ', 'advanced-reviews-pro' ) . $this->line;
				return false;
			}
		}

		/**
		 * Gets value of type from row
		 *
		 * @since  1.0.0
		 *
		 * @param $type
		 *
		 * @return bool|mixed
		 */
		protected function get_value( $type ) {

			foreach ( $this->header as $key => $item ) {
				if ( $type === $item ) {
					return $this->current_row[ $key ];
				}
			}

			return false;
		}

		/**
		 * Sanitizes array so we avoid case problems in header line
		 *
		 * @since  1.0.0
		 *
		 * @param $value
		 *
		 * @return string
		 */
		protected static function sanitize_array( $value ) {
			return sanitize_title( $value );
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
if ( ! function_exists( 'advanced_reviews_pro_importer' ) ) {

	function advanced_reviews_pro_importer() {
		return Advanced_Reviews_Pro_Importer::instance();
	}
}
