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

}