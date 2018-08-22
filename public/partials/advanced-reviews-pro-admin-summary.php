<?php

/**
 * Provide markup for summary
 *
 * @link       https://maticpogladic.com/
 * @since      1.0.0
 *
 * @package    Advanced_Reviews_Pro
 * @subpackage Advanced_Reviews_Pro/admin/partials
 */

$assets_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'public/images/';

do_action( 'arp_before_review_summary' );

?>

<table class="arp-reviews-summary-wrapper">

	<?php
	foreach ( array_reverse( $comments_counts['ratings'] ) as $key => $comments_count ) :
		$counter = count( $comments_counts['ratings'] ) - $key;
		?>

	<tr class="arp-single-star-rating-wrapper" data-href="<?php echo $comments_count > 0 ? esc_url_raw( add_query_arg( 'arp-rating', $counter, get_permalink( get_the_ID() ) ) ) . '#reviews' : ''; ?>">
		<?php
		$total_scores = count( $comments_counts['ratings'] );
		$percent      = 100 * ( $comments_count / $comments_counts['total'] );
		for ( $i = 0; $i < $total_scores; $i++ ) :
			?>
			<td class="single-star <?php echo ( $total_scores - $key ) > $i ? 'full' : ''; ?>"></td>
		<?php endfor; ?>
		<td class="arp-stars-name">
			<?php echo esc_html( ( $total_scores - $key ) . ' ' . ( 1 === ( $total_scores - $key ) ? __( 'Star', 'advanced-reviews-pro' ) : __( 'Stars', 'advanced-reviews-pro' ) ) ); ?>
		</td>
		<td class="arp-percantage-bar-wrapper" style="width: calc( 100% - 193px - ( <?php echo $total_scores; // WPCS: XSS ok. ?> * 18px ) );">
		<span class="arp-percantage-bar">
			<span class="arp-inner-bar" style="width: <?php echo $percent; // WPCS: XSS ok ?>%"></span>
		</span>
		</td>
		<td class="arp-summary-counts"><strong><?php echo $comments_count; // WPCS: XSS ok ?> </strong>(<?php echo round( $percent, 1, PHP_ROUND_HALF_UP ); // WPCS: XSS ok. ?>%)</td>
	</tr>

	<?php endforeach; ?>

</table>

<?php

$filter_score = get_query_var( 'arp-rating', false );

if ( $filter_score ) {
	?>
		<div class="arp-rating-filter-wrapper">
			<?php /* translators: 1: score, 2: s */ ?>
			<h4 class="arp-rating-filter-subtitle"><?php echo sprintf( __( 'Ratings With %1$d Star%2$s', 'advanced-reviews-pro' ), $filter_score, '1' === $filter_score ? '' : 's' ); // WPCS: XSS ok. ?></h4>
			<a href="<?php echo esc_url_raw( get_permalink( get_the_ID() ) ) . '#reviews'; ?>"><?php _e( 'View All Ratings', 'advanced-reviews-pro' ); // WPCS: XSS ok. ?></a>
		</div>
	<?php
}

do_action( 'arp_after_review_summary' );
