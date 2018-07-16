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

$assets_url  = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'public/images/';

?>

<table class="arp-reviews-summary-wrapper">

	<?php
	foreach ( array_reverse( $comments_counts['ratings'] ) as $key => $comments_count ) :
		$counter = count( $comments_counts['ratings'] ) - $key;
		?>

	<tr class="arp-single-star-rating-wrapper" data-href="<?php echo $comments_count > 0 ? add_query_arg( 'arp-rating', $counter ) : ''; ?>">
		<?php
		$total_scores = count( $comments_counts['ratings'] );
		$percent      = 100 * ( $comments_count / $comments_counts['total'] );
		for ( $i = 0; $i < $total_scores; $i++ ) :
			?>
			<td class="single-star <?php echo ( $total_scores - $key ) > $i ? 'full' : ''; ?>"></td>
		<?php endfor; ?>
		<td class="arp-stars-name">
			<?php echo ( $total_scores - $key ) . ' ' . ( 1 === ( $total_scores - $key ) ? 'Star' : 'Stars' ); ?>
		</td>
		<td class="arp-percantage-bar-wrapper" style="width: calc( 100% - 193px - ( <?php echo $total_scores; ?> * 18px ) );">
		<span class="arp-percantage-bar">
			<span class="arp-inner-bar" style="width: <?php echo $percent; ?>%"></span>
		</span>
		</td>
		<td class="arp-summary-counts"><strong><?php echo $comments_count; ?> </strong>(<?php echo round( $percent, 1, PHP_ROUND_HALF_UP ); ?>%)</td>
	</tr>

	<?php endforeach; ?>

</table>

<?php

$filter_score = get_query_var( 'arp-rating', false );

if ( $filter_score ) {
	?>
		<div class="arp-rating-filter-wrapper">
			<h4 class="arp-rating-filter-subtitle"><?php echo sprintf( __( 'Ratings With %1$d Star%2$s', 'advanced-reviews-pro' ), esc_html( $filter_score ), '1' === $filter_score ? '' : 's'); ?></h4>
			<a href="<?php echo remove_query_arg( 'arp-rating' ); ?>"><?php _e( 'View All Ratings', 'advanced-reviews-pro' ) ?></a>
		</div>
	<?php
}