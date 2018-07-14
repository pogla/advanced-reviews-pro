<?php

/**
 * Provide markup for voting
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/admin/partials
 */

$assets_url  = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'public/images/';
$total_score = get_comment_meta( $comment_id, 'arp_total_votes', true );
$classes     = $user_voted ? ' selected ' : '';

if ( ! $total_score ) {
	$total_score = 'Vote';
}

?>

<div class="arp-vote-wrapper">
	<div class="arp-vote down <?php echo esc_html( $classes ); ?>" data-vote="down" data-allow-admin="<?php echo $allow_admin; ?>" data-product="<?php echo esc_html( $product_id ); ?>" data-comment="<?php echo esc_html( $comment_id ); ?>" style="background-image: url(<?php echo $assets_url . 'down.svg'; ?>);"></div>
	<div class="arp-total-votes"><?php echo esc_html( $total_score ); ?></div>
	<div class="arp-vote up <?php echo esc_html( $classes ); ?>" data-vote="up" data-allow-admin="<?php echo $allow_admin; ?>" data-product="<?php echo esc_html( $product_id ); ?>" data-comment="<?php echo esc_html( $comment_id ); ?>" style="background-image: url(<?php echo $assets_url . 'up.svg'; ?>);"></div>
</div>
