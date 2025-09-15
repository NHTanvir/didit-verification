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
				return $output . '<p>Author verified: access granted.</p>';
			}

			return Helper::get_template( 'didit' );
		}

		$verified = get_user_meta( $current_user->ID, 'didit_verified', true );

		if ( 'approved' === $verified ) {
			return $output;
		}

		return '<p>You are not an author and not verified: no access.</p>';
	}
}