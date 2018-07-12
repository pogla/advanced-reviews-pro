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
<style>
	.edit-form-section {
		width: calc( 100% - 20px );max-width: 1200px;
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
	<?php if ( isset( $_POST['arp-added-comment'] ) ) { ?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Comment added! Check it out: ', 'advanced-reviews-pro' ); ?><a target="_blank" href="<?php echo $comment_link; ?>"><?php echo $comment_link; ?></a></p>
		</div>
	<?php } elseif ( isset( $_POST['arp-added-comment-error'] ) ) { ?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'There has been an error adding your custom comment!', 'advanced-reviews-pro' ); ?></p>
		</div>
	<?php }
}

?>

<div id="post-body-content" class="edit-form-section edit-comment-section wrap">
	<div class="title-wrapper" >
		<h3 class="metabox-title">Add Rating</h3>
		<p class="metabox-description">On this screen you can add ratings yourself.</p>
	</div>
	<form name="arp-add-custom-rating" method="post">
		<?php wp_nonce_field( 'add_rating_action', 'add_rating_nonce' ); ?>
		<div class="stuffbox">
			<fieldset>
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
										'ID'           => $user->data->ID,
										'user_email'   => $user->data->user_email,
										'user_url'     => $user->data->user_url,
										'display_name' => $user->data->display_name,
									) );
									echo "<option value='{$user->data->ID}' data-userdata='{$user_data}'>{$user->data->display_name} (#{$user->data->ID})</option>";
								}
								?>
							</select>
						</td>
					</tr>
					<tr class="hide-if-guest">
						<td class="first"><label for="name">Name: *</label></td>
						<td><input class="full-width" type="text" name="author-name" value="" id="author-name" required></td>
					</tr>
					<tr class="hide-if-guest">
						<td class="first"><label for="email">Email: *</label></td>
						<td>
							<input class="full-width" type="email" name="author-email" value="" id="author-email" required>
						</td>
					</tr>
					<tr class="hide-if-guest">
						<td class="first"><label for="newcomment_author_url">URL:</label></td>
						<td>
							<input class="full-width" type="text" name="newcomment_author_url" id="newcomment_author_url">
						</td>
					</tr>
					<tr>
						<td class="first"><label for="comment_date">Comment Date:</label></td>
						<td>
							<input class="full-width" type="datetime-local" name="comment_date" id="comment_date">
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
								'teeny'         => true,
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
								<a href="javascript:" class="arp-insert-media">Add files</a>
								<input type="hidden" name="arp-selected-imgs" id="arp-selected-imgs">
								<div id="selected-images"></div>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<td>
							<button type="submit" class="button-primary">Add Review</button>
						</td>
					</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
	</form>
</div>
<?php
