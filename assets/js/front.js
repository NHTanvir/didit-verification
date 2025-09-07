jQuery(function($) {
    jQuery(document).ready(function($) {
        var otp = Didit_Verification.otp_field;
        var phone = Didit_Verification.phone_field;
        
        // Handle both author and subscriber forms
        var authorOtpField = $('.cwp-from-author [data-name="' + otp + '"]');
        var subscriberOtpField = $('.cwp-from-subscriber [data-name="' + otp + '"]');
        var authorPhoneField = $('.cwp-from-author [data-name="' + phone + '"]');
        var subscriberPhoneField = $('.cwp-from-subscriber [data-name="' + phone + '"]');
        
        var otpSentAuthor = false;
        var otpSentSubscriber = false;

        // Initially hide OTP fields
        authorOtpField.hide();
        subscriberOtpField.hide();

        // Function to get current active form with better detection
        function getCurrentForm() {
            // Check if author form is in active tab
            var authorForm = $('.cwp-from-author');
            var subscriberForm = $('.cwp-from-subscriber');
            
            // Method 1: Check parent tab content
            if (authorForm.closest('.cwp-tab-content').hasClass('cwp-active-tab-content')) {
                return 'author';
            }
            if (subscriberForm.closest('.cwp-tab-content').hasClass('cwp-active-tab-content')) {
                return 'subscriber';
            }
            
            // Method 2: Check visibility
            if (authorForm.is(':visible') && !subscriberForm.is(':visible')) {
                return 'author';
            }
            if (subscriberForm.is(':visible') && !authorForm.is(':visible')) {
                return 'subscriber';
            }
            
            // Method 3: Check display style
            if (authorForm.css('display') !== 'none' && subscriberForm.css('display') === 'none') {
                return 'author';
            }
            if (subscriberForm.css('display') !== 'none' && authorForm.css('display') === 'none') {
                return 'subscriber';
            }
            
            // Fallback: check which tab is active
            var activeTabHref = $('.cwp-tabs a.active').attr('href');
            if (activeTabHref) {
                if (activeTabHref.includes('author') || activeTabHref.includes('1')) {
                    return 'author';
                } else if (activeTabHref.includes('subscriber') || activeTabHref.includes('2')) {
                    return 'subscriber';
                }
            }
            
            return null;
        }

        // Function to validate form fields before OTP
        function validateFormFields(formType) {
            var form = formType === 'author' ? $('.cwp-from-author') : $('.cwp-from-subscriber');
            var isValid = true;
            var errorMessage = '';

            // Check required fields
            form.find('input.required, input[required]').each(function() {
                var fieldName = $(this).attr('name');
                var fieldValue = $(this).val().trim();
                var fieldLabel = $(this).closest('.cwp-field-container').find('label').text().replace('*', '').trim();
                
                // If no label found, try other methods
                if (!fieldLabel) {
                    fieldLabel = $(this).attr('placeholder') || $(this).prev('label').text() || fieldName || 'Field';
                }

                // Skip OTP field in validation
                if (fieldName && fieldName.includes(otp)) {
                    return true;
                }

                if (!fieldValue) {
                    isValid = false;
                    errorMessage = fieldLabel + ' is required.';
                    $(this).focus();
                    return false;
                }

                // Email validation
                if ($(this).attr('type') === 'email') {
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(fieldValue)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                        $(this).focus();
                        return false;
                    }
                }

                // Password confirmation validation
                if (fieldName && fieldName.includes('confirm_pass')) {
                    var passwordField = form.find('input[name*="user_pass"]:not([name*="confirm"])');
                    if (passwordField.val() !== fieldValue) {
                        isValid = false;
                        errorMessage = 'Password and confirm password do not match.';
                        $(this).focus();
                        return false;
                    }
                }
            });

            return { isValid: isValid, message: errorMessage };
        }

        // Function to show messages
        function showMessage(currentForm, message, type) {
            var form = currentForm === 'author' ? $('.cwp-from-author') : $('.cwp-from-subscriber');
            var existingMsg = form.find('.otp-message');
            
            if (existingMsg.length) {
                existingMsg.remove();
            }
            
            var messageDiv = $('<div class="otp-message ' + type + '" style="padding: 10px; margin: 10px 0; border-radius: 4px; ' + 
                (type === 'error' ? 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' : 
                'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;') + '">' + message + '</div>');
            form.prepend(messageDiv);
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(function() {
                    messageDiv.fadeOut();
                }, 5000);
            }
        }

        $(document).ajaxSend(function(event, jqxhr, settings) {
            var isRegistrationCall = false;
            var otpValue = '';
            var phoneValue = '';
            var currentForm = getCurrentForm();

            // Debug logging
            console.log('AJAX Send - Current form:', currentForm);

            if (settings.data instanceof FormData) {
                if (settings.data.has('action') && 
                    settings.data.get('action') === 'cubewp_submit_user_register') {
                    isRegistrationCall = true;

                    if (settings.data.has(`cwp_user_register[custom_fields][${otp}]`)) {
                        otpValue = settings.data.get(`cwp_user_register[custom_fields][${otp}]`);
                    }
                    if (settings.data.has(`cwp_user_register[custom_fields][${phone}]`)) {
                        phoneValue = settings.data.get(`cwp_user_register[custom_fields][${phone}]`);
                    }
                }
            } else if (typeof settings.data === 'string') {
                if (settings.data.indexOf('action=cubewp_submit_user_register') > -1) {
                    isRegistrationCall = true;

                    var formData = new URLSearchParams(settings.data);
                    for (let [key, value] of formData.entries()) {
                        if (key === `cwp_user_register[custom_fields][${otp}]`) {
                            otpValue = value;
                        }
                        if (key === `cwp_user_register[custom_fields][${phone}]`) {
                            phoneValue = value;
                        }
                    }
                }
            }

            if (isRegistrationCall && currentForm) {
                // Clear previous messages
                $('.otp-message').remove();
                
                // First validate all form fields except OTP
                var validation = validateFormFields(currentForm);
                if (!validation.isValid) {
                    console.log('Validation failed:', validation.message);
                    jqxhr.abort();
                    showMessage(currentForm, validation.message, 'error');
                    return false;
                }

                if (!phoneValue) {
                    console.log('Phone number missing');
                    jqxhr.abort();
                    showMessage(currentForm, 'Phone number is required.', 'error');
                    return false;
                }

                var otpSent = currentForm === 'author' ? otpSentAuthor : otpSentSubscriber;

                if (!otpValue && !otpSent) {
                    // First submission - will send OTP
                    console.log('Sending OTP for first time');
                    $('.submit-btn').addClass('sending-otp');
                    return;
                } else if (!otpValue && otpSent) {
                    console.log('OTP required but not provided');
                    jqxhr.abort();
                    showMessage(currentForm, 'Please enter the OTP that was sent to your phone.', 'error');
                    return false;
                } else if (otpValue && otpSent) {
                    // OTP verification
                    console.log('Verifying OTP');
                    $('.submit-btn').addClass('verifying-otp');
                }
            } else if (isRegistrationCall && !currentForm) {
                console.log('Could not determine current form, aborting request');
                jqxhr.abort();
                // Try to show message on both forms as fallback
                showMessage('author', 'Please refresh the page and try again.', 'error');
                showMessage('subscriber', 'Please refresh the page and try again.', 'error');
                return false;
            }
        });

        $(document).ajaxComplete(function(event, xhr, settings) {
            var isRegistrationCall = false;
            var currentForm = getCurrentForm();

            if (settings.data instanceof FormData) {
                if (settings.data.has('action') && 
                    settings.data.get('action') === 'cubewp_submit_user_register') {
                    isRegistrationCall = true;
                }
            } else if (typeof settings.data === 'string' && 
                       settings.data.indexOf('action=cubewp_submit_user_register') > -1) {
                isRegistrationCall = true;
            }

            if (isRegistrationCall) {
                // Remove loading classes
                $('.submit-btn').removeClass('sending-otp verifying-otp');
                
                // Check if request was aborted
                if (xhr.readyState === 0 || xhr.status === 0) {
                    console.log('Request was aborted - this is expected for validation errors');
                    return;
                }
                
                // Check if we have a valid response
                if (!xhr.responseText || xhr.responseText === 'undefined' || xhr.responseText.trim() === '') {
                    console.log('No valid response received');
                    return;
                }

                try {
                    var response = JSON.parse(xhr.responseText);
                    console.log('AJAX Complete - Response:', response, 'Current form:', currentForm);

                    if (response.action === 'otp_sent' && currentForm) {
                        if (currentForm === 'author') {
                            otpSentAuthor = true;
                            authorOtpField.show();
                            $('.cwp-from-author .submit-btn').val('Verify OTP & Register');
                            
                            // Add resend button for author form
							if (!$('.cwp-from-author .resend-otp-btn').length) {
								var resendButton = $('<button type="button" class="resend-otp-btn">Resend OTP</button>');
								authorOtpField.after(resendButton);

								resendButton.on('click', function(e) {
									e.preventDefault();
									$(this).prop('disabled', true).text('Sending...');
									otpSentAuthor = false;
									authorOtpField.find('input').val('');
									$('.cwp-from-author .submit-btn').val('Register').click();
									
									setTimeout(function() {
										resendButton.prop('disabled', false).text('Resend OTP');
									}, 3000);
								});
							}
                            showMessage(currentForm, 'OTP sent successfully to your phone', 'success');
                            
                        } else if (currentForm === 'subscriber') {
                            otpSentSubscriber = true;
                            subscriberOtpField.show();
                            $('.cwp-from-subscriber .submit-btn').val('Verify OTP & Register');
                            
                            // Add resend button for subscriber form
					if (!$('.cwp-from-subscriber .resend-otp-btn').length) {
						var resendButton = $('<button type="button" class="resend-otp-btn">Resend OTP</button>');
						subscriberOtpField.after(resendButton);

						resendButton.on('click', function(e) {
							e.preventDefault();
							$(this).prop('disabled', true).text('Sending...');
							otpSentSubscriber = false;
							subscriberOtpField.find('input').val('');
							$('.cwp-from-subscriber .submit-btn').val('Register').click();
							
							setTimeout(function() {
								resendButton.prop('disabled', false).text('Resend OTP');
							}, 3000);
						});
					}
                            showMessage(currentForm, 'OTP sent successfully to your phone', 'success');
                        }
                    } else if (response.type === 'success' && response.redirectURL) {
                        // Registration completed successfully
                        if (currentForm) {
                            showMessage(currentForm, response.msg, 'success');
                        }
                        setTimeout(function() {
                            window.location.href = response.redirectURL;
                        }, 2000);
                    } else if (response.type === 'error') {
                        // Show error message
                        if (currentForm) {
                            showMessage(currentForm, response.msg, 'error');
                        } else {
                            // Fallback - show on both forms
                            showMessage('author', response.msg, 'error');
                            showMessage('subscriber', response.msg, 'error');
                        }
                    } else if (response.type === 'success') {
                        // Registration successful without redirect
                        if (currentForm) {
                            showMessage(currentForm, response.msg, 'success');
                        }
                    }
                } catch (e) {
                    console.log('Error parsing response:', e);
                    console.log('Response text:', xhr.responseText);
                    console.log('XHR status:', xhr.status, 'Ready state:', xhr.readyState);
                    
                    // Only show error if it's not an aborted request
                    if (xhr.readyState !== 0 && xhr.status !== 0) {
                        if (currentForm) {
                            showMessage(currentForm, 'An error occurred. Please try again.', 'error');
                        }
                    }
                }
            }
        });

        // Reset OTP state when switching tabs
        $('.cwp-tabs a').on('click', function(e) {
            console.log('Tab clicked:', $(this).attr('href'));
            
            setTimeout(function() {
                // Hide all OTP fields when switching tabs
                authorOtpField.hide();
                subscriberOtpField.hide();
                
                // Reset button text
                $('.cwp-from-author .submit-btn').val('Register');
                $('.cwp-from-subscriber .submit-btn').val('Register');
                
                // Clear OTP values
                authorOtpField.find('input').val('');
                subscriberOtpField.find('input').val('');
                
                // Remove resend buttons
                $('.resend-otp-btn').remove();
                
                // Clear messages
                $('.otp-message').remove();
                
                // Reset OTP sent flags
                otpSentAuthor = false;
                otpSentSubscriber = false;
                
                console.log('Tab switch reset completed. Current form:', getCurrentForm());
            }, 100);
        });

        // Additional error handling for form submissions
        $(document).on('submit', '.cwp-from-author, .cwp-from-subscriber', function(e) {
            var currentForm = getCurrentForm();
            console.log('Form submitted:', currentForm);
            
            if (!currentForm) {
                e.preventDefault();
                console.log('Form submission prevented - could not determine current form');
                return false;
            }
        });
    });
});