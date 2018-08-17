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
 * Voting on WooCommerce reviews
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro_Voting' ) ) {

	class Advanced_Reviews_Pro_Voting {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Allow unlimited admin vodes.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      bool    $allow_admin
		 */
		private $allow_admin;

		/**
		 * @since    1.0.0
		 */
		public function __construct() {
			include ABSPATH . 'wp-includes/pluggable.php';
			$this->allow_admin = 'on' === arp_get_option( ARP_PREFIX . 'enable_votes_admin_checkbox' ) && current_user_can( 'administrator' );
		}

		/**
		 * Order ratings by votes
		 *
		 * @param $q
		 */
		public function parse_comment_query( $q ) {

			global $is_comment_summary;

			if ( is_product() && true !== $is_comment_summary ) {
				$q->query_vars['meta_key'] = ARP_PREFIX . 'total_votes';
				$q->query_vars['orderby']  = 'meta_value_num';
				$q->query_vars['order']    = 'DESC';
			}
		}

		/**
		 * Adds voting markup to reviews
		 *
		 * @since 1.0.0
		 *
		 * @param $comment
		 */
		public function add_voting_to_rating_html( $comment ) {

			if ( ! is_product() || 0 !== intval( $comment->comment_parent ) ) {
				return;
			}

			$product_id  = get_the_ID();
			$comment_id  = $comment->comment_ID;
			$user_voted  = $this->allow_admin ? false : $this->check_if_user_voted_on_comment( $comment_id );
			$allow_admin = $this->allow_admin;

			require plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/advanced-reviews-pro-admin-add-vote.php';
		}

		/**
		 * Check if user has already voted on $comment_id
		 *
		 * @since 1.0.0
		 *
		 * @param $comment_id
		 *
		 * @return bool
		 */
		private function check_if_user_voted_on_comment( $comment_id ) {

			$user = wp_get_current_user();

			if ( $user->exists() ) {

				$voted_comments = get_user_meta( $user->ID, ARP_PREFIX . 'voted_comments', true );
				if ( in_array( absint( $comment_id ), $voted_comments, true ) ) {
					return true;
				}
			}

			$ip        = $_SERVER['REMOTE_ADDR'];
			$voted_ips = get_comment_meta( $comment_id, ARP_PREFIX . 'voted_ips', true );

			if ( in_array( $ip, $voted_ips, true ) ) {
				return true;
			}

			if ( isset( $_COOKIE[ ARP_PREFIX . 'reviews-voted' ] ) ) {
				$voted_reviews = explode( ',', $_COOKIE[ ARP_PREFIX . 'reviews-voted' ] );
				if ( in_array( absint( $comment_id ), $voted_reviews, true ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Create a vote on rating via AJAX
		 *
		 * 1 - Cheating
		 * 2 - Already voted
		 * 3 - Registered vote successful
		 * 4 - Unregistered vote successful
		 *
		 */
		public function vote() {

			if ( ! check_ajax_referer( 'arp-public-js-nonce', 'security', false ) ) {
				wp_send_json_error( array( 'code' => 1 ) );
			}

			if ( isset( $_POST['product'] ) && isset( $_POST['comment'] ) && isset( $_POST['vote'] ) ) {

				$comment_id = intval( $_POST['comment'] );
				$vote_type  = $_POST['vote'];
				$user       = wp_get_current_user();
				$user_voted = $this->check_if_user_voted_on_comment( $comment_id );

				// If admins are allowed to vote unlimited
				if ( $this->allow_admin ) {
					$this->update_vote( $comment_id, $vote_type, false );
					wp_send_json_success( array( 'code' => 3 ) );
				}

				if ( $user_voted ) {
					wp_send_json_error( array( 'code' => 2 ) );
				}

				// Registered user vote
				if ( $user->exists() ) {
					$code = $this->reg_user_vote( $user->ID, $comment_id, $vote_type );
				} else {
					$code = $this->unreg_user_vote( $comment_id, $vote_type );
				}

				wp_send_json_success( array( 'code' => $code ) );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Registered user vote
		 *
		 * @since 1.0.0
		 *
		 * @param $user_id
		 * @param $comment_id
		 * @param $vote_type
		 *
		 * @return int
		 */
		private function reg_user_vote( $user_id, $comment_id, $vote_type ) {

			$voted_comments = get_user_meta( $user_id, ARP_PREFIX . 'voted_comments', true );

			if ( ! $voted_comments ) {
				$voted_comments = array();
				add_user_meta( $user_id, ARP_PREFIX . 'voted_comments', array( absint( $comment_id ) ) );
			}

			$voted_comments[] = absint( $comment_id );
			update_user_meta( $user_id, ARP_PREFIX . 'voted_comments', $voted_comments );

			$this->update_vote( $comment_id, $vote_type, true );

			return 3;
		}

		/**
		 * Unregistered user voting
		 *
		 * @since 1.0.0
		 *
		 * @param $comment_id
		 * @param $vote_type
		 *
		 * @return int
		 */
		private function unreg_user_vote( $comment_id, $vote_type ) {

			$ip        = $_SERVER['REMOTE_ADDR'];
			$voted_ips = get_comment_meta( $comment_id, ARP_PREFIX . 'voted_ips', true );

			if ( ! $voted_ips ) {
				$voted_ips = array();
				add_comment_meta( $comment_id, ARP_PREFIX . 'voted_ips', array( $ip ) );
			}

			// If already voted (Cookie)
			if ( isset( $_COOKIE[ ARP_PREFIX . 'reviews-voted' ] ) ) {

				// Add to cookie
				$voted_reviews[] = absint( $comment_id );
				setcookie( ARP_PREFIX . 'reviews-voted', implode( ',', $voted_reviews ), time() + ( 86400 * 365 ), '/' );
			} else {
				setcookie( ARP_PREFIX . 'reviews-voted', absint( $comment_id ), time() + ( 86400 * 365 ), '/' );
			}

			$voted_ips[] = $ip;
			update_comment_meta( $comment_id, ARP_PREFIX . 'voted_ips', $voted_ips );

			$this->update_vote( $comment_id, $vote_type, false );

			return 4;
		}

		/**
		 * Updates comment/rating with votes data
		 *
		 * @param $comment_id
		 * @param $vote_type
		 * @param $reg
		 */
		private function update_vote( $comment_id, $vote_type, $reg ) {

			$reg = $reg ? 'reg_' : 'unreg_';

			// Update total votes
			$total_votes = get_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', true );
			if ( '' === $total_votes ) {
				add_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', 'up' === $vote_type ? 1 : -1 );
			} else {
				update_comment_meta( $comment_id, ARP_PREFIX . 'total_votes', 'up' === $vote_type ? ++$total_votes : --$total_votes );
			}

			if ( 'up' === $vote_type ) {

				$comment_upvotes = get_comment_meta( $comment_id, ARP_PREFIX . $reg . 'upvotes', true );
				if ( '' === $comment_upvotes ) {
					add_comment_meta( $comment_id, ARP_PREFIX . $reg . 'upvotes', 1 );
				} else {
					update_comment_meta( $comment_id, ARP_PREFIX . $reg . 'upvotes', ++$comment_upvotes );
				}
			} elseif ( 'down' === $vote_type ) {

				$comment_downvotes = get_comment_meta( $comment_id, ARP_PREFIX . $reg . 'downvotes', true );
				if ( '' === $comment_downvotes ) {
					add_comment_meta( $comment_id, ARP_PREFIX . $reg . 'downvotes', 1 );
				} else {
					update_comment_meta( $comment_id, ARP_PREFIX . $reg . 'downvotes', ++$comment_downvotes );
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
if ( ! function_exists( 'advanced_reviews_pro_voting' ) ) {

	function advanced_reviews_pro_voting() {
		return Advanced_Reviews_Pro_Voting::instance();
	}
}
