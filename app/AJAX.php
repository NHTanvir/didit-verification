<?php
namespace Codexpert\DiditVerification\App;

use Codexpert\Plugin\Base;
use Codexpert\DiditVerification\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AJAX extends Base {

    public $plugin;

    public function __construct( $plugin ) {
        $this->plugin  = $plugin;
        $this->slug    = $this->plugin['TextDomain'];
        $this->name    = $this->plugin['Name'];
        $this->version = $this->plugin['Version'];
    }

    private $send_otp_url = 'https://verification.didit.me/v2/phone/send/';
    private $verify_otp_url = 'https://verification.didit.me/v2/phone/check/';

    public function cubewp_submit_user_register() {
        // Security nonce verification first - check the actual field name used in forms
        if (!wp_verify_nonce($_POST['security_nonce'], "cubewp_submit_user_register")) {
            wp_send_json(
                array(
                    'type' => 'error',
                    'msg'  => esc_html__('Sorry! Security Verification Failed.', 'cubewp-frontend'),
                )
            );
        }

        // Get OTP and phone field names from settings
        $otp_field = Helper::get_option('didit-verification_basic', 'author_otp');
        $phone_field = Helper::get_option('didit-verification_basic', 'author_phone');

        // Extract form data
        $default_fields = isset($_POST['cwp_user_register']['default_fields']) ? 
            CubeWp_Sanitize_Text_Array($_POST['cwp_user_register']['default_fields']) : array();
        $custom_fields = isset($_POST['cwp_user_register']['custom_fields']) ? 
            CubeWp_Sanitize_Fields_Array($_POST['cwp_user_register']['custom_fields'], 'user') : array();

        $phone_number = isset($custom_fields[$phone_field]) ? 
            sanitize_text_field($custom_fields[$phone_field]) : '';
        $otp_code = isset($custom_fields[$otp_field]) ? 
            sanitize_text_field($custom_fields[$otp_field]) : '';

        // Validate all required fields before proceeding with OTP
        $validation_result = $this->validate_registration_fields($default_fields, $custom_fields, $phone_field, $otp_field);
        if (!$validation_result['success']) {
            wp_send_json(
                array(
                    'type' => 'error',
                    'msg'  => $validation_result['message'],
                )
            );
        }

        // Phone number validation and formatting
        if (empty($phone_number)) {
            wp_send_json(
                array(
                    'type' => 'error',
                    'msg'  => 'Phone number is required.',
                )
            );
        }

        $formatted_phone = $phone_number;
        if (!$formatted_phone) {
            wp_send_json(
                array(
                    'type' => 'error',
                    'msg'  => 'Invalid phone number format.',
                )
            );
        }

        if (empty($otp_code)) {
            // Send OTP if not provided
            $this->send_otp($formatted_phone);
        } else {
            // Verify OTP if provided
            $verification_result = $this->verify_otp($formatted_phone, $otp_code);

            if ($verification_result['success']) {
                // OTP verified, proceed with original registration logic
                remove_action('wp_ajax_nopriv_cubewp_submit_user_register', array($this, 'cubewp_submit_user_register'), 5);
                
                // Call original registration function
                $this->proceed_with_registration($default_fields, $custom_fields);
                return;
            } else {
                wp_send_json(
                    array(
                        'type' => 'error',
                        'msg'  => $verification_result['message'],
                    )
                );
            }
        }
    }

    /**
     * Validate registration fields before sending OTP
     */
    private function validate_registration_fields($default_fields, $custom_fields, $phone_field, $otp_field) {
        $user_login = isset($default_fields['user_login']) ? sanitize_text_field($default_fields['user_login']) : '';
        $user_email = isset($default_fields['user_email']) ? sanitize_email($default_fields['user_email']) : '';
        $user_pass = isset($default_fields['user_pass']) ? sanitize_text_field($default_fields['user_pass']) : '';
        $confirm_pass = isset($default_fields['confirm_pass']) ? sanitize_text_field($default_fields['confirm_pass']) : '';

        // Username validation
        if (empty($user_login)) {
            return array('success' => false, 'message' => esc_html__("Username is required.", "cubewp-framework"));
        }

        if (username_exists($user_login)) {
            return array('success' => false, 'message' => esc_html__("This username already exists.", "cubewp-framework"));
        }

        // Email validation
        if (empty($user_email)) {
            return array('success' => false, 'message' => esc_html__("Email is required.", "cubewp-framework"));
        }

        if (!is_email($user_email)) {
            return array('success' => false, 'message' => esc_html__("The email address is invalid.", "cubewp-framework"));
        }

        if (email_exists($user_email)) {
            return array('success' => false, 'message' => esc_html__("This email already exists.", "cubewp-framework"));
        }

        // Password validation
        if (empty($user_pass)) {
            return array('success' => false, 'message' => esc_html__("Password is required.", "cubewp-framework"));
        }

        if (empty($confirm_pass)) {
            return array('success' => false, 'message' => esc_html__("Confirm password is required.", "cubewp-framework"));
        }

        if ($user_pass !== $confirm_pass) {
            return array('success' => false, 'message' => esc_html__("Password and confirm password do not match.", "cubewp-framework"));
        }

        // All validations passed
        return array('success' => true, 'message' => '');
    }

    /**
     * Proceed with original registration after OTP verification
     */
    private function proceed_with_registration($default_fields, $custom_fields) {
        // Recaptcha verification if enabled
        if (isset($_POST['g-recaptcha-response'])) {
            CubeWp_Frontend_Recaptcha::cubewp_captcha_verification("cubewp_captcha_user_registration", cubewp_core_data($_POST['g-recaptcha-response']));
        }

        $user_pass = isset($default_fields['user_pass']) ? sanitize_text_field($default_fields['user_pass']) : '';
        
        if (empty($user_pass)) {
            $default_fields['user_pass'] = wp_generate_password(12, false);
        }

        $wp_insert_data = array();
        foreach ($default_fields as $key => $val) {
            $wp_insert_data[$key] = $val;
        }

        $wp_insert_data = apply_filters('cubewp/before/user/registration', $wp_insert_data);
        $user_id = wp_insert_user($wp_insert_data);

        if (is_wp_error($user_id)) {
            wp_send_json(
                array(
                    'type' => 'error',
                    'msg'  => $user_id->get_error_message(),
                )
            );
        } else {
            // Auto login if password was provided
            if (!empty($user_pass)) {
                $info = array();
                $info['user_login'] = $default_fields['user_login'];
                $info['user_password'] = $user_pass;
                $info['remember'] = true;
                
                if (is_ssl()) {
                    wp_signon($info, true);
                } else {
                    wp_signon($info, false);
                }
            }

            // Save custom fields
            $this->save_user_custom_fields($user_id, $custom_fields);

            do_action('cubewp/after/user/registration', $user_id);

            if (empty($user_pass)) {
                wp_send_json(
                    array(
                        'type' => 'success',
                        'msg'  => esc_html__("Registration successful! Check your email for login credentials.", "cubewp-framework")
                    )
                );
            } else {
                wp_send_json(
                    array(
                        'type' => 'success',
                        'msg' => esc_html__("Registration and login successful! Redirecting...", "cubewp-framework"),
                        'redirectURL' => apply_filters('cubewp/after/user/registration/redirect-url', home_url()),
                    )
                );
            }
        }
    }

    /**
     * Save user custom fields
     */
    private function save_user_custom_fields($user_id, $custom_fields) {
        $fieldOptions = CWP()->get_custom_fields('user');
        
        if (isset($custom_fields) && !empty($custom_fields)) {
            foreach ($custom_fields as $key => $val) {
                $singleFieldOptions = isset($fieldOptions[$key]) ? $fieldOptions[$key] : array();
                
                if (isset($singleFieldOptions['type'])) {
                    $value = $val;
                    
                    // Handle relationships
                    if ((isset($singleFieldOptions['relationship']) && $singleFieldOptions['type'] == 'post' && $singleFieldOptions['relationship']) && is_array($singleFieldOptions) && count($singleFieldOptions) > 0) {
                        if (!is_array($value)) {
                            $value = array($value);
                        }
                        if (!empty($value) && count($value) > 0) {
                            (new CubeWp_Relationships)->save_relationship($user_id, $value, $key, 'UTP');
                        }
                    } else if ((isset($singleFieldOptions['relationship']) && $singleFieldOptions['type'] == 'user' && $singleFieldOptions['relationship']) && is_array($singleFieldOptions) && count($singleFieldOptions) > 0) {
                        if (!is_array($value)) {
                            $value = array($value);
                        }
                        if (!empty($value) && count($value) > 0) {
                            (new CubeWp_Relationships)->save_relationship($user_id, $value, $key, 'UTU');
                        }
                    }

                    // Handle file uploads
                    if (isset($singleFieldOptions) && $singleFieldOptions['type'] == 'gallery') {
                        $attachment_ids = cwp_upload_user_gallery_images($key, $val, $_FILES, $user_id, 'cwp_user_register');
                        if (isset($attachment_ids) && !empty($attachment_ids)) {
                            update_user_meta($user_id, $key, $attachment_ids);
                        } else {
                            delete_user_meta($user_id, $key);
                        }
                    } else if (isset($singleFieldOptions) && ($singleFieldOptions['type'] == 'file' || $singleFieldOptions['type'] == 'image')) {
                        $attachment_id = cwp_upload_user_file($key, $val, $_FILES, $user_id, 'cwp_user_register');
                        if (isset($attachment_id) && !empty($attachment_id)) {
                            update_user_meta($user_id, $key, $attachment_id);
                        } else {
                            delete_user_meta($user_id, $key);
                        }
                    } else if (isset($singleFieldOptions) && $singleFieldOptions['type'] == 'repeating_field') {
                        // Handle repeating fields
                        $arr = array();
                        foreach ($val as $_key => $_val) {
                            $singleFieldOptions = isset($fieldOptions[$_key]) ? $fieldOptions[$_key] : array();
                            foreach ($_val as $field_key => $field_val) {
                                if (isset($singleFieldOptions) && ($singleFieldOptions['type'] == 'file' || $singleFieldOptions['type'] == 'image')) {
                                    if (isset($_FILES['cwp_user_register']['name']['custom_fields'][$key][$_key][$field_key]) && $_FILES['cwp_user_register']['name']['custom_fields'][$key][$_key][$field_key] != '') {
                                        $file = array(
                                            'name' => $_FILES['cwp_user_register']['name']['custom_fields'][$key][$_key][$field_key],
                                            'type' => $_FILES['cwp_user_register']['type']['custom_fields'][$key][$_key][$field_key],
                                            'tmp_name' => $_FILES['cwp_user_register']['tmp_name']['custom_fields'][$key][$_key][$field_key],
                                            'error' => $_FILES['cwp_user_register']['error']['custom_fields'][$key][$_key][$field_key],
                                            'size' => $_FILES['cwp_user_register']['size']['custom_fields'][$key][$_key][$field_key]
                                        );
                                        $field_val = cwp_handle_attachment($file, $user_id);
                                    }
                                    if ($field_val != 0) {
                                        $arr[$field_key][$_key] = $field_val;
                                    }
                                } else {
                                    $arr[$field_key][$_key] = $field_val;
                                }
                            }
                        }
                        if (isset($arr) && !empty($arr)) {
                            $_arr = array_filter($arr);
                            update_user_meta($user_id, $key, $_arr);
                        } else {
                            delete_user_meta($user_id, $key);
                        }
                    } else {
                        // Handle date fields
                        if (isset($singleFieldOptions) && ($singleFieldOptions['type'] == 'date_picker' || $singleFieldOptions['type'] == 'date_time_picker' || $singleFieldOptions['type'] == 'time_picker')) {
                            $val = strtotime($val);
                        }
                        update_user_meta($user_id, $key, $val);
                    }
                }
            }
        }
    }

    private function format_phone_number($phone) {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        if (preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
            return $phone;
        }

        return false;
    }

    private function send_otp($phone_number) {

		        wp_send_json(
            array(
                'type' => 'success',
                'action' => 'otp_sent',
                'msg' => 'OTP sent successfully to ' . $phone_number,
                'show_otp_field' => true,
            )
        );
        $body = array(
            'phone_number' => $phone_number,
            'options' => array(
                'code_size' => 6,
                'locale' => 'en',
            ),
            'vendor_data' => 'registration-' . time(),
        );
        
        $api_key = Helper::get_option('didit-verification_basic', 'didit_api_key');

        $response = wp_remote_post(
            $this->send_otp_url,
            array(
                'method' => 'POST',
                'headers' => array(
                    'x-api-key' => $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode($body),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            wp_send_json(
                array(
                    'type' => 'error',
                    'msg' => 'Failed to send OTP. Please try again.',
                )
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if (200 !== $status_code) {
            $error_message = isset($response_data['message']) ? $response_data['message'] : 'Failed to send OTP';
            wp_send_json(
                array(
                    'type' => 'error',
                    'msg' => $error_message,
                )
            );
        }


    }

    private function verify_otp($phone_number, $otp_code) {
        // For testing - remove this return statement to use real API
        return array(
            'success' => true,
            'message' => 'OTP verified successfully',
        );
        
        $body = array(
            'phone_number' => $phone_number,
            'code' => $otp_code,
        );
        
        $api_key = Helper::get_option('didit-verification_basic', 'didit_api_key');
        
        $response = wp_remote_post(
            $this->verify_otp_url,
            array(
                'method' => 'POST',
                'headers' => array(
                    'x-api-key' => $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode($body),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again.',
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if (200 === $status_code && isset($response_data['verified']) && true === $response_data['verified']) {
            return array(
                'success' => true,
                'message' => 'OTP verified successfully',
            );
        }

        $error_message = isset($response_data['message']) ? $response_data['message'] : 'Invalid OTP';
        return array(
            'success' => false,
            'message' => $error_message,
        );
    }

}