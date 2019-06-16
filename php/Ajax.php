<?php

namespace REALTY_BLOC_LOG;

/**
 * Ajax Method wordpress
 */
class Ajax {

	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		$list_function = array(
			'add_reviews_insurance'
		);
		foreach ( $list_function as $method ) {
			add_action( 'wp_ajax_' . $method, array( $this, $method ) );
			add_action( 'wp_ajax_nopriv_' . $method, array( $this, $method ) );
		}
	}

	/**
	 * Show Json and Exit
	 *
	 * @since    1.0.0
	 * @param $array
	 */
	public function json_exit( $array ) {
		wp_send_json( $array );
		exit;
	}

	public function add_reviews_insurance() {
		global $wpdb;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			//Show Result
			$this->json_exit( array( 'state_request' => 'success' ) );
		}
		exit;
	}

}