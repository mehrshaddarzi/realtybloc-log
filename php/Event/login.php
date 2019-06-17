<?php

namespace REALTY_BLOC_LOG\Event;

use REALTY_BLOC_LOG\Event;

class login {

	public function __construct() {

		// Get User Event Option
		$user_event = ( isset( \REALTY_BLOC_LOG::$option['user_event'] ) ? \REALTY_BLOC_LOG::$option['user_event'] : array() );

		/**
		 * Register Event Action
		 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/user_register
		 */
		if ( isset( $user_event['register'] ) and $user_event['register'] == "on" ) {
			add_action( 'user_register', array( $this, 'save_user_registered' ), 10, 1 );
		}

		/**
		 * Login Success Action
		 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_login
		 */
		if ( isset( $user_event['login'] ) and $user_event['login'] == "on" ) {
			add_action( 'wp_login', array( $this, 'save_user_login' ), 10, 2 );
		}

		/**
		 * Fail User Login
		 * @see https://developer.wordpress.org/reference/functions/wp_authenticate/
		 */
		if ( isset( $user_event['fail'] ) and $user_event['fail'] == "on" ) {
			add_action( 'wp_login_failed', array( $this, 'save_user_fail' ), 10, 1 );
		}

		/**
		 * Forget and Reset Password
		 * @see https://developer.wordpress.org/reference/functions/reset_password/
		 */
		if ( isset( $user_event['forget'] ) and $user_event['forget'] == "on" ) {
			add_action( 'password_reset', array( $this, 'save_user_forget' ), 10, 2 );
		}

	}

	/**
	 * Save User registered action
	 *
	 * @param $user_id
	 */
	public function save_user_registered( $user_id ) {
		if ( self::user_can_log( $user_id ) ) {
			Event::save( array( 'user_id' => $user_id, 'type' => 'login', 'value' => 'register' ) );
		}
	}

	/**
	 * Save User Login action
	 *
	 * @param $user_login
	 * @param $user
	 */
	public function save_user_login( $user_login, $user ) {
		if ( self::user_can_log( $user->ID ) ) {
			Event::save( array( 'user_id' => $user->ID, 'type' => 'login', 'value' => 'success' ) );
		}
	}

	/**
	 * Save User Fail
	 * @param $username
	 */
	public function save_user_fail( $username ) {

		// Check Username is email or user_login
		$type = ( is_email( trim( $username ) ) ? 'email' : 'login' );

		// Check Exist User
		// @see https://developer.wordpress.org/reference/functions/get_user_by/
		$user = get_user_by( $type, trim( $username ) );

		// Save Ent if user exist
		if ( $user ) {
			if ( self::user_can_log( $user->ID ) ) {
				Event::save( array( 'user_id' => $user->ID, 'type' => 'login', 'value' => 'fail' ) );
			}
		}
	}

	/**
	 * Save forget Password Reset
	 *
	 * @param $user
	 * @param $new_pass
	 */
	public function save_user_forget( $user, $new_pass ) {
		if ( self::user_can_log( $user->ID ) ) {
			Event::save( array( 'user_id' => $user->ID, 'type' => 'login', 'value' => 'forget' ) );
		}
	}

	/**
	 * Check User Can Log
	 *
	 * @param $user
	 * @return bool
	 * @see https://developer.wordpress.org/reference/functions/user_can/
	 */
	public static function user_can_log( $user ) {

		// Allowed User Role
		$user_roles = ( isset( \REALTY_BLOC_LOG::$option['user_event']['role'] ) ? \REALTY_BLOC_LOG::$option['user_event']['role'] : array() );
		foreach ( array_keys( $user_roles ) as $role ) {
			if ( user_can( $user, $role ) ) {
				return true;
			}
		}

		return false;
	}

}