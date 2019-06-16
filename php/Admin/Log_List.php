<?php

namespace REALTY_BLOC_LOG\Admin;

use REALTY_BLOC_LOG\Core\Utility\Admin_UI;

class Log_List {

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

	public static function view() {

		// Page title
		$args['title'] = __( 'Author Statistics', 'realty-bloc-log' );


		// Show Template Page
		Admin_UI::get_template( array( 'layout/header', 'layout/title', 'layout/footer' ), $args );
	}


}