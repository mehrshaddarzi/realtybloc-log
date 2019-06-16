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
	 */
	public function admin_assets() {
		global $pagenow;

		//List Allow This Script
		//if ( $pagenow == "edit-comments.php" || $pagenow == "edit.php" ) {

		//Jquery Raty
		//wp_enqueue_style( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.css', array(), WP_REVIEWS_INSURANCE::$plugin_version, 'all' );
		//wp_enqueue_script( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.js', array( 'jquery' ), WP_REVIEWS_INSURANCE::$plugin_version, false );

		//}
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