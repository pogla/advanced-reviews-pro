jQuery( document ).ready(
	function( $ ) {

			$( '#review_form form#commentform' ).attr( 'enctype', 'multipart/form-data' ).attr( 'encoding', 'multipart/form-data' );

			var votedComments = [];

			$( 'body' )
			.on(
				'click', '.arv-comment-images img', function (e) {

					e.preventDefault();

					var pics     = $( this ).parents( '.arv-comment-images' ).find( 'img' );
					var this_pic = $( this );
					var inx      = 0;
					if ( pics.length > 0 && this_pic.length > 0 ) {
						var a = [];
						for ( var i = 0; i < pics.length; i++ ) {
							var img = $( pics[i] );
							a.push(
								{
									src: img.data( 'full-src' ),
									w: img.data( 'natural-width' ),
									h: img.data( 'natural-height' ),
									title: pics[i].alt
								}
							);
							if ( this_pic.data( 'full-src' ) === img.data( 'full-src' ) ) {
								inx = i;
							}
						}
						new PhotoSwipe(
							$( ".pswp" )[0], PhotoSwipeUI_Default, a, {
								index: inx
							}
						).init();
					}

				}
			)
			.on(
				'init', '#arp-rating', function() {
					var outputStars = '';
					for ( var i = 1; i < $( '#arp-rating option' ).length; i++) {
						  outputStars += '<a class="star-' + i + '" href="#">' + i + '</a>';
					}
					$( '#arp-rating' ).hide().before( '<p class="stars"><span>' + outputStars + '</span></p>' );
				}
			)
			.on(
				'click', '#respond p.stars a', function() {
					var $star  = $( this ),
					$rating    = $( this ).closest( '#respond' ).find( '#arp-rating' ),
					$container = $( this ).closest( '.stars' );

					$rating.val( $star.text() );
					$star.siblings( 'a' ).removeClass( 'active' );
					$star.addClass( 'active' );
					$container.addClass( 'selected' );

					return false;
				}
			)
			.on(
				'click', '.arp-vote', function(e) {

					e.preventDefault();

					var adminCanUnlimited = 1 === $( this ).data( 'allow-admin' );
					var commendID         = $( this ).data( 'comment' );

					if ( ! adminCanUnlimited ) {

						if ( $( this ).hasClass( 'selected' ) ) {
							return;
						}

						if ( votedComments.includes( commendID ) && ! adminCanUnlimited ) {
							return;
						}

						votedComments.push( commendID );

						$( this ).addClass( 'selected' );

					}

					var data = {
						action: 'arp_vote',
						vote: $( this ).data( 'vote' ),
						product: $( this ).data( 'product' ),
						comment: commendID,
						security: wp_vars.security
					};

					var votingTotal = $( this ).siblings( '.arp-total-votes' );
					if ( isNaN( votingTotal.html() ) ) {
						  votingTotal.html( '0' );
					}

					if ( $( this ).hasClass( 'up' ) ) {
						  votingTotal.html( parseInt( votingTotal.html() ) + 1 );
					} else {
						  votingTotal.html( parseInt( votingTotal.html() ) - 1 );
					}

					$.post( ajaxurl, data, function(response) {} );

				}
			)
			.on(
				'click', '.arp-single-star-rating-wrapper', function (e) {
					e.preventDefault();
					if ( $( this ).data( 'href' ).length ) {
						  location.href = $( this ).data( 'href' );
					}
				}
			)
			.on(
				'init', '#comment', function () {
					if ( 'true' === getParameterByName( 'arp-add-review' ) ) {
						$( 'html, body' ).animate(
							{
								scrollTop: $( '#review_form_wrapper' ).offset().top
							}, 500
						);
					}
				}
			);

			$( '.arp-vote-wrapper, #arp-rating, #comment' ).trigger( 'init' );

	}
);

// Get url parameter by name
function getParameterByName( name, url ) {

	if ( ! url ) {
		url = window.location.href;
	}
	name      = name.replace( /[\[\]]/g, '\\$&' );
	var regex = new RegExp( '[?&]' + name + '(=([^&#]*)|&|#|$)' ), results = regex.exec( url );
	if ( ! results ) {
		return null;
	}
	if ( ! results[2] ) {
		return '';
	}

	return decodeURIComponent( results[2].replace( /\+/g, ' ' ) );
}
