<?php

function rbl_save_event( $args = array() ) {
	return \REALTY_BLOC_LOG\Event::save( $args );
}

function rbl_get_event_data( $event_id ) {
	return \REALTY_BLOC_LOG\Event::get( $event_id );
}

function rbl_remove_event( $event_id ) {
	\REALTY_BLOC_LOG\Event::remove( $event_id );
}