<?php
/**
 * Plugin Name: Realty Bloc Log System
 * Description: A WordPress Plugin For Realty Bloc Log System
 * Plugin URI:  https://veronaLabs.com
 * Version:     1.0.9
 * Author:      VeronaLabs
 * Author URI:  https://veronaLabs.com
 * License:     MIT
 * Text Domain: realty-bloc-log
 * Domain Path: /languages
 */

class REALTY_BLOC_LOG {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @status Core
	 */
	protected static $_instance = null;

	/**
	 * Plugin ENVIRONMENT
	 *
	 * @var string
	 * @default production
	 * @status Core
	 */
	public static $ENVIRONMENT = 'development';

	/**
	 * Use Template Engine
	 * if you want use template Engine Please add dir name
	 *
	 * @var string / dir name
	 * @status Core
	 */
	public static $Template_Engine = 'wp-realty-bloc-log';

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_url;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_path;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_version;

	/**
	 * Plugin Option Store
	 *
	 * @var array
	 * @status Optional
	 */
	public static $option;

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.13
	 * @return  object of this class
	 */
	public static function instance() {
		null === self::$_instance and self::$_instance = new self;
		return self::$_instance;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @return  void
	 */
	public function init() {

		//Get plugin Data information
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__ );

		//Get Option
		self::$option = array(
			'user_event' => ( get_option( 'rbl_user_event', false ) != false ? get_option( 'rbl_user_event' ) : array() ),
			'form_event' => ( get_option( 'rbl_forms_event', false ) != false ? get_option( 'rbl_forms_event' ) : array() )
		);

		//Get Plugin Version
		self::$plugin_version = $plugin_data['Version'];

		//Set Variable
		self::$plugin_url  = plugins_url( '', __FILE__ );
		self::$plugin_path = plugin_dir_path( __FILE__ );

		//Set Text Domain
		$this->load_language( 'realty-bloc-log' );

		//Load Composer
		include_once dirname( __FILE__ ) . '/vendor/autoload.php';

		// Load Template function
		include_once dirname( __FILE__ ) . '/functions.php';

		//Load Class
		$autoload = array(
			'Settings',
			'Admin',
			'Front',
			'Ajax',
			'Event',
			'Event\\view',
			'Event\\form',
			'Event\\login'
		);
		foreach ( $autoload as $class ) {
			$class_name = '\REALTY_BLOC_LOG\\' . $class;
			new $class_name;
		}

		//Check $ENVIRONMENT Mode
		if ( self::$ENVIRONMENT == "development" ) {
			new \REALTY_BLOC_LOG\Core\Debug();
		}

	}

	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain
	 * @return  void
	 */
	public function load_language( $domain ) {
		load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/*
	 * Activation Hook
	 */
	public static function activate() {
		global $wpdb;

		// Load DB delta
		if ( ! function_exists( 'dbDelta' ) ) {
			require( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		// Charset Collate
		$collate = $wpdb->get_charset_collate();

		// Create Realty Log Table
		$create_realty_log_tbl = ( "
					CREATE TABLE `{$wpdb->prefix}realtybloc_log` (
						`ID` bigint(200) UNSIGNED NOT NULL AUTO_INCREMENT,
						`site` varchar(255) NOT NULL,
						`user_id` bigint(200) NOT NULL,
						`date` datetime NOT NULL,
						`type` varchar(50) NOT NULL,
						`value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
						PRIMARY KEY  (ID)
					) {$collate}" );
		dbDelta( $create_realty_log_tbl );

		// Create Data Table
		$create_realty_log_data_tbl = ( "
						CREATE TABLE `{$wpdb->prefix}realtybloc_meta` (
						`meta_id` bigint(200) UNSIGNED NOT NULL AUTO_INCREMENT,
						`log_id` bigint(200) NOT NULL,
						`meta_key` varchar(255) NOT NULL,
						`meta_val` longtext NOT NULL,
						PRIMARY KEY (`meta_id`)
					) {$collate}" );
		dbDelta( $create_realty_log_data_tbl );
	}

}

//Load Plugin
add_action( 'plugins_loaded', array( REALTY_BLOC_LOG::instance(), 'init' ) );

//Activation
register_activation_hook( __FILE__, array( 'REALTY_BLOC_LOG', 'activate' ) );