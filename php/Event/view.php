<?php

namespace REALTY_BLOC_LOG\Event;

use REALTY_BLOC_LOG\Event;

class view {

	public function __construct() {

		// Get Check Active Page Type
		$view_event = ( isset( \REALTY_BLOC_LOG::$option['view_event'] ) ? \REALTY_BLOC_LOG::$option['view_event'] : array() );

		// Property Page view log
		if ( isset( $view_event['property'] ) and $view_event['property'] == "on" ) {
			add_action( 'rb_page_view_property_log', array( $this, 'save_property' ), 10, 5 );
		}

		// building Page View log
		if ( isset( $view_event['building'] ) and $view_event['building'] == "on" ) {
			add_action( 'rb_page_view_building_log', array( $this, 'save_building' ), 10, 5 );
		}
	}

	/**
	 * Save Property view Log
	 *
	 * @param $mls_number
	 * @param $post_title
	 * @param $user_id
	 * @param $url
	 * @param $status [ sale | pre-sale ]
	 */
	public function save_property( $mls_number, $post_title, $user_id, $url, $status ) {

		// Check User is Login
		if ( $user_id < 1 ) {
			return;
		}

		// Check User Capacity
		if ( login::user_can_log( $user_id ) === false ) {
			return;
		}

		// Save Log
		Event::save( array(
			'user_id' => $user_id,
			'type'    => 'view',
			'value'   => 'property',
			'meta'    => array(
				'mls'    => ( empty( $mls_number ) ? null : $mls_number ),
				'title'  => $post_title,
				'url'    => $url,
				'status' => $status
			)
		) );
	}

	/**
	 * Save Building view Log
	 *
	 * @param $mls_number
	 * @param $post_title
	 * @param $user_id
	 * @param $url
	 * @param $status [ sale | pre-sale ]
	 */
	public function save_building( $mls_number, $post_title, $user_id, $url, $status ) {

		// Check User is Login
		if ( $user_id < 1 ) {
			return;
		}

		// Check User Capacity
		if ( login::user_can_log( $user_id ) === false ) {
			return;
		}

		// Save Log
		Event::save( array(
			'user_id' => $user_id,
			'type'    => 'view',
			'value'   => 'building',
			'meta'    => array(
				'mls'    => ( empty( $mls_number ) ? null : $mls_number ),
				'title'  => $post_title,
				'url'    => $url,
				'status' => $status
			)
		) );
	}

}