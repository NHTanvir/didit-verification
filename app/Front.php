<?php
/**
 * All public facing functions
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
 * @subpackage Front
 * @author Codexpert <hi@codexpert.io>
 */
class Front extends Base {

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

	public function head() {}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'Didit_Verification_DEBUG' ) && Didit_Verification_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", Didit_Verification ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", Didit_Verification ), [ 'jquery' ], time(), true );
		
		$otp_field = Helper::get_option( 'didit-verification_basic', 'author_otp' );
		$phone_field = Helper::get_option( 'didit-verification_basic', 'author_phone' );
		
		$localized = [
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'	=> wp_create_nonce(),
			'otp_field' => $otp_field,
			'phone_field' => $phone_field
		];
		wp_localize_script( $this->slug, 'Didit_Verification', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function modal() {
		echo '
		<div id="didit-verification-modal" style="display: none">
			<img id="didit-verification-modal-loader" src="' . esc_attr( Didit_Verification_ASSET . '/img/loader.gif' ) . '" />
		</div>';
	}
}