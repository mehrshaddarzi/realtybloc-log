<?php

namespace REALTY_BLOC_LOG;

class Event {
	/**
	 * List Of Event
	 */
	public static function ls() {
		return array(
			'login' => array(
				'title' => __( 'User Event', 'realty-bloc-log' )
			),
			'form'  => array(
				'title' => __( 'Submit Form', 'realty-bloc-log' )
			),
			'view'  => array(
				'title' => __( 'Page View', 'realty-bloc-log' )
			)
		);
	}

	/**
	 * Save Event To Database
	 *
	 * @param array $args
	 * @return int
	 */
	public static function save( $args = array() ) {
		global $wpdb;

		// Define the array of defaults
		$defaults = array(
			'date'    => current_time( 'mysql' ),
			'user_id' => get_current_user_id(),
			'site'    => self::get_site_domain(),
			'type'    => '',
			'value'   => '',
			'meta'    => array()
		);
		$args     = wp_parse_args( $args, $defaults );

		// filter Dara Before Save event
		$args = apply_filters( 'realty_bloc_event_save_information', $args );

		// Insert TO Database
		$wpdb->insert(
			$wpdb->prefix . 'realtybloc_log',
			array(
				'site'    => $args['site'],
				'user_id' => $args['user_id'],
				'date'    => $args['date'],
				'type'    => $args['type'],
				'value'   => $args['value']
			),
			array( '%s', '%d', '%s', '%s', '%s' )
		);

		// Get Log Id
		$Log_id = $wpdb->insert_id;

		// Save Log Meta
		if ( count( $args['meta'] ) > 0 ) {
			foreach ( $args['meta'] as $meta_key => $meta_value ) {
				$wpdb->insert(
					$wpdb->prefix . 'realtybloc_meta',
					array(
						'log_id'   => $Log_id,
						'meta_key' => $meta_key,
						'meta_val' => $meta_value
					),
					array( '%d', '%s', '%s' )
				);
			}
		}

		// Action after Save Event
		do_action( 'realty_bloc_save_event', $Log_id );

		// Return ID
		return $Log_id;
	}

	/**
	 * Remove Event From DB
	 *
	 * @param $ID
	 */
	public static function remove( $ID ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'realtybloc_meta', array( 'log_id' => $ID ), array( '%d' ) );
		$wpdb->delete( $wpdb->prefix . 'realtybloc_log', array( 'ID' => $ID ), array( '%d' ) );
	}

	/**
	 * Get Event Data
	 *
	 * @param $ID
	 * @return bool
	 */
	public static function get( $ID ) {
		global $wpdb;

		$event = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}realtybloc_log` WHERE `ID` = " . $ID, ARRAY_A );
		if ( $event !== null ) {

			// get Extra Meta Field
			$meta      = array();
			$meta_data = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}realtybloc_meta` WHERE `log_id` = {$ID}", ARRAY_A );
			foreach ( $meta_data as $m ) {
				$meta[ $m['meta_key'] ] = $m['meta_val'];
			}

			// Return Data
			return array_merge( $event, array( 'meta' => $meta ) );
		}

		return false;
	}

	/**
	 * Get Event Number By Type
	 *
	 * @param array $args
	 * @return mixed
	 */
	public static function get_event_number( $args = array() ) {
		global $wpdb;

		// Prepare Item
		$defaults = array(
			'type' => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		// Check Where Sql
		$where = array();

		// Check Type
		if ( ! empty( $args['type'] ) ) {
			$where[] = "`type` = '{$args['type']}'";
		}

		// Basic SQL
		$sql = "SELECT COUNT(*) FROM `{$wpdb->prefix}realtybloc_log`";
		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Get WordPress Domain name
	 */
	public static function get_site_domain() {
		return rtrim( preg_replace( '/^https?:\/\//', '', get_site_url() ), " / " );
	}

	/**
	 * Get Event name
	 *
	 * @param $type
	 * @return mixed
	 */
	public static function get_event_name( $type ) {
		$list_event = self::ls();
		if ( isset( $list_event[ $type ] ) ) {
			return $list_event[ $type ]['title'];
		}

		return __( "Unknown", 'realty-bloc-log' );
	}
}