let cd_modal = ( show = true ) => {
	if ( show ) {
		jQuery( '#didit-verification-modal' ).show();
	} else {
		jQuery( '#didit-verification-modal' ).hide();
	}
};

jQuery( function ( $ ) {
	jQuery( document ).ready( function ( $ ) {
		var otp = Didit_Verification.otp;
		var phone = Didit_Verification.phone;
		var otpField = $( '#' + otp ).closest( '.cwp-field-container' );
		otpField.hide();

		var otpSent = false;

		$( document ).ajaxSend( function ( event, jqxhr, settings ) {
			var isRegistrationCall = false;
			var otpValue = '';
			var phoneValue = '';

			if ( settings.data instanceof FormData ) {
				if (
					settings.data.has( 'action' ) &&
					settings.data.get( 'action' ) === 'cubewp_submit_user_register'
				) {
					isRegistrationCall = true;

					if ( settings.data.has( `cwp_user_register[custom_fields][${otp}]` ) ) {
						otpValue = settings.data.get( `cwp_user_register[custom_fields][${otp}]` );
					}
					if ( settings.data.has( `cwp_user_register[custom_fields][${phone}]` ) ) {
						phoneValue = settings.data.get( `cwp_user_register[custom_fields][${phone}]` );
					}
				}
			} else if ( typeof settings.data === 'string' ) {
				if ( settings.data.indexOf( 'action=cubewp_submit_user_register' ) > -1 ) {
					isRegistrationCall = true;

					var formData = new URLSearchParams( settings.data );
					for ( let [ key, value ] of formData.entries() ) {
						if ( key === `cwp_user_register[custom_fields][${otp}]` ) {
							otpValue = value;
						}
						if ( key === `cwp_user_register[custom_fields][${phone}]` ) {
							phoneValue = value;
						}
					}
				}
			}

			if ( isRegistrationCall ) {
				if ( ! phoneValue ) {
					return;
				}

				if ( ! otpValue && ! otpSent ) {
					return;
				} else if ( ! otpValue && otpSent ) {
					jqxhr.abort();
					alert( 'Please enter the OTP that was sent to your phone.' );
					return false;
				}
			}
		} );

		$( document ).ajaxComplete( function ( event, xhr, settings ) {
			var isRegistrationCall = false;

			if ( settings.data instanceof FormData ) {
				if ( settings.data.has( 'action' ) && settings.data.get( 'action' ) === 'cubewp_submit_user_register' ) {
					isRegistrationCall = true;
				}
			} else if ( typeof settings.data === 'string' && settings.data.indexOf( 'action=cubewp_submit_user_register' ) > -1 ) {
				isRegistrationCall = true;
			}

			if ( isRegistrationCall ) {
				try {
					var response = JSON.parse( xhr.responseText );

					if ( response.action === 'otp_sent' ) {
						otpSent = true;
						otpField.show();
						$( '.submit-btn' ).val( 'Verify OTP & Register' );

						if ( ! $( '#resend-otp' ).length ) {
							var resendButton = $( '<button type="button" id="resend-otp" style="margin-left: 10px;">Resend OTP</button>' );
							otpField.append( resendButton );

							resendButton.on( 'click', function () {
								otpSent = false;
								$( '#cwp_field_523312282631' ).val( '' );
								$( '.submit-btn' ).click();
							} );
						}
						$( '#resend-otp' ).show();
					}
				} catch ( e ) {
				}
			}
		} );
	} );
} );
