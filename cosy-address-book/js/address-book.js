(
	function ( $ ) {
		var rest_endpoint = '/wp-json/cosy/v1';

		init_post_handlers();
		init_persisted_mapping_values();

		function init_post_handlers() {
			$( 'button#connect-api-key' ).click( function () {
				$.ajax( {
					url: rest_endpoint + '/address-book/connect',
					type: 'POST',
					headers: {
						'Content-Type': 'application/vnd.ionos.product.integration.cosy.wordpress.api-key-v1+json'
					},
					data: JSON.stringify( {
						"api_key": $( '#apiKey' ).val()
					} )
				} ).done( function ( data ) {
					if ( data.is_successful === true ) {
						location.reload( true );
					} else {
						show_form_message( 'error' );
					}
				} ).error( function () {
					show_form_message( 'error' );
				} )
			} );

			$( 'button#save-field-mappings' ).click( function () {
				var form_type = $( '.ui-form-type' ).attr( 'id' );

				$.ajax( {
					url: rest_endpoint + '/' + form_type + '/fields/save',
					type: 'POST',
					headers: {
						'Content-Type': 'application/vnd.ionos.product.integration.cosy.wordpress.field-mapping-v1+json'
					},
					data: JSON.stringify( {
						"field_mapping": get_field_mapping()
					} )
				} ).done( function ( data ) {
					show_form_message( data.is_successful ? 'success' : 'error', data.message );
				} ).error( function () {
					show_form_message( 'error' );
				} );
			} );
		}

		function init_persisted_mapping_values() {
			var form_type = $( '.ui-form-type' ).attr( 'id' );

			if ( form_type !== undefined ) {
				$.ajax( {
					url: rest_endpoint + '/' + form_type + '/fields',
					type: 'GET',
				} ).done( function ( data ) {

					var persistedMapping = (
						$.isPlainObject( data )
					) ? data : JSON.parse( data );
					$.each( persistedMapping, function ( form_field, api_field ) {
						$( "[id='" + api_field + "']" ).parent().find( 'select' ).val( form_field );
					} );
				} );
			}
		}

		function get_field_mapping() {
			var field_mapping = {};

			$( '.ui-mapping-configuration' ).each( function () {
				var api_field = $( this ).find( '.ui-api-field' ).attr( 'id' ),
					form_field = $( this ).find( '.ui-form-field select' ).val();

				if ( form_field !== 'none' ) {
					field_mapping[form_field] = api_field;
				}
			} );

			return JSON.stringify( field_mapping );
		}

		function show_form_message( type, text ) {
			var feedbackContainer = $( '#feedback-message' );
			var messageText = text || feedbackContainer.attr( 'default-error' );
			var message = $( '<p class="info-message ' + type + '" style="display:none"></p>' ).text( messageText );
			feedbackContainer.append( message );
			$( '#feedback-message .info-message' ).slideDown();
			setTimeout( function () {
				$( '#feedback-message .info-message' ).slideUp( 'normal', function () {
					$( this ).remove();
				} );
			}, 3500 );
		}
	}
)( jQuery );
