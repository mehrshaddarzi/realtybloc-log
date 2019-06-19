<?php

namespace REALTY_BLOC_LOG;

use REALTY_BLOC_LOG\Core\Utility\Timezone;
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
		$query = $wpdb->get_results( "SELECT `user_id` FROM `{$wpdb->prefix}realtybloc_log` WHERE `user_id` >0 AND EXISTS (SELECT `ID` FROM `{$wpdb->users}` WHERE {$wpdb->prefix}realtybloc_log.user_id = {$wpdb->users}.ID) GROUP BY `user_id` ORDER BY `user_id`", ARRAY_A );
		$item  = array();
		foreach ( $query as $row ) {
			$item[ $row['user_id'] ] = User::get_name( $row['user_id'] );
		}

		return $item;
	}

	public static function get_number_day_install_plugin() {
		global $wpdb;
		$first_day = $wpdb->get_var( "SELECT `date` FROM `{$wpdb->prefix}realtybloc_log` ORDER BY `ID` ASC LIMIT 1" );
		if ( ! empty( $first_day ) ) {
			return (int) Timezone::getNumberDayBetween( $first_day );
		}

		return 30;
	}


}