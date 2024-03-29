jQuery( document ).ready(
	function( $ ) {

			$( 'body' )
			.on( 'change', '#selected-user', function () {
					if ( 'guest' !== $( this ).val() ) {
						$( '.hide-if-guest' ).hide();
						$( '#author-email, #author-name' ).attr( "disabled", true );
					} else {
						$( '.hide-if-guest' ).show();
						$( '#author-email, #author-name' ).attr( "disabled", false );
					}
				} )
			.on( 'click', '.arp-insert-media', function(e) {

					e.preventDefault();

					var image_frame, type = $( this ).data( 'type' );

					// Define image_frame as wp.media object
					image_frame = wp.media(
						{
							title: 'Select Review Images',
							multiple : true,
							library : {
								type : 'image'
							}
						}
					);

					if ( 'video' === type ) {
						// Define image_frame as wp.media object
						image_frame = wp.media(
							{
								title: 'Select Review Videos',
								multiple : true,
								library : {
									type : 'video'
								}
							}
						);
					}

					if ( image_frame ) {
						  image_frame.open();
					}

					image_frame.on(
						'close', function() {

							/**
							 *  On close, get selections and save to the hidden input
							 *  plus other AJAX stuff to refresh the image preview
							 */
							var selection   = image_frame.state().get( 'selection' );
							var gallery_ids = [];
							var index       = 0;

							selection.each(
								function(attachment) {
									gallery_ids[index] = attachment['id'];
									index++;
								}
							);

							var ids = gallery_ids.join( ',' );

							if ( 'image' === type ) {
								$( 'input#arp-selected-imgs' ).val( ids );
								refreshImages( ids, type );
							}

							if ( 'video' === type ) {
								$( 'input#arp-selected-videos' ).val( ids );
								refreshImages( ids, type );
							}

						}
					);

					image_frame.on(
						'open',function() {

							// On open, get the id from the hidden input
							// and select the appropiate images in the media manager
							var selection = image_frame.state().get( 'selection' ), ids;

							if ( 'image' === type ) {
								ids = $( 'input#arp-selected-imgs' ).val().split( ',' );
							}

							if ( 'video' === type ) {
								ids = $( 'input#arp-selected-videos' ).val().split( ',' );
							}

							ids.forEach(
								function( id ) {
									attachment = wp.media.attachment( id );
									attachment.fetch();
									selection.add( attachment ? [ attachment ] : [] );
								}
							);

						}
					);

					image_frame.open();
				} )
			.on( 'init, change', '#arp_enable_coupon_review_reminder_checkbox, #arp_enable_review_discount_checkbox', function () {
					if ( ! $( '#arp_enable_coupon_review_reminder_checkbox' ).is( ':checked' ) && ! $( '#arp_enable_review_discount_checkbox' ).is( ':checked' ) ) {
						  $( '.arp_tab3_hide' ).addClass( 'is-hidden' );
					} else {
						  $( '.arp_tab3_hide' ).removeClass( 'is-hidden' );
					}
				} );

			$( '#arp_enable_coupon_review_reminder_checkbox' ).trigger( 'init' );

			// Ajax request to refresh the image preview
		function refreshImages( ids, type ){

			var data = {
				action: 'arp_get_files',
				ids: ids,
				type: type
			};

			$.post(
				ajaxurl, data, function( response ) {

					if ( true === response.success ) {

						var imagesContainer = type === 'image' ? $( '#selected-images' ) : $( '#selected-videos' );
						imagesContainer.html( '' );

						response.data.images.forEach(
							function( image ) {
								imagesContainer.append( image );
							}
						);

					}
				}
			);
		}

	}
);
