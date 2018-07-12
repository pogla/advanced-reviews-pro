jQuery( document ).ready( function( $ ) {

	$('#review_form form#commentform').attr( 'enctype', 'multipart/form-data' ).attr( 'encoding', 'multipart/form-data' );

	$( 'body' )
		.on( 'click', '.arv-comment-images img', function (e) {

			e.preventDefault();

			var pics = $(this).parents('.arv-comment-images').find('img');
			var this_pic = $(this);
			var inx = 0;
			if( pics.length > 0 && this_pic.length > 0 ) {
				var a = [];
				for( var i=0; i < pics.length; i++ ) {
					var img = $( pics[i] );
					a.push({
						src: img.data('full-src'),
						w: img.data('natural-width'),
						h: img.data('natural-height'),
						title: pics[i].alt
					});
					if( this_pic.data('full-src') === img.data('full-src') ) {
						inx = i;
					}
				}
				new PhotoSwipe( $(".pswp")[0], PhotoSwipeUI_Default, a, {
					index: inx
				} ).init();
			}

		} )
		.on( 'init', '#arp-rating', function() {
			var outputStars = '';
			for ( var i = 1; i < $( '#arp-rating option' ).length; i++) {
				outputStars += '<a class="star-' + i + '" href="#">' + i + '</a>';
			}
			$( '#arp-rating' ).hide().before( '<p class="stars"><span>' + outputStars + '</span></p>' );
		} )
		.on( 'click', '#respond p.stars a', function() {
			var $star = $( this ),
				$rating = $( this ).closest( '#respond' ).find( '#arp-rating' ),
				$container = $( this ).closest( '.stars' );

			$rating.val( $star.text() );
			$star.siblings( 'a' ).removeClass( 'active' );
			$star.addClass( 'active' );
			$container.addClass( 'selected' );

			return false;
		} )
		.on( 'init', '#reviews', function() {

		} );

		$( '#reviews, #arp-rating' ).trigger( 'init' );

});