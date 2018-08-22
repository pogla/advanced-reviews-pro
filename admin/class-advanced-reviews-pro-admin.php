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

defined( 'ABSPATH' ) || exit;

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
		 * Show action links on the plugin screen.
		 *
		 * @since   1.0.0
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		public static function plugin_action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=arp_options' ) . '" aria-label="' . esc_attr__( 'View Advanced Reviews Pro Settings', 'advanced-reviews-pro' ) . '">' . esc_html__( 'Settings', 'advanced-reviews-pro' ) . '</a>',
			);

			return array_merge( $action_links, $links );
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

			$comment_id = isset( $_GET['c'] ) ? esc_attr( $_GET['c'] ) : false;

			if ( 'comment' === get_current_screen()->id && $comment_id ) {

				wp_enqueue_script( $this->plugin_name . 'max-score', plugin_dir_url( __FILE__ ) . 'js/advanced-reviews-pro-max-score.js', array( 'jquery' ), $this->version, false );
				wp_localize_script(
					$this->plugin_name . 'max-score', 'wp_vars', array(
						'review_score_max' => absint( arp_get_option( ARP_PREFIX . 'max_review_score_number' ) ),
						'selected_score'   => get_comment_meta( $comment_id, 'rating', true ),
					)
				);
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
					'id'           => ARP_PREFIX . 'option_metabox',
					'title'        => __( 'Advanced Reviews Pro', 'advanced-reviews-pro' ),
					'object_types' => array( 'options-page' ),
					'option_key'   => ARP_PREFIX . 'options',
					'tab_group'    => ARP_PREFIX . 'main_options',
					'tab_title'    => __( 'General Options', 'advanced-reviews-pro' ),
					'icon_url'     => 'dashicons-star-half',
					'menu_title'   => __( 'Advanced Reviews', 'advanced-reviews-pro' ),
					'capability'   => 'manage_options',
					'position'     => 50,
					'save_button'  => __( 'Save', 'advanced-reviews-pro' ),
				)
			);

			/**
			 * SECTION: General
			 */

			$tab1_options->add_field(
				array(
					'name' => __( 'General', 'advanced-reviews-pro' ),
					'desc' => __( 'General settings.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'general_settings_title',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Reviews Summary', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable display of a histogram table with a summary of reviews on a product page.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_summary_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Manual Reviews', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable manual review generation via admin panel.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_manual_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Maximum Review Score', 'advanced-reviews-pro' ),
					'desc'       => __( 'Custom maximum review score. Between 2 and 10.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'max_review_score_number',
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

			$tab1_options->add_field(
				array(
					'name' => __( 'Vote for Reviews', 'advanced-reviews-pro' ),
					'desc' => __( 'Settings for review voting.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'voting_title',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Vote for Reviews', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable people to upvote or downvote reviews. The plugin allows one vote per review per person. If the person is a guest, the plugin uses cookies and IP addresses to identify this visitor.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_votes_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Admin Votes', 'advanced-reviews-pro' ),
					'desc'       => __( 'Allow logged-in administrators to make unlimited votes.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'enable_votes_admin_checkbox',
					'type'       => 'checkbox',
					'attributes' => array(
						'data-conditional-id' => ARP_PREFIX . 'enable_votes_checkbox',
					),
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Sort Reviews by Votes', 'advanced-reviews-pro' ),
					'desc'       => __( 'Sort product reviews by the total score of votes.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'enable_votes_sorting_checkbox',
					'type'       => 'checkbox',
					'attributes' => array(
						'data-conditional-id' => ARP_PREFIX . 'enable_votes_checkbox',
					),
				)
			);

			/**
			 * SECTION: Emails
			 */

			$tab1_options->add_field(
				array(
					'name' => __( 'Emails', 'advanced-reviews-pro' ),
					'desc' => __( 'Configure email settings.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'email_settings_title',
				)
			);

			$tab1_options->add_field(
				array(
					'name'    => __( 'Shop Name', 'advanced-reviews-pro' ),
					'desc'    => __( 'Name of your shop.', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'shop_name_text',
					'type'    => 'text',
					'default' => get_bloginfo( 'name' ),
				)
			);

			$tab1_options->add_field(
				array(
					'name'    => __( 'From Name', 'advanced-reviews-pro' ),
					'desc'    => __( 'From name.', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'from_name_text',
					'type'    => 'text',
					'default' => get_bloginfo( 'name' ),
				)
			);

			$tab1_options->add_field(
				array(
					'name'    => __( 'From Address', 'advanced-reviews-pro' ),
					'desc'    => __( 'From email address.', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'from_email_text',
					'type'    => 'text_email',
					'default' => get_bloginfo( 'admin_email' ),
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Reply-To Name', 'advanced-reviews-pro' ),
					'desc' => __( 'Reply-To Name.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'reply_to_name_text',
					'type' => 'text',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Reply-To Address', 'advanced-reviews-pro' ),
					'desc' => __( 'Reply-to address.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'reply_to_email_text',
					'type' => 'text_email',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'BCC Addresses', 'advanced-reviews-pro' ),
					'desc' => __( 'BCC addresses. Separated by comma.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'bbc_email_text',
					'type' => 'text_email',
				)
			);

			$tab1_options->add_field(
				array(
					'name'    => __( 'Limit emails', 'advanced-reviews-pro' ),
					'desc'    => __( 'Limit emails per user', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'limit_emails_per_user_checkbox',
					'default' => 'on',
					'type'    => 'checkbox',
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Days between emails', 'advanced-reviews-pro' ),
					'desc'       => __( 'Minimum days between emails for user/email. 0 means no limit.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'emails_limit_text',
					'type'       => 'text',
					'default'    => 7,
					'attributes' => array(
						'type'                => 'number',
						'data-conditional-id' => ARP_PREFIX . 'limit_emails_per_user_checkbox',
					),
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Unlimited emails for Review for Discount', 'advanced-reviews-pro' ),
					'desc'       => __( 'Force unlimited emails for "Review for Discount" feature', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'force_unlimited_review_emails_checkbox',
					'type'       => 'checkbox',
					'default'    => 'on',
					'attributes' => array(
						'data-conditional-id' => ARP_PREFIX . 'limit_emails_per_user_checkbox',
					),
				)
			);

			/**
			 * SECTION: Images
			 */

			$tab1_options->add_field(
				array(
					'name' => __( 'Images', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable attaching images to reviews left on WooCommerce product pages.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'images_settings_title',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Enable', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable Images on Reviews', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_images_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Total Images', 'advanced-reviews-pro' ),
					'desc'       => __( 'Maximum amount of images to be left on a single review.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'total_imgs_number',
					'type'       => 'text',
					'default'    => 3,
					'attributes' => array(
						'type'                => 'number',
						'data-conditional-id' => ARP_PREFIX . 'enable_images_checkbox',
					),
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'Image Size', 'advanced-reviews-pro' ),
					'desc'       => __( 'Maximum size of image (MB).', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'size_imgs_number',
					'type'       => 'text',
					'default'    => 5,
					'attributes' => array(
						'type'                => 'number',
						'data-conditional-id' => ARP_PREFIX . 'enable_images_checkbox',
					),
				)
			);

			/**
			 * SECTION: reCAPTCHA V2
			 */

			$tab1_options->add_field(
				array(
					'name' => __( 'reCAPTCHA V2', 'advanced-reviews-pro' ),
					'desc' => __( 'Eliminate fake and spam reviews.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'recaptcha_settings_title',
				)
			);

			$tab1_options->add_field(
				array(
					'name' => __( 'Enable', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable reCAPTCHA', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_recaptcha_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'reCAPTCHA V2 Site Key', 'advanced-reviews-pro' ),
					/* translators: 1: link start, 2: link end */
					'desc'       => sprintf( __( 'reCAPTCHA V2 site key. %1$sHow to get reCAPTCHA?%2$s', 'advanced-reviews-pro' ), '<a href="http://2bcoding.com/how-to-get-google-recaptcha-v2-api-keys/" target="_blank">', '</a>' ),
					'id'         => ARP_PREFIX . 'recaptcha_site_key_text',
					'type'       => 'text',
					'attributes' => array(
						'data-conditional-id' => ARP_PREFIX . 'enable_recaptcha_checkbox',
					),
				)
			);

			$tab1_options->add_field(
				array(
					'name'       => __( 'reCAPTCHA V2 Secret Key', 'advanced-reviews-pro' ),
					/* translators: 1: link start, 2: link end */
					'desc'       => sprintf( __( 'reCAPTCHA V2 secret key. %1$sHow to get reCAPTCHA?%2$s', 'advanced-reviews-pro' ), '<a href="http://2bcoding.com/how-to-get-google-recaptcha-v2-api-keys/" target="_blank">', '</a>' ),
					'id'         => ARP_PREFIX . 'recaptcha_secret_key_text',
					'type'       => 'text',
					'attributes' => array(
						'data-conditional-id' => ARP_PREFIX . 'enable_recaptcha_checkbox',
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
					'id'           => ARP_PREFIX . 'option_tab2_metabox',
					'title'        => __( 'Review Reminder', 'advanced-reviews-pro' ),
					'object_types' => array( 'options-page' ),
					'option_key'   => ARP_PREFIX . 'tab2_options',
					'parent_slug'  => ARP_PREFIX . 'options',
					'tab_group'    => ARP_PREFIX . 'main_options',
					'save_button'  => __( 'Save', 'advanced-reviews-pro' ),
				)
			);

			$tab2_options->add_field(
				array(
					'name' => __( 'Review Reminder', 'advanced-reviews-pro' ),
					'desc' => __( 'Settings for review reminder.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'review_reminder_settings_title',
				)
			);

			$tab2_options->add_field(
				array(
					'name' => __( 'Enable Automatic Review Reminders', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable automatic review reminders.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_review_reminder_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab2_options->add_field(
				array(
					'name' => __( 'Enable Manual Review Reminders', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable manual sending of follow-up emails with a reminder to submit a review. Manual reminders can be sent for completed orders pages.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_manual_review_reminder_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab2_options->add_field(
				array(
					'name'       => __( 'Sending Delay', 'advanced-reviews-pro' ),
					'desc'       => __( 'Choose when automatic reminder should be sent after the purchase.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'sending_delay_text',
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
					'id'         => ARP_PREFIX . 'sending_delay_unit_text',
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

			$tab2_options->add_field(
				array(
					'name'       => __( 'Limit to Product Categories', 'advanced-reviews-pro' ),
					'desc'       => __( 'Select product categories where review reminder should apply. Leave empty to apply to all tags!', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'sending_delay_cats_select',
					'type'       => 'pw_multiselect',
					'options'    => self::get_taxonomies_by_slug( 'product_cat' ),
					'attributes' => array(
						'placeholder' => __( 'Select Categories', 'advanced-reviews-pro' ),
					),
				)
			);

			$tab2_options->add_field(
				array(
					'name'       => __( 'Limit to Product Tags', 'advanced-reviews-pro' ),
					'desc'       => __( 'Select product tags where review reminder should apply. Leave empty to apply to all tags!', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'sending_delay_tags_select',
					'type'       => 'pw_multiselect',
					'options'    => self::get_taxonomies_by_slug( 'product_tag' ),
					'attributes' => array(
						'placeholder' => __( 'Select Tags', 'advanced-reviews-pro' ),
					),
				)
			);

			$tab2_options->add_field(
				array(
					'name'       => __( 'Limit to Products', 'advanced-reviews-pro' ),
					'desc'       => __( 'Select products where review reminder should apply. Leave empty to apply to all products!', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'sending_delay_products_select',
					'type'       => 'pw_multiselect',
					'options'    => self::get_all_posts_by_type( 'product' ),
					'attributes' => array(
						'placeholder' => __( 'Select Products', 'advanced-reviews-pro' ),
					),
				)
			);

			$tab2_options->add_field(
				array(
					'name' => __( 'Email Template', 'advanced-reviews-pro' ),
					'desc' => __( 'Design your email template.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'review_reminder_template_title',
				)
			);

			$tab2_options->add_field(
				array(
					'name'    => __( 'Email Subject', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'email_subject_text',
					'type'    => 'text',
					'default' => '[{site_title}] ' . __( 'Review Your Experience with Us', 'advanced-reviews-pro' ),
				)
			);

			$tab2_options->add_field(
				array(
					'name'    => __( 'Email Heading', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'email_heading_text',
					'type'    => 'text',
					'default' => __( 'Thanks for your order. Please take a moment and review our products.', 'advanced-reviews-pro' ),
				)
			);

			$tab2_options->add_field(
				array(
					'name'    => __( 'Email Body', 'advanced-reviews-pro' ),
					'desc'    => __( '<b>You can use these variables</b>:<br>{site_title} - Site title<br>{customer_first_name} - Billing first name<br>{customer_last_name} - Billing last name<br>{customer_full_name} - Billing full name<br>{order_id} - Order ID<br>{list_of_products} - List of products to review<br>{review_link} - Link to review all purchased products, one after another<br>{order_date} - Order date', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'email_body_text',
					'type'    => 'wysiwyg',
					'default' => 'Howdy {customer_full_name},<br><br>Thank you for your order #{order_id} made on {order_date}!<br><br>We would love if you could help us by reviewing products that you recently purchased. Please follow <a href="{review_link}">this link</a> that will redirect you after each review you make. <br><br>Or you can review each purchased product separately:<br>{list_of_products}<br><br>Regards,<br>Martin',
				)
			);

			/**
			 * TAB 3
			 *
			 * Registers options page menu item and form.
			 */
			$tab3_options = new_cmb2_box(
				array(
					'id'           => ARP_PREFIX . 'option_tab3_metabox',
					'title'        => __( 'Coupon for Review', 'advanced-reviews-pro' ),
					'object_types' => array( 'options-page' ),
					'option_key'   => ARP_PREFIX . 'tab3_options',
					'parent_slug'  => ARP_PREFIX . 'options',
					'tab_group'    => ARP_PREFIX . 'main_options',
					'save_button'  => __( 'Save', 'advanced-reviews-pro' ),
				)
			);

			$tab3_options->add_field(
				array(
					'name' => __( 'Review for Discount', 'advanced-reviews-pro' ),
					'desc' => __( 'Settings for review for discount.', 'advanced-reviews-pro' ),
					'type' => 'title',
					'id'   => ARP_PREFIX . 'review_discount_settings_title',
				)
			);

			$tab3_options->add_field(
				array(
					'name' => __( 'Enable Review for Discount', 'advanced-reviews-pro' ),
					'desc' => __( 'Enable generation of discount coupons for customers who provide reviews.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_review_discount_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab3_options->add_field(
				array(
					'name' => __( 'Enable on Review Reminder', 'advanced-reviews-pro' ),
					'desc' => __( 'Send email when user finishes with reviewing their order. User gets redirected after each review and on the last review triggers this email. Only works with pre-generated link {review_link}.', 'advanced-reviews-pro' ),
					'id'   => ARP_PREFIX . 'enable_coupon_review_reminder_checkbox',
					'type' => 'checkbox',
				)
			);

			$tab3_options->add_field(
				array(
					'name'    => __( 'Review Coupons Settings', 'advanced-reviews-pro' ),
					'desc'    => __( 'Settings for review coupons.', 'advanced-reviews-pro' ),
					'type'    => 'title',
					'id'      => ARP_PREFIX . 'review_discount_coupon_settings_title',
					'classes' => ARP_PREFIX . 'tab3_hide',
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Coupon Type', 'advanced-reviews-pro' ),
					'desc'       => __( 'Select product categories where review reminder should apply. Leave empty to apply to all tags!', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'coupon_type_select',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'pw_select',
					'default'    => 'generate_coupon',
					'options'    => array(
						'generate_coupon' => __( 'Generate Unique Coupon', 'advanced-reviews-pro' ),
						'existing_coupon' => __( 'Existing Coupon', 'advanced-reviews-pro' ),
					),
					'attributes' => array(
						'placeholder' => __( 'Select Categories', 'advanced-reviews-pro' ),
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Select Coupon', 'advanced-reviews-pro' ),
					'desc'       => __( 'This coupon code will be sent to customers who provide reviews.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'existing_coupon_select',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'pw_select',
					'options'    => self::get_all_posts_by_type( 'shop_coupon' ),
					'attributes' => array(
						'placeholder'            => __( 'Select Coupon', 'advanced-reviews-pro' ),
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'existing_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Discount Type', 'advanced-reviews-pro' ),
					'desc'       => __( 'Select a discount type.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_discount_type_select',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'pw_select',
					'options'    => array(
						'percent'       => __( 'Percentage discount', 'advanced-reviews-pro' ),
						'fixed_cart'    => __( 'Fixed cart discount', 'advanced-reviews-pro' ),
						'fixed_product' => __( 'Fixed product discount', 'advanced-reviews-pro' ),
					),
					'attributes' => array(
						'required'               => 'required',
						'placeholder'            => __( 'Select Discount Type', 'advanced-reviews-pro' ),
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Coupon Amount', 'advanced-reviews-pro' ),
					'desc'       => __( 'Coupon amount.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_amount_text',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'text',
					'attributes' => array(
						'type'                   => 'number',
						'required'               => 'required',
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Allow free shipping', 'advanced-reviews-pro' ),
					'desc'       => __( 'Check this box if the coupon grants free shipping. A <a href="https://docs.woocommerce.com/document/free-shipping/">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_allow_free_shipping_checkbox',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'checkbox',
					'attributes' => array(
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Validity', 'advanced-reviews-pro' ),
					'desc'       => __( 'How many days should the coupon be valid.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_validity_text',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'text',
					'attributes' => array(
						'type'                   => 'number',
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Minimum spend', 'advanced-reviews-pro' ),
					'desc'       => __( 'Minimum spend for the generated coupon.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_min_text',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'text',
					'attributes' => array(
						'type'                   => 'number',
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Maximum spend', 'advanced-reviews-pro' ),
					'desc'       => __( 'Maximum spend for the generated coupon.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_max_text',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'text',
					'attributes' => array(
						'type'                   => 'number',
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Individual Use Only', 'advanced-reviews-pro' ),
					'desc'       => __( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_individual_use_only_checkbox',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'checkbox',
					'attributes' => array(
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Exclude sale items', 'advanced-reviews-pro' ),
					'desc'       => __( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_exclude_sale_items_checkbox',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'checkbox',
					'attributes' => array(
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Products', 'advanced-reviews-pro' ),
					'desc'       => __( 'Products that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_products_select',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'pw_multiselect',
					'options'    => self::get_all_posts_by_type( 'product' ),
					'attributes' => array(
						'placeholder'            => __( 'Search for a product', 'advanced-reviews-pro' ),
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Exclude Products', 'advanced-reviews-pro' ),
					'desc'       => __( 'Products that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_exclude_products_select',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'pw_multiselect',
					'options'    => self::get_all_posts_by_type( 'product' ),
					'attributes' => array(
						'placeholder'            => __( 'Search for a product', 'advanced-reviews-pro' ),
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Product Categories', 'advanced-reviews-pro' ),
					'desc'       => __( 'Product categories that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_product_categories_select',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'pw_multiselect',
					'options'    => self::get_taxonomies_by_slug( 'product_cat' ),
					'attributes' => array(
						'placeholder'            => __( 'Any Category', 'advanced-reviews-pro' ),
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Exclude Categories', 'advanced-reviews-pro' ),
					'desc'       => __( 'Product categories that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_exclude_product_categories_select',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'pw_multiselect',
					'options'    => self::get_taxonomies_by_slug( 'product_cat' ),
					'attributes' => array(
						'placeholder'            => __( 'No Categories', 'advanced-reviews-pro' ),
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Allowed emails', 'advanced-reviews-pro' ),
					'desc'       => __( 'Whitelist of billing emails to check against when an order is placed. Separate email addresses with commas. You can also use an asterisk (*) to match parts of an email. For example "*@gmail.com" would match all gmail addresses. <b>Use {BUYER_EMAIL} to restrict coupon to email receiver.</b>', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_email_restrict_text',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'text',
					'default'    => __( '{BUYER_EMAIL}', 'advanced-reviews-pro' ),
					'attributes' => array(
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Usage limit per coupon', 'advanced-reviews-pro' ),
					'desc'       => __( 'How many times this coupon can be used before it is void.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_usage_restrict_text',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'text',
					'attributes' => array(
						'type'                   => 'number',
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'       => __( 'Coupon Format', 'advanced-reviews-pro' ),
					'desc'       => __( 'Format of generated coupon. Use {PREFIX}, {SUFFIX}, {RANDOM-X}. Replace X with a number. X is the number of random characters.', 'advanced-reviews-pro' ),
					'id'         => ARP_PREFIX . 'generate_coupon_format_text',
					'classes'    => ARP_PREFIX . 'tab3_hide',
					'type'       => 'text',
					'default'    => '{PREFIX}{RANDOM-10}{SUFFIX}',
					'attributes' => array(
						'data-conditional-id'    => ARP_PREFIX . 'coupon_type_select',
						'data-conditional-value' => 'generate_coupon',
					),
				)
			);

			$tab3_options->add_field(
				array(
					'name'    => __( 'Email Template', 'advanced-reviews-pro' ),
					'desc'    => __( 'Design your email template. Email is triggered after a customer leaves a review. Only works if the customer is logged in the store.', 'advanced-reviews-pro' ),
					'type'    => 'title',
					'id'      => ARP_PREFIX . 'review_coupon_template_title',
					'classes' => ARP_PREFIX . 'tab3_hide',
				)
			);

			$tab3_options->add_field(
				array(
					'name'    => __( 'Email Subject', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'review_coupon_email_subject_text',
					'classes' => ARP_PREFIX . 'tab3_hide',
					'type'    => 'text',
					'default' => '[{site_title}] ' . __( 'Thanks for the review', 'advanced-reviews-pro' ),
				)
			);

			$tab3_options->add_field(
				array(
					'name'    => __( 'Email Heading', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'review_coupon_email_heading_text',
					'classes' => ARP_PREFIX . 'tab3_hide',
					'type'    => 'text',
					'default' => __( 'Here is your coupon', 'advanced-reviews-pro' ),
				)
			);

			$tab3_options->add_field(
				array(
					'name'    => __( 'Email Body', 'advanced-reviews-pro' ),
					'desc'    => __( '<b>You can use these variables</b>:<br>{coupon} - Coupon code<br>{site_title} - Site title<br>{user_first_name} - User first name (Customer if "Review Reminder")<br>{user_last_name} - User last name (Customer if "Review Reminder")<br>{user_full_name} - User full name (Customer if "Review Reminder")<br>{user_display_name} - User display name (Customer full name if "Review Reminder")<br>{coupon_expiration_date} - Coupon expiration datetime<br>{reviewed_product_name} - Product that has been reviewed ("Ordered products" if "Review Reminder")<br>', 'advanced-reviews-pro' ),
					'id'      => ARP_PREFIX . 'review_coupon_email_body_text',
					'classes' => ARP_PREFIX . 'tab3_hide',
					'type'    => 'wysiwyg',
					'default' => 'Howdy {user_display_name},<br><br>Thank you for your review on {reviewed_product_name}!<br><br>We like to give you a coupon code: <br><br><b>{coupon}</b><br><br> which expires on {coupon_expiration_date}.<br><br>Regards,<br>Martin',
				)
			);
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

			$taxs = get_terms(
				$tax, wp_parse_args(
					$query_args, array(
						'hide_empty' => false,
					)
				)
			);

			$output_taxs = array();

			foreach ( $taxs as $tax ) {
				$output_taxs[ $tax->term_id ] = '#' . $tax->term_id . ' - ' . $tax->name;
			}

			return apply_filters( 'arp_get_taxonomies_by_slug', $output_taxs );
		}

		/**
		 * Gets all the posts of a type
		 *
		 * @param $type
		 * @since 1.0.0
		 *
		 * @return array
		 */
		private static function get_all_posts_by_type( $type ) {

			$posts = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type'      => $type,
					'post_status'    => 'any',
				)
			);

			$output_posts = array();

			foreach ( $posts as $post ) {
				$output_posts[ $post->ID ] = '#' . $post->ID . ' - ' . $post->post_title;
			}

			return apply_filters( 'arp_get_all_posts_by_type', $output_posts );
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
 *
 * @since  1.0.0
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

	return apply_filters( 'arp_get_option', $val );
}

/**
 * Instance of plugin
 *
 * @return object
 * @since  1.0.0
 */
if ( ! function_exists( 'advanced_reviews_pro_admin' ) ) {

	/**
	 * @param $plugin_name
	 * @param $version
	 *
	 * @return object
	 */
	function advanced_reviews_pro_admin( $plugin_name, $version ) {
		return Advanced_Reviews_Pro_Admin::instance( $plugin_name, $version );
	}
}
