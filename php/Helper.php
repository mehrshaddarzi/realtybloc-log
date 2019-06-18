<?php

namespace REALTY_BLOC_LOG;

use REALTY_BLOC_LOG\Core\Utility\User;

/**
 * Class Helper Used in custom Helper Method For This Plugin
 */
class Helper {

	/**
	 * Format array for the datepicker
	 *
	 * @param $array_to_strip
	 * @return array
	 */
	public static function strip_array_indices( $array_to_strip ) {
		$NewArray = array();
		foreach ( $array_to_strip as $objArrayItem ) {
			$NewArray[] = $objArrayItem;
		}

		return ( $NewArray );
	}

	/**
	 * Get List User Created Action
	 */
	public static function get_list_user_log() {
		global $wpdb;
		$query = $wpdb->get_results( "SELECT `user_id` FROM `{$wpdb->prefix}realtybloc_log` GROUP BY `user_id` ORDER BY `user_id`", ARRAY_A );
		$item  = array();
		foreach ( $query as $row ) {
			if ( User::exists( $row['user_id'] ) ) {
				$item[ $row['user_id'] ] = User::get_name( $row['user_id'] );
			}
		}

		return $item;
	}


}