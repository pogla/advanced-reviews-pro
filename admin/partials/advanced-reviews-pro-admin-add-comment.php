<?php

/**
 * Provide a admin area view for the add comment page
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/admin/partials
 */

?>
<style>
	.edit-form-section {
		width: calc( 100% - 20px );
		max-width: 1200px;
	}
	#post-body-content table tr {
		padding: 1em;
		margin-top: -1px;
		background: #fff;
		border-bottom: 1px solid #e9e9e9;
	}
	.title-wrapper {
		margin-top: 1em;
		padding: .6em 1em;
		background-color: #fafafa;
		border-color: #e9e9e9;
		border-style: solid;
		border-width: 1px 1px 0 1px;
	}
	.metabox-title {
		font-size: 12px;
		margin-top: 0;
		margin-bottom: 0;
		text-transform: uppercase;
	}
	.metabox-description {
		margin: .25em 0 0;
	}
	.stuffbox {
		box-sizing: border-box;
	}
	.form-table.editcomment {
		margin-top: 0;
	}
	.selected-images {
		padding-top: 10px;
	}
	.form-table .full-width {
		width:100%;
	}
	.notice {
		margin-left: 4px !important;
	}
</style>

<?php

if ( isset( $_POST['add_rating_nonce'] ) && wp_verify_nonce( $_POST['add_rating_nonce'], 'add_rating_action' ) ) {
	$comment_link = get_permalink( $_POST['selected-product'] ) . '#comment-' . $_POST['arp-review-id'];
	?>
	<br>
	<?php if ( isset( $_POST['arp-added-comment'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Comment added! Check it out: ', 'advanced-reviews-pro' ); // WPCS XSS ok. ?><a target="_blank" href="<?php echo $comment_link; // WPCS XSS ok. ?>"><?php echo $comment_link; // WPCS XSS ok. ?></a></p>
		</div>
	<?php elseif ( isset( $_POST['arp-added-comment-error'] ) ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'There has been an error adding your custom comment!', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></p>
		</div>
	<?php
	endif;
}

?>

<div id="post-body-content" class="edit-form-section edit-comment-section wrap">
	<div class="title-wrapper" >
		<h3 class="metabox-title"><?php _e( 'Add Rating', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></h3>
		<p class="metabox-description"><?php _e( 'On this screen you can add ratings yourself', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>.</p>
	</div>
	<form name="arp-add-custom-rating" method="post">
		<?php wp_nonce_field( 'add_rating_action', 'add_rating_nonce' ); ?>
		<div class="stuffbox">
			<fieldset>
				<table class="form-table editcomment">
					<tbody>
					<?php do_action( 'arp_before_add_manual_review_form' ); ?>
					<tr>
						<td class="first"><label for="selected-user"><?php _e( 'Select User', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>:</label></td>
						<td>
							<select name="selected-user" id="selected-user">
								<option value="guest"><?php _e( 'Guest', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></option>
								<?php
								foreach ( $users as $user ) {
									$user_data = wp_json_encode(
										array(
											'ID'           => $user->data->ID,
											'user_email'   => $user->data->user_email,
											'user_url'     => $user->data->user_url,
											'display_name' => $user->data->display_name,
										)
									);
									echo "<option value='{$user->data->ID}' data-userdata='{$user_data}'>{$user->data->display_name} (#{$user->data->ID})</option>"; // WPCS XSS ok.
								}
								?>
							</select>
						</td>
					</tr>
					<tr class="hide-if-guest">
						<td class="first"><label for="name"><?php _e( 'Name', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>: *</label></td>
						<td><input class="full-width" type="text" name="author-name" value="" id="author-name" required></td>
					</tr>
					<tr class="hide-if-guest">
						<td class="first"><label for="email"><?php _e( 'Email', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>: *</label></td>
						<td>
							<input class="full-width" type="email" name="author-email" value="" id="author-email" required>
						</td>
					</tr>
					<tr class="hide-if-guest">
						<td class="first"><label for="newcomment_author_url"><?php _e( 'URL', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>:</label></td>
						<td>
							<input class="full-width" type="text" name="newcomment_author_url" id="newcomment_author_url">
						</td>
					</tr>
					<tr>
						<td class="first"><label for="comment_date"><?php _e( 'Comment Date', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>:</label></td>
						<td>
							<input class="full-width" type="datetime-local" name="comment_date" id="comment_date">
						</td>
					</tr>
					<tr>
						<td class="first"><label for="name"><?php _e( 'Comment', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>:</label></td>
						<td>
							<?php
							wp_editor(
								'', 'comment-content', array(
									'media_buttons' => false,
									'textarea_name' => 'comment-content',
									'textarea_rows' => 10,
									'teeny'         => true,
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="selected-rating"><?php _e( 'Rating', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>:</label></td>
						<td>
							<select name="selected-rating">
								<?php
								for ( $i = 1; $i <= 5; $i++ ) {
									echo '<option value="' . $i . '">' . $i . '</option>'; // WPCS XSS ok.
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="total-votes"><?php _e( 'Total Votes', 'advanced-reviews-pro' ); // WPCS XSS ok. ?>:</label></td>
						<td>
							<input class="full-width" type="number" name="total-votes" value="0" id="total-votes">
						</td>
					</tr>
					<tr>
						<td class="first"><label for="selected-product"><?php _e( 'Select a Product', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></label></td>
						<td>
							<select name="selected-product" id="arp-selected-product">
								<?php
								foreach ( $products as $product ) {
									$product_title = get_the_title( $product->ID );
									echo "<option value='{$product->ID}'>{$product_title} (#{$product->ID})</option>"; // WPCS XSS ok.
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="selected-images"><?php _e( 'Upload images', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></label></td>
						<td>
							<a href="javascript:" class="arp-insert-media button" data-type="image"><?php _e( 'Add Media', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></a>
							<input type="hidden" name="arp-selected-imgs" id="arp-selected-imgs">
							<div id="selected-images"></div>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="selected-videos"><?php _e( 'Upload videos', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></label></td>
						<td>
							<a href="javascript:" class="arp-insert-media button" data-type="video"><?php _e( 'Add Media', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></a>
							<input type="hidden" name="arp-selected-videos" id="arp-selected-videos">
							<div id="selected-videos"></div>
						</td>
					</tr>
					<?php do_action( 'arp_after_add_manual_review_form' ); ?>
					<tr>
						<td>
							<button type="submit" class="button-primary"><?php _e( 'Add Review', 'advanced-reviews-pro' ); // WPCS XSS ok. ?></button>
						</td>
					</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
	</form>
</div>
