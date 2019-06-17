<?php

namespace REALTY_BLOC_LOG;

use REALTY_BLOC_LOG\Core\SettingAPI;
use REALTY_BLOC_LOG\Core\Utility\Post;
use REALTY_BLOC_LOG\Core\Utility\User;

/**
 * Class Settings
 * @see https://github.com/tareq1988/wordpress-settings-api-class
 */
class Settings {
	/**
	 * Setting Page
	 *
	 * @var mixed
	 */
	public $setting;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Admin_Setting_Api constructor.
	 */
	public function __construct() {
		/**
		 * Set Admin Setting
		 */
		add_action( 'admin_init', array( $this, 'wedevs_admin_init' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function wedevs_plugin_page() {

		echo '<div class="wrap">';
		settings_errors();

		$this->setting->show_navigation();
		$this->setting->show_forms();

		echo '</div>';
	}

	/**
	 * Registers settings section and fields
	 */
	public function wedevs_admin_init() {
		$sections = array(
			array(
				'id'    => 'rbl_user_event',
				'desc'  => __( 'User Event Log Setting', 'realty-bloc-log' ),
				'title' => __( 'User Event', 'realty-bloc-log' )
			),
			array(
				'id'    => 'rbl_forms_event',
				'title' => __( 'Form Submit Event', 'realty-bloc-log' )
			)
		);

		$fields = array(
			'rbl_user_event'           => array(
				array(
					'name'    => 'register',
					'label'   => __( 'Register User', 'realty-bloc-log' ),
					'desc'    => __( 'save user log when user is registered', 'realty-bloc-log' ),
					'type'    => 'checkbox',
					'default' => 'on'
				),
				array(
					'name'    => 'login',
					'label'   => __( 'Login', 'realty-bloc-log' ),
					'desc'    => __( 'save user log when login success', 'realty-bloc-log' ),
					'type'    => 'checkbox',
					'default' => 'on'
				),
				array(
					'name'    => 'fail',
					'label'   => __( 'Fail Login', 'realty-bloc-log' ),
					'desc'    => __( 'save user log when user login is failed', 'realty-bloc-log' ),
					'type'    => 'checkbox',
					'default' => 'on'
				),
				array(
					'name'    => 'forget',
					'label'   => __( 'Forget Password', 'realty-bloc-log' ),
					'desc'    => __( 'save user log when user want to forget Password', 'realty-bloc-log' ),
					'type'    => 'checkbox',
					'default' => 'on'
				),
				array(
					'name'    => 'role',
					'label'   => __( 'Users Role', 'realty-bloc-log' ),
					'desc'    => __( 'which User role do you want to Save Log ?', 'realty-bloc-log' ),
					'type'    => 'multicheck',
					'options' => User::get_role_list(),
					'default' => array(
						'subscriber' => 'subscriber'
					)
				)
			),
			'rbl_forms_event'     => array(
				array(
					'name'    => 'form',
					'label'   => __( 'Form List', 'realty-bloc-log' ),
					'desc'    => __( 'which Form do you want to Save Log ?', 'realty-bloc-log' ),
					'type'    => 'multicheck',
					'options' => Post::get_list_post(array(
						'post_type' => 'wpforms'
					))
				)
			)
		);

		$this->setting = new SettingAPI();

		//set sections and fields
		$this->setting->set_sections( $sections );
		$this->setting->set_fields( $fields );

		//initialize them
		$this->setting->admin_init();
	}

}