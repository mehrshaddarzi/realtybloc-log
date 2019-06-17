<?php

namespace REALTY_BLOC_LOG\Core\Utility;

class Post {
	/**
	 * Get List Post From Post Type
	 *
	 * @param $post_type
	 * @return array
	 */
	public static function get_list_post( $post_type = 'post' ) {
		$list = array();
		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => '-1',
			'order'          => 'ASC',
			'fields'         => 'ids'
		);

		$query = new \WP_Query( $args );
		foreach ( $query->posts as $ID ) {
			$list[ $ID ] = get_the_title( $ID );
		}
		wp_reset_postdata();

		return $list;
	}

	/**
	 * Check Post Exist By ID in wordpress
	 *
	 * @param $ID
	 * @param bool $post_type
	 * @return int
	 */
	public static function post_exist( $ID, $post_type = false ) {
		global $wpdb;

		$query = "SELECT count(*) FROM `$wpdb->posts` WHERE `ID` = $ID";
		if ( ! empty ( $post_type ) ) {
			$query .= " AND `post_type` = '$post_type'";
		}

		return ( (int) $wpdb->get_var( $query ) > 0 ? true : false );
	}

	/**
	 * Get List WordPress Post Type
	 *
	 * @param array $args
	 * @return array
	 * @see https://developer.wordpress.org/reference/functions/get_post_types/
	 */
	public static function get_list_post_type( $args = array() ) {

		// Get Default WordPress Post Type
		$post_types     = array( 'post', 'page' );

		// Define the array of defaults
		$defaults = array(
			'public' => true,
			'_builtin' => false
		);
		$args = wp_parse_args( $args, $defaults );

		// Get List Post Type
		$get_post_types = get_post_types( $args, 'names', 'and' );
		foreach ( $get_post_types as $name ) {
			$post_types[] = $name;
		}

		return $post_types;
	}

}