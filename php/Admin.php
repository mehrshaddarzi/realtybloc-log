<?php

namespace REALTY_BLOC_LOG;

use REALTY_BLOC_LOG\Admin\Log_List;

class Admin {
	/**
	 * Admin Page slug
	 */
	public static $admin_page_slug;

	/**
	 * Admin_Page constructor.
	 */
	public function __construct() {
		/*
		 * Set Page slug Admin
		 */
		self::$admin_page_slug = 'realtybloc';
		/*
		 * Setup Admin Menu
		 */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		/*
		 * Register Script in Admin Area
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Admin Link
	 *
	 * @param $page
	 * @param array $args
	 * @return string
	 */
	public static function admin_link( $page, $args = array() ) {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . $page ) );
	}

	/**
	 * If in Page in Admin
	 *
	 * @param $page_slug
	 * @return bool
	 */
	public static function in_page( $page_slug ) {
		global $pagenow;
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and $_GET['page'] == $page_slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Load assets file in admin
	 *
	 * @param $hook
	 */
	public function admin_assets( $hook ) {

		//Add Admin Asset
		if ( self::in_page( self::$admin_page_slug ) ) {
			wp_enqueue_style( 'realty-bloc-log', \REALTY_BLOC_LOG::$plugin_url . '/dist/css/admin.min.css', array(), \REALTY_BLOC_LOG::$plugin_version, 'all' );
			wp_enqueue_script( 'realty-bloc-log', \REALTY_BLOC_LOG::$plugin_url . '/dist/js/admin.min.js', array( 'jquery' ), self::version(), false );
			wp_localize_script( 'realty-bloc-log', 'rbl_global', self::global_var_js( $hook ) );
		}

	}

	/**
	 * Get Version of File
	 *
	 * @param $ver
	 * @return bool
	 */
	public static function version( $ver = false ) {
		if ( $ver ) {
			return $ver;
		} else {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				return time();
			} else {
				return \REALTY_BLOC_LOG::$plugin_version;
			}
		}
	}

	/**
	 * Prepare global WP-Statistics data for use Admin Js
	 *
	 * @param $hook
	 * @return mixed
	 */
	public static function global_var_js( $hook ) {

		//Global Option
		$list['options'] = array(
			'rtl'       => ( is_rtl() ? 1 : 0 ),
			'gutenberg' => ( \REALTY_BLOC_LOG\Core\Utility\Admin::is_gutenberg() ? 1 : 0 )
		);

		// WordPress Current Page
		$list['page'] = $hook;

		// WordPress Admin Page request Params
		if ( isset( $_GET ) ) {
			foreach ( $_GET as $key => $value ) {
				if ( $key != "page" ) {
					$list['request'][ $key ] = $value;
				}
			}
		}

		// Global Lang
		$list['i18n'] = array(
			'more_detail' => __( 'More Details', 'wp-statistics' ),
			'reload'      => __( 'Reload', 'wp-statistics' ),
			'please_wait' => __( 'Please Wait ...', 'wp-statistics' ),
		);

		// Rest-API Meta Box Url
		$list['ajax_url'] = admin_url( 'admin-ajax.php' );
		$list['wp_nonce'] = wp_create_nonce( 'wp_rest' );

		// Return Data JSON
		return $list;
	}

	/**
	 * Set Admin Menu
	 */
	public function admin_menu() {
		// Base Menu
		add_menu_page( __( 'RealtyBloc', 'realty-bloc-log' ), __( 'RealtyBloc', 'realty-bloc-log' ), 'manage_options', self::$admin_page_slug, array( '\REALTY_BLOC_LOG\Admin\Log_List', 'view' ), 'dashicons-album', 6 );

		// Setting Page
		add_submenu_page( self::$admin_page_slug, __( 'Settings', 'realty-bloc-log' ), __( 'Settings', 'realty-bloc-log' ), 'manage_options', self::$admin_page_slug . '-option', array( Settings::instance(), 'wedevs_plugin_page' ) );
	}
}