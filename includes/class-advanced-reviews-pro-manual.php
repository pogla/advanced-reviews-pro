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
 * Handle recaptcha on WooCommerce reviews
 *namediv
 * @since      1.0.0
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/includes
 * @author     Matic Pogladič <matic.pogladic@gmail.com>
 */
class Advanced_Reviews_Pro_Manual {

	/**
	 * Prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $prefix    Prefix for cmb2 fields.
	 */
	private $prefix = 'arp_';

	/**
	 * @since    1.0.0
	 */
	public function __construct() {
	}

	public function add_rating_submenu() {
		add_submenu_page( 'edit-comments.php', 'Add rating', 'Add rating', 'manage_options', 'custom-link-unique-identifier', array( $this, 'output_add_comment' ) );
	}

	public function output_add_comment() {

		$users            = get_users();
		$review_score_max = absint( arp_get_option( $this->prefix . 'max_review_score_number' ) );
		if ( ! $review_score_max ) {
			$review_score_max = 5;
		}

		$products = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		) );

		?>
		<div id="post-body-content" class="edit-form-section edit-comment-section wrap" style="width: calc( 100% - 20px );">
			<form name="arp-add-custom-rating" method="post">
				<?php wp_nonce_field( 'add_rating_action', 'add_rating_nonce' ); ?>

				<div class="stuffbox" style="padding: 20px;box-sizing: border-box;">
					<div class="inside">
						<fieldset>
							<legend class="edit-comment-author">Author</legend>
							<table class="form-table editcomment">
								<tbody>
								<tr>
									<td class="first"><label for="selected-user">Select User:</label></td>
									<td>
										<select name="selected-user" id="selected-user">
											<option value="guest">Guest</option>
											<?php
											foreach ( $users as $user ) {
												$user_data = wp_json_encode( array(
													'ID' => $user->data->ID,
													'user_email' => $user->data->user_email,
													'user_url' => $user->data->user_url,
													'display_name' => $user->data->display_name,
												) );
												echo "<option value='{$user_data}'>{$user->data->display_name} (#{$user->ID})</option>";
											}
											?>
										</select>
									</td>
								</tr>
								<tr class="hide-if-guest">
									<td class="first"><label for="name">Name: *</label></td>
									<td><input style="width:100%;" type="text" name="author-name" value="" id="author-name" required></td>
								</tr>
								<tr class="hide-if-guest">
									<td class="first"><label for="email">Email: *</label></td>
									<td>
										<input style="width:100%;" type="email" name="author-email" value="" id="author-email" required>
									</td>
								</tr>
								<tr class="hide-if-guest">
									<td class="first"><label for="newcomment_author_url">URL:</label></td>
									<td>
										<input style="width:100%;" type="text" name="newcomment_author_url" id="newcomment_author_url">
									</td>
								</tr>
								<tr>
									<td class="first"><label for="name">Comment</label></td>
									<td>
										<?php
										wp_editor( '', 'comment-content', array(
											'media_buttons' => false,
											'textarea_name' => 'comment-content',
											'textarea_rows' => 10,
											'teeny' => true,
										) );
										?>
									</td>
								</tr>
								<tr>
									<td class="first"><label for="selected-rating">Rating</label></td>
									<td>
										<select name="selected-rating">
											<?php
											for ( $i = 1; $i <= $review_score_max; $i++ ) {
												echo '<option value="' . esc_attr( $i ) . '">' . esc_html( $i ) . '</option>';
											}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td class="first"><label for="selected-product">Select a Product</label></td>
									<td>
										<select name="selected-product" id="arp-selected-product">
											<?php
											foreach ( $products as $product ) {
												$product_title = get_the_title( $product->ID );
												echo "<option value='{$product->ID}'>{$product_title} (#{$product->ID})</option>";
											}
											?>
										</select>
									</td>
								</tr>
								<?php if ( 'on' === arp_get_option( $this->prefix . 'enable_images_checkbox' ) ) { ?>
									<tr>
										<td class="first"><label for="selected-images">Upload images</label></td>
										<td>
											<a href="javascript:;" class="arp-insert-media">Add files</a>
											<input type="hidden" id="arp-selected-imgs">
											<div id="selected-images" style="padding-top: 10px;"></div>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							<br>
						</fieldset>
						<button type="submit" class="button-primary">Add Review</button>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	public function arp_get_images() {

		if( isset( $_GET['ids'] ) ){

			$ids    = explode( ',', $_GET['ids'] );
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

}
