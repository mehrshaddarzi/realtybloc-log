<?php

namespace REALTY_BLOC_LOG\Admin;

use REALTY_BLOC_LOG\Event;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Wp_List_Table_Event_Log extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'event_log',
			'plural'   => 'event_log',
			'ajax'     => false
		) );
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		//Column Option
		$this->_column_headers = $this->get_column_info();

		//Process Bulk and Row Action
		$this->process_bulk_action();

		//Prepare Data
		$per_page     = $this->get_items_per_page( 'rbl_event_log_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		//Create Pagination
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

		//return items
		$this->items = self::get_actions( $per_page, $current_page );
	}

	/**
	 * Retrieve Items data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_actions( $per_page = 10, $page_number = 1 ) {
		global $wpdb;

		$tbl = $wpdb->prefix . 'realtybloc_log';
		$sql = "SELECT * FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		//Check Order By
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		} else {
			$sql .= ' ORDER BY `ID`';
		}

		//Check Order Fields
		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Conditional sql
	 */
	public static function conditional_sql() {
		//Where conditional
		$where = false;

		//Check Search
		if ( isset( $_GET['s'] ) and ! empty( $_GET['s'] ) ) {
			$search  = sanitize_text_field( $_GET['s'] );
			$where[] = "`action_name` LIKE '%{$search}%'";
		}

		//Check Filter Method
		if ( isset( $_GET['filter'] ) and ! empty( $_GET['filter'] ) ) {
			$status_id = array( "active" => 1, "inactive" => 0, "draft" => 2 );
			$where[]   = '`action_status` =' . $status_id[ sanitize_text_field( $_GET["filter"] ) ];
		}

		//Check filter Creator User
		if ( isset( $_GET['user'] ) and ! empty( $_GET['user'] ) ) {
			$where[] = '`user_create` =' . $_GET['user'];
		}

		return $where;
	}

	/**
	 * Delete a action record.
	 *
	 * @param int $id action ID
	 */
	public static function delete_action( $id ) {
		global $wpdb;
		$tbl = $wpdb->prefix . 'realtybloc_log';
		$wpdb->delete( $tbl, array( 'ID' => $id ), array( '%d' ) );
	}


	/**
	 * Returns the count of records in the database.
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$tbl = $wpdb->prefix . 'realtybloc_log';
		$sql = "SELECT COUNT(*) FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Not Found Item Text
	 */
	public function no_items() {
		_e( 'No event log avaliable.', 'realty-bloc-log' );
	}

	/**
	 *  Associative array of columns
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'event'       => __( 'Event', 'realty-bloc-log' ),
			'date_create' => __( 'Date Create', 'realty-bloc-log' ),
			'user_id'     => __( 'User', 'realty-bloc-log' ),
			'info'        => __( 'Information', 'realty-bloc-log' ),
		);

		return $columns;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID'] );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		global $WP_Statistics;

		//Default unknown Column Value
		$unknown = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'wp-statistics-actions' ) . '</span>';

		switch ( $column_name ) {
			case 'action_name' :

				// row actions to ID
				$actions['id'] = '<span class="text-muted">#' . $item['ID'] . '</span>';

				// row actions to edit
				$actions['edit'] = '<a href="' . add_query_arg( array( 'page' => WP_Statistics_Actions::admin_slug, 'method' => 'edit', 'ID' => $item['ID'] ), admin_url( "admin.php" ) ) . '">' . __( 'Edit', 'wp-statistics-actions' ) . '</a>';

				// row actions to show status
				if ( $item['action_status'] == 1 and $WP_Statistics->get_option( 'visits' ) ) {
					$trigger           = WP_Statistics_Actions_Trigger::get_trigger_data( $item['action_trigger'] );
					$actions['status'] = '<a data-trigger-type="' . $trigger['type'] . '" data-view-status="' . $trigger['value'] . '" href="#" class="text-success">' . __( 'Statistics', 'wp-statistics-actions' ) . '</a>';
				}

				//Row Action to Clone
				$actions['clone'] = '<a href="' . add_query_arg( array( 'page' => WP_Statistics_Actions::admin_slug, 'clone' => $item['ID'], '_wpnonce' => wp_create_nonce( 'clone_action_nonce' ) ), admin_url( "admin.php" ) ) . '" class="text-warning">' . __( 'Clone', 'wp-statistics-actions' ) . '</a>';

				// row actions to Delete
				$actions['trash'] = '<a data-trash="yes" href="' . add_query_arg( array( 'page' => WP_Statistics_Actions::admin_slug, 'action' => 'delete', '_wpnonce' => wp_create_nonce( 'delete_action_nonce' ), 'del' => $item['ID'] ), admin_url( "admin.php" ) ) . '">' . __( 'Delete', 'wp-statistics-actions' ) . '</a>';

				return $item['action_name'] . $this->row_actions( $actions );
				break;

			case 'date_create' :
				$date                   = date_i18n( "j F Y", strtotime( $item['date_create'] ) );
				$actions['create_time'] = date_i18n( "H:i:s", strtotime( $item['date_create'] ) );

				return $date . $this->row_actions( $actions );
				break;
			case 'user_create' :

				return '<span data-action-id="' . $item['ID'] . '" data-user-create="' . $item['user_create'] . '">' . WP_Statistics_Actions_Helper::get_user_fullname( $item['user_create'] ) . '</span>';
				break;
			case 'trigger' :
				$trigger = WP_Statistics_Actions_Trigger::get_trigger_data( $item['action_trigger'] );
				if ( $trigger === false ) {
					return $unknown;
				} else {
					return '<span class="text-primary">' . $trigger['name'] . '</span> <br/>' . ( $trigger['admin_link'] != "" ? '<a href="' . $trigger['admin_link'] . '" class="text-danger" target="_blank">' : '' ) . $trigger['id'] . ' : ' . number_format( $trigger['value'] ) . ( $trigger['admin_link'] != "" ? '</a>' : '' );
				}

				break;
			case 'action' :

				$action = WP_Statistics_Actions_Approach::get_action_data( $item['action_approach'] );
				if ( ! isset( $action['method'] ) ) {
					return $unknown;
				} else {
					return '<span class="text-success">' . WP_Statistics_Actions_Approach::get_group_name( $action['type'] ) . '</span> <br> ' . WP_Statistics_Actions_Approach::get_method_name( $action['type'], $action['method'] );
				}
				break;
			case 'expiration_date' :

				if ( is_null( $item['expiration_date'] ) || empty( $item['expiration_date'] ) ) {
					return $unknown;
				} else {
					$date = date_i18n( "j F Y", strtotime( $item['expiration_date'] ) );
					$time = date_i18n( "H:i", strtotime( $item['expiration_date'] ) );

					return $date . '<br>' . $time;
				}
				break;
			case 'run_date' :

				if ( is_null( $item['run_date'] ) || empty( $item['run_date'] ) ) {
					return $unknown;
				} else {
					$date = date_i18n( "j F Y", strtotime( $item['run_date'] ) );
					$time = date_i18n( "H:i:s", strtotime( $item['run_date'] ) );

					return $date . '<br>' . $time;
				}
				break;
			case 'status' :

				$status = '<div class="tooltip">';
				if ( $item['action_status'] == 0 ) {
					//InActivate
					$status .= '<i class="fa fa-circle text-danger circle"></i><span class="tooltiptext">' . __( "inActive", 'wp-statistics-actions' ) . '</span></div>';
				} elseif ( $item['action_status'] == 1 ) {
					//Active
					if ( $WP_Statistics->get_option( 'visits' ) ) {
						$status .= '<a data-view-status="' . $item['ID'] . '" href="#">';
					}
					$status .= '<i class="fa fa-circle active_status circle"></i><span class="tooltiptext">' . __( "Active", 'wp-statistics-actions' ) . '</span>';
					if ( $WP_Statistics->get_option( 'visits' ) ) {
						$status .= '</a>';
					}
					$status .= '</div>';
				} else {
					//Draft
					$status .= '<i class="fa fa-circle circle"></i><span class="tooltiptext">' . __( "Draft", 'wp-statistics-actions' ) . '</span></div>';
				}
				return $status;
				break;
		}
	}

	/**
	 * Columns to make sortable.
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'event'       => array( 'event', false ),
			'date_create' => array( 'date_create', false ),
			'user_id'     => array( 'user_id', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Show SubSub Filter
	 */
	protected function get_views() {
		$views   = array();
		$current = ( ! empty( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'all' );

		//All Item
		$class        = ( $current == 'all' ? ' class="current"' : '' );
		$all_url      = remove_query_arg( array( 'filter', 's', 'paged', 'alert', 'user' ) );
		$views['all'] = "<a href='{$all_url}' {$class} >" . __( "All", 'realty-bloc-log' ) . " <span class=\"count\">(" . number_format_i18n( Event::get_event_number() ) . ")</span></a>";

		// Push Item
		foreach ( Event::ls() as $key => $val ) {
			$views_item[ $key ] = $val['title'];
			$custom_url         = add_query_arg( 'type', $key, remove_query_arg( array( 's', 'paged', 'alert' ) ) );
			$class              = ( $current == $key ? ' class="current"' : '' );
			$views[ $key ]      = "<a href='{$custom_url}' {$class} >" . $val['title'] . " <span class=\"count\">(" . number_format_i18n( Event::get_event_number( array( 'type' => $key ) ) ) . ")</span></a>";
		}

		return $views;
	}

	/**
	 * Advance Custom Filter
	 *
	 * @param $which
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			?>
            <!-- <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php //_e( "Creator User" ); ?></label>
                <select name="user" id="bulk-action-selector-top">
                    <option value=""><?php //_e( "Creator User" ); ?></option>
                </select>
                <input type="submit" id="doaction" class="button action" value="<?php //_e( "Filter" ); ?>">
            </div> -->
			<?php
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-deactivate' => __( 'Deactivate', 'wp-statistics-actions' ),
			'bulk-delete'     => __( 'Delete', 'wp-statistics-actions' ),
		);

		return $actions;
	}

	/**
	 * Search Box
	 *
	 * @param $text
	 * @param $input_id
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" placeholder="<?php echo __( "Action Name", 'wp-statistics-actions' ); ?>" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" autocomplete="off"/>
			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
	}

	/**
	 * Bulk and Row Actions
	 */
	public function process_bulk_action() {

		// Row Action Delete
		if ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_action_nonce' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			} else {
				self::delete_action( absint( $_GET['del'] ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => WP_Statistics_Actions::admin_slug, 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}


		//Bulk Action Delete
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) ) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			if ( is_array( $delete_ids ) and count( $delete_ids ) > 0 ) {
				foreach ( $delete_ids as $id ) {
					self::delete_action( $id );
				}

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => WP_Statistics_Actions::admin_slug, 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}

		//Bulk Action Deactivated
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-deactivate' ) ) {

			$deactivate_ids = esc_sql( $_POST['bulk-delete'] );
			if ( is_array( $deactivate_ids ) and count( $deactivate_ids ) > 0 ) {
				foreach ( $deactivate_ids as $id ) {
					WP_Statistics_Actions_Helper::set_action_status( $id, 0 );
				}

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => WP_Statistics_Actions::admin_slug, 'alert' => 'deactivate' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}

	}

}