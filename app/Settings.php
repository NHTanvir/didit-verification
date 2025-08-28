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
							'label'     => __( 'Text Field', 'didit-verification' ),
							'type'      => 'text',
							'desc'      => __( 'This is a text field.', 'didit-verification' ),
							// 'class'     => '',
							'default'   => 'Hello World!',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_number' => [
							'id'      => 'sample_number',
							'label'     => __( 'Number Field', 'didit-verification' ),
							'type'      => 'number',
							'desc'      => __( 'This is a number field.', 'didit-verification' ),
							// 'class'     => '',
							'default'   => 10,
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_email' => [
							'id'      => 'sample_email',
							'label'     => __( 'Email Field', 'didit-verification' ),
							'type'      => 'email',
							'desc'      => __( 'This is an email field.', 'didit-verification' ),
							// 'class'     => '',
							'default'   => 'john@doe.com',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_url' => [
							'id'      => 'sample_url',
							'label'     => __( 'URL Field', 'didit-verification' ),
							'type'      => 'url',
							'desc'      => __( 'This is a url field.', 'didit-verification' ),
							// 'class'     => '',
							'default'   => 'https://johndoe.com',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_password' => [
							'id'      => 'sample_password',
							'label'     => __( 'Password Field', 'didit-verification' ),
							'type'      => 'password',
							'desc'      => __( 'This is a password field.', 'didit-verification' ),
							// 'class'     => '',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
							'default'   => 'uj34h'
						],
						'sample_textarea' => [
							'id'      => 'sample_textarea',
							'label'     => __( 'Textarea Field', 'didit-verification' ),
							'type'      => 'textarea',
							'desc'      => __( 'This is a textarea field.', 'didit-verification' ),
							// 'class'     => '',
							'columns'   => 24,
							'rows'      => 5,
							'default'   => 'lorem ipsum dolor sit amet',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_radio' => [
							'id'      => 'sample_radio',
							'label'     => __( 'Radio Field', 'didit-verification' ),
							'type'      => 'radio',
							'desc'      => __( 'This is a radio field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'item_1'  => 'Item One',
								'item_2'  => 'Item Two',
								'item_3'  => 'Item Three',
							],
							'default'   => 'item_2',
							'disabled'  => false, // true|false
						],
						'sample_select' => [
							'id'      => 'sample_select',
							'label'     => __( 'Select Field', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'This is a select field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => 'option_2',
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
						],
						'sample_multiselect' => [
							'id'      => 'sample_multiselect',
							'label'     => __( 'Multi-select Field', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'This is a multiselect field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2', 'option_3' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
						],
						'sample_checkbox' => [
							'id'      => 'sample_checkbox',
							'label'     => __( 'Checkbox Field', 'didit-verification' ),
							'type'      => 'checkbox',
							'desc'      => __( 'This is a checkbox field.', 'didit-verification' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => 'on'
						],
						'sample_multicheck' => [
							'id'      => 'sample_multicheck',
							'label'     => __( 'Multi-check Field', 'didit-verification' ),
							'type'      => 'checkbox',
							'desc'      => __( 'This is a multi-check field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
						],
						'sample_switch' => [
							'id'      => 'sample_switch',
							'label'     => __( 'Switch Field', 'didit-verification' ),
							'type'      => 'switch',
							'desc'      => __( 'This is a switch field.', 'didit-verification' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => 'on'
						],
						'sample_multiswitch' => [
							'id'      => 'sample_multiswitch',
							'label'     => __( 'Multi-switch Field', 'didit-verification' ),
							'type'      => 'switch',
							'desc'      => __( 'This is a multi-switch field.', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
						],
						'sample_range' => [
							'id'      => 'sample_range',
							'label'     => __( 'Range Field', 'didit-verification' ),
							'type'      => 'range',
							'desc'      => __( 'This is a range field.', 'didit-verification' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'min'		=> 0,
							'max'		=> 16,
							'step'		=> 2,
							'default'   => 4,
						],
						'sample_date' => [
							'id'      => 'sample_date',
							'label'     => __( 'Date Field', 'didit-verification' ),
							'type'      => 'date',
							'desc'      => __( 'This is a date field.', 'didit-verification' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => '1971-12-16',
						],
						'sample_time' => [
							'id'      => 'sample_time',
							'label'     => __( 'Time Field', 'didit-verification' ),
							'type'      => 'time',
							'desc'      => __( 'This is a time field.', 'didit-verification' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => '15:45',
						],
						'sample_color' => [
							'id'      => 'sample_color',
							'label'     => __( 'Color Field', 'didit-verification' ),
							'type'      => 'color',
							'desc'      => __( 'This is a color field.', 'didit-verification' ),
							// 'class'     => '',
							// 'default'   => '#f0f'
						],
						'sample_wysiwyg' => [
							'id'      => 'sample_wysiwyg',
							'label'     => __( 'WYSIWYG Field', 'didit-verification' ),
							'type'      => 'wysiwyg',
							'desc'      => __( 'This is a wysiwyg field.', 'didit-verification' ),
							// 'class'     => '',
							'width'     => '100%',
							'rows'      => 5,
							'teeny'     => true,
							'text_mode'     => false, // true|false
							'media_buttons' => false, // true|false
							'default'       => 'Hello World'
						],
						'sample_file' => [
							'id'      => 'sample_file',
							'label'     => __( 'File Field' ),
							'type'      => 'file',
							'upload_button'     => __( 'Choose File', 'didit-verification' ),
							'select_button'     => __( 'Select File', 'didit-verification' ),
							'desc'      => __( 'This is a file field.', 'didit-verification' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => 'http://example.com/sample/file.txt'
						],
					]
				],
				'didit-verification_advanced'	=> [
					'id'        => 'didit-verification_advanced',
					'label'     => __( 'Advanced Settings', 'didit-verification' ),
					'icon'      => 'dashicons-admin-generic',
					// 'color'		=> '#d30c5c',
					'sticky'	=> false,
					'fields'    => [
						'sample_select3' => [
							'id'      => 'sample_select3',
							'label'     => __( 'Select with Chosen', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Chosen plugin enabled. <a href="https://harvesthq.github.io/chosen/" target="_blank">[See more]</a>', 'didit-verification' ),
							// 'class'     => '',
							'options'   => Helper::get_posts( [ 'post_type' => 'page' ], false, true ),
							'default'   => 2,
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
							'chosen'    => true
						],
						'sample_multiselect3' => [
							'id'      => 'sample_multiselect3',
							'label'     => __( 'Multi-select with Chosen', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Chosen plugin enabled. <a href="https://harvesthq.github.io/chosen/" target="_blank">[See more]</a>', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2', 'option_3' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
							'chosen'    => true
						],
						'sample_select2' => [
							'id'      => 'sample_select2',
							'label'     => __( 'Select with Select2', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Select2 plugin enabled. <a href="https://select2.org/" target="_blank">[See more]</a>', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => 'option_2',
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
							'select2'   => true
						],
						'sample_multiselect2' => [
							'id'      => 'sample_multiselect2',
							'label'     => __( 'Multi-select with Select2', 'didit-verification' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Select2 plugin enabled. <a href="https://select2.org/" target="_blank">[See more]</a>', 'didit-verification' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2', 'option_3' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
							'select2'   => true
						],
						'sample_group' => [
							'id'      => 'sample_group',
							'label'     => __( 'Field Group' ),
							'type'      => 'group',
							'desc'      => __( 'A group of fields.', 'didit-verification' ),
							'items'     => [
								'sample_group_select1' => [
									'id'      => 'sample_group_select1',
									'label'     => __( 'First Item', 'didit-verification' ),
									'type'      => 'select',
									'options'   => [
										'option_1'  => 'Option One',
										'option_2'  => 'Option Two',
										'option_3'  => 'Option Three',
									],
									'default'   => 'option_2',
								],
								'sample_group_select2' => [
									'id'      => 'sample_group_select2',
									'label'     => __( 'Second Item', 'didit-verification' ),
									'type'      => 'select',
									'options'   => [
										'option_1'  => 'Option One',
										'option_2'  => 'Option Two',
										'option_3'  => 'Option Three',
									],
									'default'   => 'option_1',
								],
								'sample_group_select3' => [
									'id'      => 'sample_group_select3',
									'label'     => __( 'Third Item', 'didit-verification' ),
									'type'      => 'select',
									'options'   => [
										'option_1'  => 'Option One',
										'option_2'  => 'Option Two',
										'option_3'  => 'Option Three',
									],
									'default'   => 'option_3',
								],
							],
						],
						'sample_conditional' => [
							'id'      => 'sample_conditional',
							'label'     => __( 'Conditional Field', 'didit-verification' ),
							'type'      => 'select',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'desc'      => __( 'Shows up if the third option in the  \'Field Group\' above is set as \'Option Two\'', 'didit-verification' ),
							'default'   => 'option_2',
							'condition'	=> [
								'key'		=> 'sample_group_select3',
								'value'		=> 'option_2',
								'compare'	=> '==',
							]
						],
						'sample_repeater'	=> [
							'id'		=> 'sample_repeater',
							'label'		=> __( 'Sample Repeater' ),
							'type'		=> 'repeater',
							'items'		=> [
								'text_repeat' => [
									'id'		=> 'text_repeat',
									'label'		=> __( 'Repeat Text Field', 'didit-verification' ),
									'type'		=> 'text',
									'placeholder'=> __( 'Repeat Text', 'didit-verification' ),
									'desc'		=> __( 'This field will be repeated.', 'didit-verification' ),
								],
								'number_repeat' => [
									'id'		=> 'number_repeat',
									'label'		=> __( 'Repeat Number Field', 'didit-verification' ),
									'type'		=> 'number',
									'placeholder'=> __( 'Repeat Number', 'didit-verification' ),
									'desc'		=> __( 'This field will be repeated.', 'didit-verification' ),
								],
							]
						],
						'sample_tabs' => [
							'id'      => 'sample_tabs',
							'label'     => __( 'Sample Tabs' ),
							'type'      => 'tabs',
							'items'     => [
								'sample_tab1' => [
									'id'      => 'sample_tab1',
									'label'     => __( 'First Tab', 'didit-verification' ),
									'fields'    => [
										'sample_tab1_email' => [
											'id'      => 'sample_tab1_email',
											'label'     => __( 'Tab Email Field', 'didit-verification' ),
											'type'      => 'email',
											'desc'      => __( 'This is an email field.', 'didit-verification' ),
											// 'class'     => '',
											'default'   => 'john@doe.com',
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
										'sample_tab1_url' => [
											'id'      => 'sample_tab1_url',
											'label'     => __( 'Tab URL Field', 'didit-verification' ),
											'type'      => 'url',
											'desc'      => __( 'This is a url field.', 'didit-verification' ),
											// 'class'     => '',
											'default'   => 'https://johndoe.com',
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
									],
								],
								'sample_tab2' => [
									'id'      => 'sample_tab2',
									'label'     => __( 'Second Tab', 'didit-verification' ),
									'fields'    => [
										'sample_tab2_text' => [
											'id'        => 'sample_tab2_text',
											'label'     => __( 'Tab Text Field', 'didit-verification' ),
											'type'      => 'text',
											'desc'      => __( 'This is a text field.', 'didit-verification' ),
											// 'class'     => '',
											'default'   => 'Hello World!',
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
										'sample_tab2_number' => [
											'id'      => 'sample_tab2_number',
											'label'     => __( 'Tab Number Field', 'didit-verification' ),
											'type'      => 'number',
											'desc'      => __( 'This is a number field.', 'didit-verification' ),
											// 'class'     => '',
											'default'   => 10,
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
									],
								],
							],
						],
					]
				],
				'didit-verification_tools'	=> [
					'id'        => 'didit-verification_tools',
					'label'     => __( 'Tools', 'didit-verification' ),
					'icon'      => 'dashicons-hammer',
					'sticky'	=> false,
					'fields'    => [
						'enable_debug' => [
							'id'      	=> 'enable_debug',
							'label'     => __( 'Enable Debug', 'didit-verification' ),
							'type'      => 'switch',
							'desc'      => __( 'Enable this if you face any CSS or JS related issues.', 'didit-verification' ),
							'disabled'  => false,
						],
						'report' => [
							'id'      => 'report',
							'label'     => __( 'Report', 'didit-verification' ),
							'type'      => 'textarea',
							'desc'     	=> '<button id="didit-verification_report-copy" class="button button-primary"><span class="dashicons dashicons-admin-page"></span></button>',
							'columns'   => 24,
							'rows'      => 10,
							'default'   => json_encode( $site_config, JSON_PRETTY_PRINT ),
							'readonly'  => true,
						],
					]
				],
				'didit-verification_table' => [
					'id'        => 'didit-verification_table',
					'label'     => __( 'Table', 'didit-verification' ),
					'icon'      => 'dashicons-editor-table',
					// 'color'		=> '#28c9ee',
					'hide_form'	=> true,
					'template'  => Didit_Verification_DIR . '/views/settings/table.php',
				],
			],
		];

		new Settings_API( $settings );
	}
}