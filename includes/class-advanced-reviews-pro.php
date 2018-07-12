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
class Advanced_Reviews_Pro {

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
	 * Prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $prefix    Prefix for cmb2 fields.
	 */
	private $prefix = 'arp_';

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
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'advanced-reviews-pro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

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
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-advanced-reviews-pro-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-advanced-reviews-pro-public.php';

		$this->loader = new Advanced_Reviews_Pro_Loader();

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

		$plugin_i18n = new Advanced_Reviews_Pro_i18n();

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

		$plugin_admin = new Advanced_Reviews_Pro_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'cmb2_admin_init', $plugin_admin, 'register_plugin_options' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Advanced_Reviews_Pro_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// reCAPTCHA
		if ( 'on' === arp_get_option( $this->prefix . 'enable_recaptcha_checkbox' ) ) {

			$plugin_recaptcha = new Advanced_Reviews_Pro_Recaptcha();
			$this->loader->add_filter( 'comment_form_submit_field', $plugin_recaptcha, 'output_captcha', 9 );
			$this->loader->add_action( 'set_comment_cookies', $plugin_recaptcha, 'validate_captcha', 9 );
		}

		// Images
		if ( 'on' === arp_get_option( $this->prefix . 'enable_images_checkbox' ) ) {

			$plugin_review_images = new Advanced_Reviews_Pro_Images();
			$this->loader->add_action( 'woocommerce_product_review_comment_form_args', $plugin_review_images, 'review_fields_attachment' );
			$this->loader->add_filter( 'wp_insert_comment', $plugin_review_images, 'save_review_images', 10, 2 );
			$this->loader->add_filter( 'comments_array', $plugin_review_images, 'display_review_image', 12 );
		}

		// Manual adding
		if ( 'on' === arp_get_option( $this->prefix . 'enable_manual_checkbox' ) ) {

			$plugin_review_manual = new Advanced_Reviews_Pro_Manual();
			$this->loader->add_action( 'admin_menu', $plugin_review_manual, 'add_rating_submenu' );
			$this->loader->add_action( 'wp_ajax_arp_get_images', $plugin_review_manual, 'arp_get_images' );
		}

		// Custom review score
		$review_score_max = absint( arp_get_option( $this->prefix . 'max_review_score_number' ) );
		if ( ! empty( $review_score_max ) && 5 !== $review_score_max ) {

			$plugin_max_score = new Advanced_Reviews_Pro_Max_Review_Score( $review_score_max );
			$this->loader->add_filter( 'wp_insert_comment', $plugin_max_score, 'insert_current_review_score' );
			$this->loader->add_action( 'woocommerce_product_review_comment_form_args', $plugin_max_score, 'custom_review_stars' );
			$this->loader->add_filter( 'woocommerce_get_star_rating_html', $plugin_max_score, 'woocommerce_get_star_rating_html', 10, 3 );
			$this->loader->add_filter( 'wp_update_comment_data', $plugin_max_score, 'save_comment_admin' );
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

}
