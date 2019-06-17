<?php

namespace REALTY_BLOC_LOG\Event;

use REALTY_BLOC_LOG\Event;

class form {

	public function __construct() {

		// Get Form Activate List
		$form_event = ( isset( \REALTY_BLOC_LOG::$option['form_event'] ) ? \REALTY_BLOC_LOG::$option['form_event'] : array() );

		// List Active Form
		$active_forms = ( isset( $form_event['form'] ) ? array_keys( $form_event['form'] ) : array() );

		// Add Hook For Save Form
		foreach ( $active_forms as $form_id ) {
			add_action( "wpforms_process_complete_{$form_id}", array( $this, 'save_form_event' ), 10, 4 );
		}
	}

	/**
	 * Save Form Action
	 *
	 * @param $fields
	 * @param $entry
	 * @param $form_data
	 * @param $entry_id
	 */
	public function save_form_event( $fields, $entry, $form_data, $entry_id ) {

		// First Check User is Login
		if ( is_user_logged_in() ) {

			// Save event
			Event::save( array(
				'type'  => 'form',
				'value' => $form_data['id'],
				'meta'  => array(
					'entry_id' => $entry_id
				)
			) );

		}
	}

	/**
	 * Get Entry Link
	 *
	 * @param $entry_id
	 * @return mixed
	 */
	public static function get_entry_link( $entry_id ) {
		return add_query_arg( array( 'page' => 'wpforms-entries', 'view' => 'details', 'entry_id' => $entry_id, ), admin_url( 'admin.php' ) );
	}

}