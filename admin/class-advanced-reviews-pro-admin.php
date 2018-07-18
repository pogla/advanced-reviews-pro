<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Enqueue the admin-specific stylesheet and JavaScript.
 * Register plugin options using CMB2
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/admin
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Advanced_Reviews_Pro_Admin' ) ) {

	class Advanced_Reviews_Pro_Admin {

		/**
		 * @var      object The single instance of the class
		 * @since    1.0.0
		 * @access   protected
		 */
		protected static $_instance = null;

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Prefix.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $prefix    Prefix for cmb2 fields.
		 */
		private $prefix = 'arp_';

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param    string    $plugin_name       The name of this plugin.
		 * @param    string    $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version     = $version;

		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Advanced_Reviews_Pro_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Advanced_Reviews_Pro_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/advanced-reviews-pro-admin.css', array(), $this->version, 'all' );

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Advanced_Reviews_Pro_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Advanced_Reviews_Pro_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			wp_enqueue_media();

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/advanced-reviews-pro-admin.js', array( 'jquery' ), $this->version, false );

			$comment_id = esc_attr( $_GET['c'] );
			if ( 'comment' === get_current_screen()->id && $comment_id ) {

				wp_enqueue_script( $this->plugin_name . 'max-score', plugin_dir_url( __FILE__ ) . 'js/advanced-reviews-pro-max-score.js', array( 'jquery' ), $this->version, false );
				wp_localize_script( $this->plugin_name . 'max-score', 'wp_vars', array(
					'review_score_max' => absint( arp_get_option( $this->prefix . 'max_review_score_number' ) ),
					'selected_score'   => get_comment_meta( $comment_id, 'rating', true ),
				) );
			}

		}

		/**
		 * Register the CMB2 fields for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function register_plugin_options() {

			/**
			 * TAB 1
			 *
			 * Registers options page menu item and form.
			 */
			$tab1_options = new_cmb2_box(
				array(
					'id'           => $this->prefix . 'option_metabox',
					'title'        => esc_html__( 'Advanced Reviews Pro', 'advanced-reviews-pro' ),
					'object_types' => array( 'options-page' ),
					'option_key'   => $this->prefix . 'options',
					'tab_group'    => $this->prefix . 'main_options',
					'tab_title'    => 'General Options',
					'icon_url'     => 'dashicons-star-half',
					'menu_title'   => esc_html__( 'Advanced Reviews', 'advanced-reviews-pro' ),
					'capability'   => 'manage_options',
					'position'     => 50,
					'save_button'  => esc_html__( 'Save', 'advanced-reviews-pro' ),
				)
			);

			/**
			 * SECTION: General
			 */

			$tab1_options->add_field( array(
				'name' => 'General',
				'desc' => 'General settings.',
				'type' => 'title',
				'id'   => $this->prefix . 'general_settings_title',
			) );

			$tab1_options->add_field( array(
				'name' => 'Reviews Summary',
				'desc' => 'Enable display of a histogram table with a summary of reviews on a product page.',
				'id'   => $this->prefix . 'enable_summary_checkbox',
				'type' => 'checkbox',
			) );

			$tab1_options->add_field( array(
				'name' => 'Manual Reviews',
				'desc' => 'Enable manual review generation via admin panel.',
				'id'   => $this->prefix . 'enable_manual_checkbox',
				'type' => 'checkbox',
			) );

			$tab1_options->add_field(
				array(
					'name'       => __( 'Maximum Review Score', 'advanced-reviews-pro' ),
					'desc'       => __( 'Custom maximum review score. Between 2 and 10.', 'advanced-reviews-pro' ),
					'id'         => $this->prefix . 'max_review_score_number',
					'type'       => 'text',
					'default'    => 5,
					'attributes' => array(
						'type' => 'number',
						'min'  => 2,
						'max'  => 10,
					),
				)
			);

			/**
			 * SECTION: Vote for reviews
			 */

			$tab1_options->add_field( array(
				'name' => 'Vote for Reviews',
				'desc' => 'Settings for review voting.',
				'type' => 'title',
				'id'   => $this->prefix . 'voting_title',
			) );

			$tab1_options->add_field( array(
				'name' => 'Vote for Reviews',
				'desc' => 'Enable people to upvote or downvote reviews. The plugin allows one vote per review per person. If the person is a guest, the plugin uses cookies and IP addresses to identify this visitor.',
				'id'   => $this->prefix . 'enable_votes_checkbox',
				'type' => 'checkbox',
			) );

			$tab1_options->add_field( array(
				'name'       => 'Admin Votes',
				'desc'       => 'Allow logged-in administrators to make unlimited votes.',
				'id'         => $this->prefix . 'enable_votes_admin_checkbox',
				'type'       => 'checkbox',
				'attributes' => array(
					'data-conditional-id' => $this->prefix . 'enable_votes_checkbox',
				),
			) );

			$tab1_options->add_field( array(
				'name'       => 'Sort Reviews by Votes',
				'desc'       => 'Sort product reviews by the total score of votes.',
				'id'         => $this->prefix . 'enable_votes_sorting_checkbox',
				'type'       => 'checkbox',
				'attributes' => array(
					'data-conditional-id' => $this->prefix . 'enable_votes_checkbox',
				),
			) );

			/**
			 * SECTION: Emails
			 */

			$tab1_options->add_field( array(
				'name' => 'Emails',
				'desc' => 'Configure email settings.',
				'type' => 'title',
				'id'   => $this->prefix . 'email_settings_title',
			) );

			$tab1_options->add_field(
				array(
					'name'    => __( 'Shop Name', 'advanced-reviews-pro' ),
					'desc'    => __( 'Name of your shop.', 'advanced-reviews-pro' ),
					'id'      => $this->prefix . 'shop_name_text',
					'type'    => 'text',
					'default' => get_bloginfo( 'name' ),
				)
			);

			$tab1_options->add_field(
				array(
					'name'    => __( 'From Address', 'advanced-reviews-pro' ),
					'desc'    => __( 'From email address.', 'advanced-reviews-pro' ),
					'id'      => $this->prefix . 'from_email_text',
					'type'    => 'text_email',
					'default' => get_bloginfo( 'admin_email' ),
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'BCC Addresses', 'advanced-reviews-pro' ),
					'desc' => __( 'BCC addresses. Separated by comma.', 'advanced-reviews-pro' ),
					'id'   => $this->prefix . 'bbc_email_text',
					'type' => 'text_email',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Reply-To Address', 'advanced-reviews-pro' ),
					'desc' => __( 'Reply-to address.', 'advanced-reviews-pro' ),
					'id'   => $this->prefix . 'reply_to_email_text',
					'type' => 'text_email',
				)
			);

			$tab1_options->add_field(
				array(
					'name'    => __( 'From Name', 'advanced-reviews-pro' ),
					'desc'    => __( 'From name.', 'advanced-reviews-pro' ),
					'id'      => $this->prefix . 'from_name_text',
					'type'    => 'text',
					'default' => get_bloginfo( 'name' ),
				)
			);

			/**
			 * SECTION: Emails
			 */

			$tab1_options->add_field( array(
				'name' => 'Images',
				'desc' => 'Enable attaching images to reviews left on WooCommerce product pages.',
				'type' => 'title',
				'id'   => $this->prefix . 'images_settings_title',
			) );

			$tab1_options->add_field( array(
				'name' => 'Enable',
				'desc' => 'Enable Images on Reviews',
				'id'   => $this->prefix . 'enable_images_checkbox',
				'type' => 'checkbox',
			) );

			$tab1_options->add_field(
				array(
					'name'       => __( 'Total Images', 'advanced-reviews-pro' ),
					'desc'       => __( 'Maximum amount of images to be left on a single review.', 'advanced-reviews-pro' ),
					'id'         => $this->prefix . 'total_imgs_number',
					'type'       => 'text',
					'default'    => 3,
					'attributes' => array(
						'type'                => 'number',
						'data-conditional-id' => $this->prefix . 'enable_images_checkbox',
					),
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Image Size', 'advanced-reviews-pro' ),
					'desc'       => __( 'Maximum size of image (MB).', 'advanced-reviews-pro' ),
					'id'         => $this->prefix . 'size_imgs_number',
					'type'       => 'text',
					'default'    => 5,
					'attributes' => array(
						'type'                => 'number',
						'data-conditional-id' => $this->prefix . 'enable_images_checkbox',
					),
				)
			);

			/**
			 * SECTION: reCAPTCHA V2
			 */

			$tab1_options->add_field( array(
				'name' => 'reCAPTCHA V2',
				'desc' => 'Eliminate fake and spam reviews.',
				'type' => 'title',
				'id'   => $this->prefix . 'recaptcha_settings_title',
			) );

			$tab1_options->add_field( array(
				'name' => 'Enable',
				'desc' => 'Enable reCAPTCHA',
				'id'   => $this->prefix . 'enable_recaptcha_checkbox',
				'type' => 'checkbox',
			) );

			$tab1_options->add_field(
				array(
					'name'       => __( 'reCAPTCHA V2 Site Key', 'advanced-reviews-pro' ),
					'desc'       => sprintf( 'reCAPTCHA V2 site key. %sHow to get reCAPTCHA?%s', '<a href="http://2bcoding.com/how-to-get-google-recaptcha-v2-api-keys/" target="_blank">', '</a>' ),
					'id'         => $this->prefix . 'recaptcha_site_key_text',
					'type'       => 'text',
					'attributes' => array(
						'data-conditional-id' => $this->prefix . 'enable_recaptcha_checkbox',
					),
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'reCAPTCHA V2 Secret Key', 'advanced-reviews-pro' ),
					'desc'       => sprintf( 'reCAPTCHA V2 secret key. %sHow to get reCAPTCHA?%s', '<a href="http://2bcoding.com/how-to-get-google-recaptcha-v2-api-keys/" target="_blank">', '</a>' ),
					'id'         => $this->prefix . 'recaptcha_secret_key_text',
					'type'       => 'text',
					'attributes' => array(
						'data-conditional-id' => $this->prefix . 'enable_recaptcha_checkbox',
					),
				)
			);

			/**
			 * TAB 2
			 *
			 * Registers options page menu item and form.
			 */
			$tab2_options = new_cmb2_box(
				array(
					'id'           => $this->prefix . 'option_tab2_metabox',
					'title'        => esc_html__( 'Review Reminder', 'advanced-reviews-pro' ),
					'object_types' => array( 'options-page' ),
					'option_key'   => $this->prefix . 'tab2_options',
					'parent_slug'  => $this->prefix . 'options',
					'tab_group'    => $this->prefix . 'main_options',
					'save_button'  => esc_html__( 'Save', 'advanced-reviews-pro' ),
				)
			);

			$tab2_options->add_field( array(
				'name' => 'Review Reminder',
				'desc' => 'Settings for review reminder.',
				'type' => 'title',
				'id'   => $this->prefix . 'review_reminder_settings_title',
			) );

			$tab2_options->add_field( array(
				'name' => 'Enable Automatic Review Reminders',
				'desc' => 'Enable automatic review reminders.',
				'id'   => $this->prefix . 'enable_review_reminder_checkbox',
				'type' => 'checkbox',
			) );

			$tab2_options->add_field( array(
				'name' => 'Enable Manual Review Reminders',
				'desc' => 'Enable manual sending of follow-up emails with a reminder to submit a review. Manual reminders can be sent for completed orders pages.',
				'id'   => $this->prefix . 'enable_manual_review_reminder_checkbox',
				'type' => 'checkbox',
			) );

			$tab2_options->add_field(
				array(
					'name'       => __( 'Sending Delay', 'advanced-reviews-pro' ),
					'desc'       => __( 'Choose when automatic reminder should be sent after the purchase.', 'advanced-reviews-pro' ),
					'id'         => $this->prefix . 'sending_delay_text',
					'type'       => 'text',
					'default'    => 3,
					'attributes' => array(
						'type'     => 'number',
						'required' => 'required',
					),
				)
			);

			$tab2_options->add_field(
				array(
					'name'       => __( 'Sending Delay Unit', 'advanced-reviews-pro' ),
					'desc'       => __( 'Choose the unit for sending delay.', 'advanced-reviews-pro' ),
					'id'         => $this->prefix . 'sending_delay_unit_text',
					'type'       => 'pw_select',
					'default'    => 'days',
					'options'    => array(
						'minutes' => __( 'Minutes', 'advanced-reviews-pro' ),
						'hours'   => __( 'Hours', 'advanced-reviews-pro' ),
						'days'    => __( 'Days', 'advanced-reviews-pro' ),
					),
					'attributes' => array(
						'required' => 'required',
					),
				)
			);

			$tab2_options->add_field( array(
				'name'       => __( 'Limit to Product Categories', 'advanced-reviews-pro' ),
				'desc'       => __( 'Select product categories where review reminder should apply. Leave empty to apply to all tags!', 'advanced-reviews-pro' ),
				'id'         => $this->prefix . 'sending_delay_cats_select',
				'type'       => 'pw_multiselect',
				'options'    => self::get_taxonomies_by_slug( 'product_cat' ),
				'attributes' => array(
					'placeholder' => 'Select Categories',
				),
			) );

			$tab2_options->add_field( array(
				'name'       => __( 'Limit to Product Tags', 'advanced-reviews-pro' ),
				'desc'       => __( 'Select product tags where review reminder should apply. Leave empty to apply to all tags!', 'advanced-reviews-pro' ),
				'id'         => $this->prefix . 'sending_delay_tags_select',
				'type'       => 'pw_multiselect',
				'options'    => self::get_taxonomies_by_slug( 'product_tag' ),
				'attributes' => array(
					'placeholder' => 'Select Tags',
				),
			) );

			$tab2_options->add_field( array(
				'name'       => __( 'Limit to Products', 'advanced-reviews-pro' ),
				'desc'       => __( 'Select products where review reminder should apply. Leave empty to apply to all products!', 'advanced-reviews-pro' ),
				'id'         => $this->prefix . 'sending_delay_products_select',
				'type'       => 'pw_multiselect',
				'options'    => self::get_all_products(),
				'attributes' => array(
					'placeholder' => 'Select Products',
				),
			) );

			$tab2_options->add_field( array(
				'name' => 'Email Template',
				'desc' => 'Design your email template.',
				'type' => 'title',
				'id'   => $this->prefix . 'review_reminder_template_title',
			) );

			$tab2_options->add_field( array(
				'name'    => __( 'Email Subject', 'advanced-reviews-pro' ),
				'id'      => $this->prefix . 'email_subject_text',
				'type'    => 'text',
				'default' => '[{site_title}] Review Your Experience with Us',
			) );

			$tab2_options->add_field( array(
				'name'    => __( 'Email Heading', 'advanced-reviews-pro' ),
				'id'      => $this->prefix . 'email_heading_text',
				'type'    => 'text',
				'default' => 'Thanks for your order. Please take a moment and review our products.',
			) );

			$tab2_options->add_field( array(
				'name'    => __( 'Email Body', 'advanced-reviews-pro' ),
				'desc'    => '<b>You can use these variables</b>:<br>{site_title} - Site title<br>{customer_first_name} - Billing first name<br>{customer_last_name} - Billing last name<br>{customer_full_name} - Billing full name<br>{order_id} - Order ID<br>{list_of_products} - List of products to review<br>{review_link} - Link to review all purchased products, one after another<br>{order_date} - Order date',
				'id'      => $this->prefix . 'email_body_text',
				'type'    => 'wysiwyg',
				'default' => 'Howdy {customer_full_name},<br><br>Thank you for your order #{order_id} made on {order_date}!<br><br>We would  love if you could help us by reviewing products that you recently purchased. Please follow <a href="{review_link}">this link</a> that will redirect you after each review you make. <br><br>Or you can review each purchased product separately:<br>{list_of_products}<br><br>Regards,<br>Martin',
			) );

		}

		/**
		 * Gets taxonomies by slug
		 *
		 * @since 1.0.0
		 *
		 * @param $tax
		 * @param string $query_args
		 *
		 * @return array
		 */
		private static function get_taxonomies_by_slug( $tax, $query_args = '' ) {

			$defaults = array(
				'hide_empty' => false,
			);
			$args     = wp_parse_args( $query_args, $defaults );
			$taxs     = get_terms( $tax, $args );

			$output_taxs = array();
			foreach ( $taxs as $tax ) {
				$output_taxs[ $tax->term_id ] = '#' . $tax->term_id . ' - ' . $tax->name;
			}

			return $output_taxs;
		}

		/**
		 * Gets all the products
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		private static function get_all_products() {

			$products = get_posts( array(
				'posts_per_page' => -1,
				'post_type'      => 'product',
				'post_status'    => 'any',
			) );

			$output_products = array();
			foreach ( $products as $product ) {
				$output_products[ $product->ID ] = '#' . $product->ID . ' - ' . $product->post_title;
			}

			return $output_products;
		}

		/**
		 * Class Instance
		 *
		 * @static
		 *
		 * @param $plugin_name
		 * @param $version
		 *
		 * @return object instance
		 *
		 * @since  1.0.0
		 */
		public static function instance( $plugin_name, $version ) {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $plugin_name, $version );
			}

			return self::$_instance;
		}

	}
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @param  $tab   $int     Tab
 * @return mixed           Option value
 */
function arp_get_option( $key = '', $tab = 1, $default = false ) {

	$option_key = 1 === $tab ? 'arp_options' : "arp_tab{$tab}_options";

	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( $option_key, $key, $default );
	}

	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( $option_key, $default );
	$val  = $default;

	if ( 'all' === $key ) {
		$val = $opts;
	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}

/**
 * Instance of plugin
 *
 * @return object
 * @since  1.0.0
 */
if ( ! function_exists( 'advanced_reviews_pro_admin' ) ) {

	function advanced_reviews_pro_admin( $plugin_name, $version ) {
		return Advanced_Reviews_Pro_Admin::instance( $plugin_name, $version );
	}
}
