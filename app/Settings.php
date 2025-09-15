<?php
/**
 * All settings related functions
 */
namespace Codexpert\DiditVerification\App;
use Codexpert\DiditVerification\Helper;
use Codexpert\Plugin\Base;
use Codexpert\Plugin\Settings as Settings_API;

/**
 * @package Plugin
 * @subpackage Settings
 * @author Codexpert <hi@codexpert.io>
 */
class Settings extends Base {

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
	
	public function init_menu() {
		
		$site_config = [
			'PHP Version'				=> PHP_VERSION,
			'WordPress Version' 		=> get_bloginfo( 'version' ),
			'WooCommerce Version'		=> is_plugin_active( 'woocommerce/woocommerce.php' ) ? get_option( 'woocommerce_version' ) : 'Not Active',
			'Memory Limit'				=> defined( 'WP_MEMORY_LIMIT' ) && WP_MEMORY_LIMIT ? WP_MEMORY_LIMIT : 'Not Defined',
			'Debug Mode'				=> defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled',
			'Active Plugins'			=> get_option( 'active_plugins' ),
		];


		$register_form_data = get_option( 'cwp_user_register_form' );
		$author_fields     = array();
		$subscriber_fields = array();

		if (
			! empty( $register_form_data['author']['groups'] ) &&
			is_array( $register_form_data['author']['groups'] )
		) {
			$author_group  		= reset( $register_form_data['author']['groups'] );
			$author_fields_data = isset( $author_group['fields'] ) ? $author_group['fields'] : array();

			foreach ( $author_fields_data as $field_key => $field_data ) {
				if ( isset( $field_data['label'] ) && strpos( $field_key, 'cwp_field_' ) === 0 ) {
					$author_fields[ $field_key ] = $field_data['label'];
				}
			}
		}

		if (
			! empty( $register_form_data['subscriber']['groups'] ) &&
			is_array( $register_form_data['subscriber']['groups'] )
		) {
			$subscriber_group  			= reset( $register_form_data['subscriber']['groups'] );
			$subscriber_fields_data 	= isset( $subscriber_group['fields'] ) ? $subscriber_group['fields'] : array();

			foreach ( $subscriber_fields_data as $field_key => $field_data ) {
				if ( isset( $field_data['label'] ) && strpos( $field_key, 'cwp_field_' ) === 0 ) {
					$subscriber_fields[ $field_key ] = $field_data['label'];
				}
			}
		}

		$settings = [
			'id'            => $this->slug,
			'label'         => $this->name,
			'title'         => "{$this->name} v{$this->version}",
			'header'        => $this->name,
			// 'parent'     => 'woocommerce',
			// 'priority'   => 10,
			// 'capability' => 'manage_options',
			// 'icon'       => 'dashicons-wordpress',
			// 'position'   => 25,
			// 'topnav'	=> true,
			'sections'      => [
				'didit-verification_basic'	=> [
					'id'        => 'didit-verification_basic',
					'label'     => __( 'Basic Settings', 'didit-verification' ),
					'icon'      => 'dashicons-admin-tools',
					// 'color'		=> '#4c3f93',
					'sticky'	=> false,
					'fields'    => [
						'didit_api_key' => [
							'id'        => 'didit_api_key',
							'label'     => __( 'API Key', 'didit-verification' ),
							'type'      => 'text',
							'desc'      => __( 'This is a api key.', 'didit-verification' ),
							// 'class'     => '',
							'default'   => 'Hello World!',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'didit_workflow_id' => [
							'id'        => 'didit_workflow_id',
							'label'     => __( 'Workflow ID', 'didit-verification' ),
							'type'      => 'text',
							'desc'      => __( 'This is a workflow id.', 'didit-verification' ),
							// 'class'     => '',
							'default'   => 'Hello World!',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'author_phone' => [
							'id'      => 'author_phone',
							'label'     => __( 'Select author phone', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'This is a select field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => $author_fields,
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
						],
						'author_otp' => [
							'id'      => 'author_otp',
							'label'     => __( 'Select author otp', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'This is a select field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => $author_fields,
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
						],
						'add_listing_page' => [
							'id'      => 'add_listing_page',
							'label'     => __( 'Select add listing page', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'This is a select field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => Helper::get_posts(['post_type' => 'page']),
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
						],
					]
				]
			],
		];

		new Settings_API( $settings );
	}
}