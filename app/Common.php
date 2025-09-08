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

	public function widget_output( $empty, $args ) {
		if( ! empty( $args['staybnb_post_type'] ) ) {
			return '<div class="staybnb-widget">Post type: ' . esc_html( $args['staybnb_post_type'] ) . '</div>';
		}
		return $empty;
	}
}