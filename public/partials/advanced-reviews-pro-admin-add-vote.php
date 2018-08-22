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
	$total_score = __( 'Vote', 'advanced-reviews-pro' );
}

do_action( 'arp_before_voting_html', $total_score );

?>

<div class="arp-vote-wrapper">
	<div class="arp-vote down <?php echo $classes; // WPCS XSS ok. ?>" data-vote="down" data-allow-admin="<?php echo $allow_admin; // WPCS XSS ok. ?>" data-product="<?php echo $product_id; // WPCS XSS ok. ?>" data-comment="<?php echo $comment_id; // WPCS XSS ok ?>" style="background-image: url(<?php echo $assets_url . 'down.svg'; // WPCS XSS ok ?>);"></div>
	<div class="arp-total-votes"><?php echo $total_score; // WPCS XSS ok ?></div>
	<div class="arp-vote up <?php echo $classes; // WPCS XSS ok ?>" data-vote="up" data-allow-admin="<?php echo $allow_admin; // WPCS XSS ok ?>" data-product="<?php echo $product_id; // WPCS XSS ok ?>" data-comment="<?php echo $comment_id; // WPCS XSS ok ?>" style="background-image: url(<?php echo $assets_url . 'up.svg'; // WPCS XSS ok ?>);"></div>
</div>

<?php
do_action( 'arp_after_voting_html', $total_score );
?>
