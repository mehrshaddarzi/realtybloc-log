<?php

namespace REALTY_BLOC_LOG;

class Front {

	/**
	 * Asset Script name
	 */
	public static $asset_name = 'realty-bloc-log';

	/**
	 * constructor.
	 */
	public function __construct() {
		/*
		 * Add Script
		 */
		//add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_style' ) );
	}

	/**
	 * Register Asset
	 */
	public function wp_enqueue_style() {

		//Jquery Custom Plugin
		//wp_enqueue_style( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.css', array(), WP_REVIEWS_INSURANCE::$plugin_version, 'all' );
		//wp_enqueue_script( 'jquery-raty', WP_REVIEWS_INSURANCE::$plugin_url . '/asset/jquery-raty/jquery.raty.js', array( 'jquery' ), WP_REVIEWS_INSURANCE::$plugin_version, false );

		//Native Plugin
		//wp_enqueue_style( self::$asset_name, WP_REVIEWS_INSURANCE::$plugin_url . '/asset/style.css', array(), WP_REVIEWS_INSURANCE::$plugin_version, 'all' );
		//$custom_css = ".cancel-on-png, .cancel-off-png, .star-on-png, .star-off-png, .star-half-png {color: " . WP_REVIEWS_INSURANCE::$option['star_color'] . ";}";
		//wp_add_inline_style( self::$asset_name, $custom_css );
	}
}