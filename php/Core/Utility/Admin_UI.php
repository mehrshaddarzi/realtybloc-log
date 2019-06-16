<?php

namespace REALTY_BLOC_LOG\Core\Utility;

class Admin_UI {
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

}