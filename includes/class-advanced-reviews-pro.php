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
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Advanced_Reviews_Pro' ) ) {

	class Advanced_Reviews_Pro {

		/**
		 * @var object The single instance of the class
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Advanced_Reviews_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      array    $errors
		 */
		protected static $errors = array();

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ), 8 );
		}

		public function init() {

			if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
				$this->version = PLUGIN_NAME_VERSION;
			} else {
				$this->version = '1.0.0';
			}

			$this->plugin_name = 'advanced-reviews-pro';

			$this->load_dependencies();

			// Check max review score
			Advanced_Reviews_Pro_Max_Review_Score::check_if_new_max_rating_selected();

			// Update all ratings
			Advanced_Reviews_Pro_Functions::update_comments_with_meta();

			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

		/**
		 * Check if we can activate plugin
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public static function check() {

			$passed = true;

			/* translators: 1: Plugin name */
			$inactive_text = '<strong>' . sprintf( __( '%s is inactive.', 'advanced-reviews-pro' ), ARP_NAME ) . '</strong>';

			if ( version_compare( phpversion(), ARP_MIN_PHP_VER, '<' ) ) {
				/* translators: 1: inactive text, 2: plugin name */
				self::$errors[] = sprintf( __( '%1$s The plugin requires PHP version %2$s or newer.', 'advanced-reviews-pro' ), $inactive_text, ARP_MIN_PHP_VER );
				$passed         = false;
			} elseif ( ! self::is_woocommerce_version_ok() ) {
				/* translators: 1: inactive text, 2: plugin name */
				self::$errors[] = sprintf( __( '%1$s The plugin requires WooCommerce version %2$s or newer.', 'advanced-reviews-pro' ), $inactive_text, ARP_MIN_WC_VER );
				$passed         = false;
			} elseif ( ! self::is_wp_version_ok() ) {
				/* translators: 1: inactive text, 2: plugin name */
				self::$errors[] = sprintf( __( '%1$s The plugin requires WordPress version %2$s or newer.', 'advanced-reviews-pro' ), $inactive_text, ARP_MIN_WP_VER );
				$passed         = false;
			}

			return $passed;
		}

		/**
		 * Check WC version
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		protected static function is_woocommerce_version_ok() {

			if ( ! function_exists( 'WC' ) ) {
				return false;
			};
			if ( ! ARP_MIN_WC_VER ) {
				return true;
			};
			return version_compare( WC()->version, ARP_MIN_WC_VER, '>=' );
		}


		/**
		 * Check WP version
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		protected static function is_wp_version_ok() {
			global $wp_version;
			if ( ! ARP_MIN_WP_VER ) {
				return true;
			}
			return version_compare( $wp_version, ARP_MIN_WP_VER, '>=' );
		}

		/**
		 * Admin notices
		 *
		 * @since 1.0.0
		 */
		public static function admin_notices() {
			if ( empty( self::$errors ) ) {
				return;
			};
			echo '<div class="notice notice-error"><p>';
			echo implode( '<br>', self::$errors ); // WPCS XSS ok.
			echo '</p></div>';
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Advanced_Reviews_Pro_Loader. Orchestrates the hooks of the plugin.
		 * - Advanced_Reviews_Pro_i18n. Defines internationalization functionality.
		 * - Advanced_Reviews_Pro_Admin. Defines all hooks for the admin area.
		 * - Advanced_Reviews_Pro_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-i18n.php';

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-functions.php';

			/**
			 * Include reviews class.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-recaptcha.php';

			/**
			 * Include reviews images class.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-images.php';

			/**
			 * Include reviews manual class.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-manual.php';

			/**
			 * Include max reviews score class.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-max-review-score.php';

			/**
			 * Include max reviews score class.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-voting.php';

			/**
			 * Include summary class.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-summary.php';

			/**
			 * Reminders class
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-reminders.php';

			/**
			 * Coupons class
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advanced-reviews-pro-coupons.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-advanced-reviews-pro-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-advanced-reviews-pro-public.php';

			// Initiate loader
			$this->loader = advanced_reviews_pro_loader();

		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Advanced_Reviews_Pro_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {

			$plugin_i18n = advanced_reviews_pro_i18n();
			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {

			$plugin_admin = advanced_reviews_pro_admin( $this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'cmb2_admin_init', $plugin_admin, 'register_plugin_options' );

			// Manual adding
			if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_manual_checkbox' ) ) {

				$plugin_review_manual = advanced_reviews_pro_manual();
				$this->loader->add_action( 'admin_menu', $plugin_review_manual, 'add_rating_submenu' );
				$this->loader->add_action( 'wp_ajax_arp_get_images', $plugin_review_manual, 'arp_get_images' );
				$this->loader->add_action( 'wp_loaded', $plugin_review_manual, 'submit_new_comment' );
				$this->loader->add_action( 'add_meta_boxes', $plugin_review_manual, 'add_images_meta_box' );
				$this->loader->add_action( 'edit_comment', $plugin_review_manual, 'save_images_edit_comment' );
			}
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {

			$plugin_public = advanced_reviews_pro_public( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

			// General functions
			$plugin_functions = advanced_reviews_pro_functions();
			$this->loader->add_action( 'comment_post', $plugin_functions, 'add_comment_post_meta' );

			// reCAPTCHA
			if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_recaptcha_checkbox' ) ) {

				$plugin_recaptcha = advanced_reviews_pro_recaptcha();
				$this->loader->add_filter( 'comment_form_submit_field', $plugin_recaptcha, 'output_captcha', 9 );
				$this->loader->add_action( 'set_comment_cookies', $plugin_recaptcha, 'validate_captcha', 9 );
			}

			// Images
			if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_images_checkbox' ) ) {

				$plugin_review_images = advanced_reviews_pro_images();
				$this->loader->add_action( 'woocommerce_product_review_comment_form_args', $plugin_review_images, 'review_fields_attachment' );
				$this->loader->add_filter( 'wp_insert_comment', $plugin_review_images, 'save_review_images', 10, 2 );
				$this->loader->add_filter( 'comments_array', $plugin_review_images, 'display_review_image', 12 );
			}

			// Voting
			if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_votes_checkbox' ) ) {

				$plugin_review_voting = advanced_reviews_pro_voting();
				$this->loader->add_filter( 'woocommerce_review_after_comment_text', $plugin_review_voting, 'add_voting_to_rating_html' );
				$this->loader->add_action( 'wp_ajax_arp_vote', $plugin_review_voting, 'vote' );
				$this->loader->add_action( 'wp_ajax_nopriv_arp_vote', $plugin_review_voting, 'vote' );

				// Sort by votes
				if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_votes_sorting_checkbox' ) ) {
					$this->loader->add_action( 'parse_comment_query', $plugin_review_voting, 'parse_comment_query' );
				}
			}

			// Custom review score
			$review_score_max = absint( arp_get_option( ARP_PREFIX . 'max_review_score_number' ) );
			if ( ! empty( $review_score_max ) && 5 !== $review_score_max ) {

				$plugin_max_score = advanced_reviews_pro_max_review_score( $review_score_max );
				$this->loader->add_filter( 'wp_insert_comment', $plugin_max_score, 'insert_current_review_score' );
				$this->loader->add_action( 'woocommerce_product_review_comment_form_args', $plugin_max_score, 'custom_review_stars' );
				$this->loader->add_filter( 'woocommerce_get_star_rating_html', $plugin_max_score, 'woocommerce_get_star_rating_html', 10, 3 );
				$this->loader->add_filter( 'wp_update_comment_data', $plugin_max_score, 'save_comment_admin' );
			}

			// Summary
			if ( 'on' === arp_get_option( ARP_PREFIX . 'enable_summary_checkbox' ) ) {

				if ( ! $review_score_max ) {
					$review_score_max = 5;
				}

				$plugin_review_summary = advanced_reviews_pro_summary( $review_score_max );
				$this->loader->add_filter( 'woocommerce_product_review_list_args', $plugin_review_summary, 'add_summary' );
				$this->loader->add_filter( 'query_vars', $plugin_review_summary, 'add_query_vars' );
				$this->loader->add_action( 'parse_comment_query', $plugin_review_summary, 'parse_comment_query' );
			}

			// Review Reminders
			$is_auto_reminders   = 'on' === arp_get_option( ARP_PREFIX . 'enable_review_reminder_checkbox', 2 );
			$is_manual_reminders = 'on' === arp_get_option( ARP_PREFIX . 'enable_manual_review_reminder_checkbox', 2 );

			if ( $is_auto_reminders || $is_manual_reminders ) {

				$plugin_review_reminders = advanced_reviews_pro_reminders();
				$this->loader->add_filter( 'woocommerce_email_classes', $plugin_review_reminders, 'add_review_reminder_woocommerce_email' );
				$this->loader->add_action( 'query_vars', $plugin_review_reminders, 'add_query_vars' );
				$this->loader->add_action( 'template_redirect', $plugin_review_reminders, 'handle_multiple_reviews_visit_session' );
				$this->loader->add_action( 'woocommerce_product_review_comment_form_args', $plugin_review_reminders, 'add_review_reminder_comment_notice', 99 );
				$this->loader->add_filter( 'comment_post_redirect', $plugin_review_reminders, 'redirect_after_review' );

				if ( $is_auto_reminders ) {
					$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_review_reminders, 'order_status_completed' );
					$this->loader->add_action( 'send_reminder_review_email_event', $plugin_review_reminders, 'send_reminder_review_email' );
				}

				if ( $is_manual_reminders ) {
					$this->loader->add_action( 'woocommerce_order_actions', $plugin_review_reminders, 'add_reminder_order_action' );
					$this->loader->add_action( 'woocommerce_order_action_wc_review_reminder_action', $plugin_review_reminders, 'process_reminder_order_action' );
				}
			}

			// Review for discount
			$is_review_for_discount             = 'on' === arp_get_option( ARP_PREFIX . 'enable_review_discount_checkbox', 3 );
			$is_review_for_discount_on_reminder = 'on' === arp_get_option( ARP_PREFIX . 'enable_coupon_review_reminder_checkbox', 3 );

			if ( $is_review_for_discount || ( $is_review_for_discount_on_reminder && ( $is_auto_reminders || $is_manual_reminders ) ) ) {

				$review_coupons = advanced_reviews_pro_coupons();
				$this->loader->add_filter( 'woocommerce_email_classes', $review_coupons, 'add_review_coupons_woocommerce_email' );
				$this->loader->add_filter( 'comment_post_redirect', $review_coupons, 'send_coupon_after_review', 9 );
			}
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Advanced_Reviews_Pro_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
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
if ( ! function_exists( 'advanced_reviews_pro' ) ) {

	function advanced_reviews_pro() {
		return Advanced_Reviews_Pro::instance();
	}
}
