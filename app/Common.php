<?php
/**
 * All common functions to load in both admin and front
 */
namespace Codexpert\DiditVerification\App;
use Codexpert\Plugin\Base;
use Codexpert\DiditVerification\Helper;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Common
 * @author Codexpert <hi@codexpert.io>
 */
class Common extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function form_output( $output, $parameters ) {
		$current_user = wp_get_current_user();

		if ( ! $current_user || ! isset( $current_user->ID ) ) {
			return '';
		}

		if ( current_user_can( 'manage_options' ) ) {
			return $output;
		}

		if ( in_array( 'author', (array) $current_user->roles, true ) ) {
			$verified = get_user_meta( $current_user->ID, 'didit_verified', true );

			if ( 'approved' === $verified ) {
				return $output;
			}

			return Helper::get_template( 'didit' );
		}

		$verified = get_user_meta( $current_user->ID, 'didit_verified', true );

		if ( 'approved' === $verified ) {
			return $output;
		}

		return '<p>You are not an author and not verified: no access.</p>';
	}
	public function webhook_init() {

		if ( ( $_GET['didit_webhook'] ?? '' ) !== '1' ) {
			return;
		}

		$secret = Helper::get_option( 'didit-verification_basic', 'didit_webhook_secret' );
		$body   = file_get_contents( 'php://input' );
		$sig 	= $_SERVER['HTTP_X_SIGNATURE'] ?? '';

		if ( ! hash_equals( hash_hmac( 'sha256', $body, $secret ), $sig ) ) {
			wp_die( esc_html__( 'Invalid signature', 'didit-verification' ), 401 );
		}

		$data = json_decode( $body, true );

		if ( ! empty( $data['metadata']['wp_user_id'] ) ) {
			$uid = absint( $data['metadata']['wp_user_id'] );

			update_user_meta( $uid, 'didit_status', sanitize_text_field( $data['status'] ?? '' ) );

			if ( ( $data['status'] ?? '' ) === 'Approved' ) {
				update_user_meta( $uid, 'didit_verified', 'approved' );
			}
		}

		wp_send_json_success( array( 'message' => esc_html__( 'ok', 'didit-verification' ) ) );
		exit;
	}
}