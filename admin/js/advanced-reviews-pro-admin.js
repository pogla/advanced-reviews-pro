jQuery( document ).ready(
	function( $ ) {

			$( 'body' )
			.on(
				'change', '#selected-user', function () {
					if ( 'guest' !== $( this ).val() ) {
						$( '.hide-if-guest' ).hide();
						$( '#author-email, #author-name' ).attr( "disabled", true );
					} else {
						$( '.hide-if-guest' ).show();
						$( '#author-email, #author-name' ).attr( "disabled", false );
					}
				}
			)
			.on(
				'click', '.arp-insert-media', function(e) {

					e.preventDefault();

					var image_frame;
					if ( image_frame ) {
						  image_frame.open();
					}

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

							$( 'input#arp-selected-imgs' ).val( ids );

							refreshImages( ids );

						}
					);

					image_frame.on(
						'open',function() {

							// On open, get the id from the hidden input
							// and select the appropiate images in the media manager
							var selection = image_frame.state().get( 'selection' );
							var ids       = $( 'input#arp-selected-imgs' ).val().split( ',' );

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
				}
			)
			.on(
				'init, change', '#arp_enable_coupon_review_reminder_checkbox, #arp_enable_review_discount_checkbox', function () {
					if ( ! $( '#arp_enable_coupon_review_reminder_checkbox' ).is( ':checked' ) && ! $( '#arp_enable_review_discount_checkbox' ).is( ':checked' ) ) {
						  $( '.arp_tab3_hide' ).addClass( 'is-hidden' );
					} else {
						  $( '.arp_tab3_hide' ).removeClass( 'is-hidden' );
					}
				}
			);

			$( '#arp_enable_coupon_review_reminder_checkbox' ).trigger( 'init' );

			// Ajax request to refresh the image preview
		function refreshImages( ids ){

			var data = {
				action: 'arp_get_images',
				ids: ids
			};

			$.post(
				ajaxurl, data, function(response) {

					if ( true === response.success ) {

						var imagesContainer = $( '#selected-images' );
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
