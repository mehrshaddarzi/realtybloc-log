<?php

namespace REALTY_BLOC_LOG;

class Rest_API {
	/**
	 * Rest API namespace
	 *
	 * @var string
	 */
	public static $namespace = 'realtybloc/v1';

	/**
	 * Handle Response
	 *
	 * @param $message
	 * @param int $status
	 * @return \WP_REST_Response
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
	 */
	public static function response( $message, $status = 200 ) {
		if ( $status == 200 ) {
			$output = array(
				'data' => $message
			);
		} else {
			$output = array(
				'error' => array(
					'status'  => $status,
					'message' => $message,
				)
			);
		}
		return new \WP_REST_Response( $output, $status );
	}

	/**
	 * Internal Request WP REST API
	 *
	 * @param array $args
	 * @return mixed
	 */
	public static function request( $args = array() ) {

		// Define the array of defaults
		$defaults = array(
			'type'      => 'GET',
			'namespace' => self::$namespace,
			'route'     => '',
			'params'    => array()
		);
		$args     = wp_parse_args( $args, $defaults );

		// Send Request
		$request = new \WP_REST_Request( $args['type'], '/' . ltrim( $args['namespace'], "/" ) . '/' . $args['route'] );
		$request->set_query_params( $args['params'] );
		$response = rest_do_request( $request );
		$server   = rest_get_server();
		return $server->response_to_data( $response, false );
	}

}