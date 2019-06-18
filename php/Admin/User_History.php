<?php

namespace REALTY_BLOC_LOG\Admin;

use REALTY_BLOC_LOG\Admin;
use REALTY_BLOC_LOG\Core\Utility\Timezone;
use REALTY_BLOC_LOG\Core\Utility\User;
use REALTY_BLOC_LOG\Event;
use REALTY_BLOC_LOG\Helper;

class User_History {

	/**
	 * Default Pagination GET name
	 *
	 * @var string
	 */
	public static $paginate_link_name = 'pagination-page';

	/**
	 * Default Item Per Page in Pagination
	 *
	 * @var int
	 */
	public static $item_per_page = 10;

	/**
	 * Jquery UI Datepicker Format in PHP
	 *
	 * @var string
	 */
	public static $datepicker_format = 'Y-m-d';

	/**
	 * Global Request Date time in Pages
	 *
	 * @var string
	 */
	public static $request_from_date = 'from';
	public static $request_to_date = 'to';

	/**
	 * Default time Ago Days in Pages
	 *
	 * @var int
	 */
	public static $time_ago_days = 30;

	/**
	 * User_History constructor.
	 *
	 * @throws \Exception
	 */
	public function __construct() {

		if ( self::in_user_history_page() ) {

			// Check Time Picker
			$DateRequest = self::isValidDateRequest();
			if ( ! $DateRequest['status'] ) {
				wp_die( $DateRequest['message'] );
			}

		}

	}

	public static function in_user_history_page() {
		return Admin::in_page( Admin::$admin_page_slug ) and isset( $_GET['method'] ) and $_GET['method'] == "user-history";
	}

	/**
	 * Check is Validation Date time Request in Page
	 *
	 * @throws \Exception
	 */
	public static function isValidDateRequest() {

		// Get Default Time Ago days
		$default_days_ago = Helper::get_number_day_install_plugin();

		// Check if not Request Params
		if ( ! isset( $_GET[ self::$request_from_date ] ) and ! isset( $_GET[ self::$request_to_date ] ) ) {
			return array( 'status' => true, 'days' => Timezone::getListDays( array( 'from' => TimeZone::getTimeAgo( $default_days_ago ) ) ), 'type' => 'ago' );
		}

		// Check if Not Exist
		if ( ( isset( $_GET[ self::$request_from_date ] ) and ! isset( $_GET[ self::$request_to_date ] ) ) || ( ! isset( $_GET[ self::$request_from_date ] ) and isset( $_GET[ self::$request_to_date ] ) ) ) {
			return array( 'status' => false, 'message' => __( "Request is not valid.", "wp-statistics" ) );
		}

		// Check Validate DateTime
		if ( TimeZone::isValidDate( $_GET[ self::$request_from_date ] ) === false || TimeZone::isValidDate( $_GET[ self::$request_to_date ] ) === false ) {
			return array( 'status' => false, 'message' => __( "Time request is not valid.", "wp-statistics" ) );
		}

		// Export Days Between
		$type = ( self::$request_to_date == TimeZone::getCurrentDate( "Y-m-d" ) ? 'ago' : 'between' );
		return array( 'status' => true, 'days' => TimeZone::getListDays( array( 'from' => $_GET[ self::$request_from_date ], 'to' => $_GET[ self::$request_to_date ] ) ), 'type' => $type );
	}

	/**
	 * Custom page
	 *
	 * @throws \Exception
	 */
	public static function view() {

		// Get User ID
		$args['user_id'] = $_GET['user_id'];

		// Page title
		$args['title'] = __( 'User History', 'wp-statistics' );

		// Get Current Page Url
		$args['pageName']             = 'realtybloc';
		$args['custom_get']['method'] = 'user-history';

		// Get Date-Range
		$args['DateRang'] = self::DateRange();

		// Get User information
		$args['user']            = User::get( $args['user_id'] );
		$args['user_name']       = User::get_name( $args['user_id'] );
		$args['number_of_event'] = Event::get_event_number( array( 'user_id' => $args['user_id'] ) );




		// Show Template Page
		\REALTY_BLOC_LOG\Core\Utility\Admin::get_template( array( 'layout/header', 'layout/title', 'layout/date.range', 'layout/footer' ), $args );
	}

	/**
	 * Create Date Range
	 *
	 * @param bool $page_link
	 * @return array
	 * @throws \Exception
	 */
	public static function DateRange( $page_link = false ) {

		// Default List Of Date Range
		$date_range = array(
			10  => __( '10 Days', 'wp-statistics' ),
			20  => __( '20 Days', 'wp-statistics' ),
			30  => __( '30 Days', 'wp-statistics' ),
			60  => __( '2 Months', 'wp-statistics' ),
			90  => __( '3 Months', 'wp-statistics' ),
			180 => __( '6 Months', 'wp-statistics' ),
			270 => __( '9 Months', 'wp-statistics' ),
			365 => __( '1 Year', 'wp-statistics' )
		);

		// Get All Date From installed plugins day
		$first_day = Helper::get_number_day_install_plugin();
		if ( $first_day != false ) {
			$date_range[ $first_day ] = __( 'All', 'realty-bloc-log' );
		}

		// Create Link Of Date Time range
		$list = array();
		foreach ( $date_range as $number_days => $title ) {

			// Generate Link
			$link = add_query_arg( array( 'from' => TimeZone::getTimeAgo( $number_days ), 'to' => TimeZone::getCurrentDate( "Y-m-d" ) ), ( isset( $page_link ) ? $page_link : remove_query_arg( array( self::$request_from_date, self::$request_to_date ) ) ) );

			// Check Activate Page
			$active      = false;
			$RequestDate = self::isValidDateRequest();
			if ( $RequestDate['status'] === true ) {
				$RequestDateKeys = array_keys( $RequestDate['days'] );
				if ( reset( $RequestDateKeys ) == TimeZone::getTimeAgo( $number_days ) and end( $RequestDateKeys ) == TimeZone::getCurrentDate( "Y-m-d" ) ) {
					$active = true;
				}
			}

			// Push To list
			$list[ $number_days ] = array( 'title' => $title, 'link' => $link, 'active' => $active );
		}

		return array( 'list' => $list, 'from' => reset( $RequestDateKeys ), 'to' => end( $RequestDateKeys ) );
	}

	/**
	 * Unknown Column
	 *
	 * @return string
	 */
	public static function UnknownColumn() {
		return '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'wp-statistics' ) . '</span>';
	}

}