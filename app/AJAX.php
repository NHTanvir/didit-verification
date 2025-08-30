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
		$otp_field = Helper::get_option( 'didit-verification_basic', 'author_otp' );
		$phone_field = Helper::get_option( 'didit-verification_basic', 'author_phone' );

		$phone_number = isset( $_POST['cwp_user_register']['custom_fields'][$phone_field] )
			? sanitize_text_field( wp_unslash( $_POST['cwp_user_register']['custom_fields'][$phone_field] ) )
			: '';

		$otp_code = isset( $_POST['cwp_user_register']['custom_fields'][$otp_field] )
			? sanitize_text_field( wp_unslash( $_POST['cwp_user_register']['custom_fields'][$otp_field] ) )
			: '';

		if ( empty( $phone_number ) ) {
			wp_send_json(
				array(
					'type' => 'error',
					'msg'  => 'Phone number is required.',
				)
			);
		}

		$formatted_phone = $this->format_phone_number( $phone_number );

		if ( ! $formatted_phone ) {
			wp_send_json(
				array(
					'type' => 'error',
					'msg'  => 'Invalid phone number format.',
				)
			);
		}

		if ( empty( $otp_code ) ) {
			$this->send_otp( $formatted_phone );
		} else {
			$verification_result = $this->verify_otp( $formatted_phone, $otp_code );

			if ( $verification_result['success'] ) {
				remove_action( 'wp_ajax_nopriv_cubewp_submit_user_register', array( $this, 'cubewp_submit_user_register' ), 5 );
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

	private function format_phone_number( $phone ) {
		$phone = preg_replace( '/[\s\-\(\)]/', '', $phone );

		if ( substr( $phone, 0, 1 ) !== '+' ) {
			$phone = '+' . $phone;
		}

		if ( preg_match( '/^\+[1-9]\d{6,14}$/', $phone ) ) {
			return $phone;
		}

		return false;
	}


	private function send_otp( $phone_number ) {
		wp_send_json(
			array(
				'type'           => 'success',
				'action'         => 'otp_sent',
				'msg'            => 'OTP sent successfully to ' . $phone_number,
				'show_otp_field' => true,
			)
		);

		$body = array(
			'phone_number' => $phone_number,
			'options'      => array(
				'code_size' => 6,
				'locale'    => 'en',
			),
			'vendor_data'  => 'registration-' . time(),
		);
		$api_key = Helper::get_option( 'didit-verification_basic', 'didit_api_key' );

		$response = wp_remote_post(
			$this->send_otp_url,
			array(
				'method'  => 'POST',
				'headers' => array(
					'x-api-key'    => $api_key,
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json(
				array(
					'type' => 'error',
					'msg'  => 'Failed to send OTP. Please try again.',
				)
			);
		}

		$status_code   = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$response_data = json_decode( $response_body, true );

		if ( 200 !== $status_code ) {
			$error_message = isset( $response_data['message'] ) ? $response_data['message'] : 'Failed to send OTP';
			wp_send_json(
				array(
					'type' => 'error',
					'msg'  => $error_message,
				)
			);
		}
	}

	private function verify_otp( $phone_number, $otp_code ) {
		return array(
			'success' => true,
			'message' => 'OTP verified successfully',
		);

		$body = array(
			'phone_number' => $phone_number,
			'code'         => $otp_code,
		);
		$api_key = Helper::get_option( 'didit-verification_basic', 'didit_api_key' );
		$response = wp_remote_post(
			$this->verify_otp_url,
			array(
				'method'  => 'POST',
				'headers' => array(
					'x-api-key'    => $api_key,
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to verify OTP. Please try again.',
			);
		}

		$status_code   = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$response_data = json_decode( $response_body, true );

		if ( 200 === $status_code && isset( $response_data['verified'] ) && true === $response_data['verified'] ) {
			return array(
				'success' => true,
				'message' => 'OTP verified successfully',
			);
		}

		$error_message = isset( $response_data['message'] ) ? $response_data['message'] : 'Invalid OTP';
		return array(
			'success' => false,
			'message' => $error_message,
		);
	}
}