jQuery( document ).ready( function( $ ) {

	$( 'body' )
		.on( 'init', '#rating', function () {
			var outputRatingHtml = '';
			var selectedIndex = checkSelectedIndex();
			for ( var i = 1; i <= wp_vars.review_score_max; i++ ) {
				var selected = '';
				if ( i === selectedIndex ) {
					selected = ' selected="selected" ';
				}
				outputRatingHtml += '<option value="' + ( ( i / wp_vars.review_score_max ) * 5 ) + '" ' + selected + '>' + i + '</option>';
			}
			$( this ).html( outputRatingHtml );
		} );

	$( '#rating' ).trigger( 'init' );

});

/**
 * Calculates the ratings
 *
 * @returns {number}
 */
var checkSelectedIndex = function () {

	var scoreMax = wp_vars.review_score_max;
	var selectedScore = wp_vars.selected_score;
	// Round
	selectedScore = Math.round(selectedScore * 100) / 100;

	for ( var i = 2; i <= scoreMax; i++ ) {

		var currentNewValue = ( i / scoreMax ) * 5;
		// Round
		currentNewValue = Math.round(currentNewValue * 100) / 100;

		if ( selectedScore === currentNewValue ) {
			return i;
		}

		var prevNewValue = ( ( i - 1 ) / scoreMax ) * 5;
		// Round
		prevNewValue = Math.round(prevNewValue * 100) / 100;

		var prevDiff = selectedScore - prevNewValue;
		var currentDiff = selectedScore - currentNewValue;

		if ( prevDiff > 0 && currentDiff < 0 ) {

			if ( Math.abs( prevDiff ) >= Math.abs( currentDiff ) ) {
				return i;
			} else {
				return i - 1;
			}

		}

	}

	return 1;

}