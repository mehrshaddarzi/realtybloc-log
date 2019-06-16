<?php

namespace REALTY_BLOC_LOG\Core\Utility;

class Admin {
	/**
	 * Show Admin Wordpress Ui Notice
	 *
	 * @param $text
	 * @param string $model
	 * @param bool $close_button
	 * @param bool $echo
	 * @param string $style_extra
	 * @return string
	 */
	public static function wp_admin_notice( $text, $model = "info", $close_button = true, $echo = true, $style_extra = 'padding:12px;' ) {
		$text = '
        <div class="notice notice-' . $model . '' . ( $close_button === true ? " is-dismissible" : "" ) . '">
           <div style="' . $style_extra . '">' . $text . '</div>
        </div>
        ';
		if ( $echo ) {
			echo $text;
		} else {
			return $text;
		}
	}

	/**
	 * Get Template File
	 *
	 * @param $template
	 * @param array $args
	 */
	public static function get_template( $template, $args = array() ) {

		// Push Args
		if ( is_array( $args ) && isset( $args ) ) :
			extract( $args );
		endif;

		// Check Load single file or array list
		if ( is_string( $template ) ) {
			$template = explode( " ", $template );
		}

		// Load File
		foreach ( $template as $file ) {

			$template_file = \REALTY_BLOC_LOG::$plugin_path . "php/Admin/views/" . $file . ".php";
			if ( ! file_exists( $template_file ) ) {
				_doing_it_wrong( __FUNCTION__, 'Template not found.', \REALTY_BLOC_LOG::$plugin_version );
				return;
			}

			// include File
			include $template_file;
		}
	}

	/**
	 * Get Screen ID
	 *
	 * @return string
	 */
	public static function get_screen_id() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		return $screen_id;
	}

	/**
	 * Check User Is Using Gutenberg Editor
	 */
	public static function is_gutenberg() {
		$current_screen = get_current_screen();
		return ( ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) || ( function_exists( 'is_gutenberg_page' ) ) && is_gutenberg_page() );
	}

	/**
	 * WP-Statistics WordPress Log
	 *
	 * @param $function
	 * @param $message
	 * @param $version
	 */
	public static function doing_it_wrong( $function, $message, $version ) {
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();
		if ( is_ajax() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public static function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'wp-cli':
				return defined( 'WP_CLI' ) && WP_CLI;
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! self::is_rest_request();
		}
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * @return bool
	 */
	public static function is_rest_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		return ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );
	}

	/**
	 * Check is Login Page
	 *
	 * @return bool
	 */
	public static function is_login_page() {

		// Check From global WordPress
		if ( isset( $GLOBALS['pagenow'] ) and in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
			return true;
		}

		// Check Native php
		$protocol   = strpos( strtolower( $_SERVER['SERVER_PROTOCOL'] ), 'https' ) === false ? 'http' : 'https';
		$host       = $_SERVER['HTTP_HOST'];
		$script     = $_SERVER['SCRIPT_NAME'];
		$currentURL = $protocol . '://' . $host . $script;
		$loginURL   = wp_login_url();
		if ( $currentURL == $loginURL ) {
			return true;
		}

		return false;
	}


}