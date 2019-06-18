<?php

namespace REALTY_BLOC_LOG\Admin;

use REALTY_BLOC_LOG\Admin;
use REALTY_BLOC_LOG\Core\Utility\Post;
use REALTY_BLOC_LOG\Core\Utility\User;
use REALTY_BLOC_LOG\Event;
use REALTY_BLOC_LOG\Helper;

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

		// Base Table
		$tbl = $wpdb->prefix . 'realtybloc_log';

		// Basic Table
		$sql = "SELECT * FROM `$tbl`";

		// Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		// Check Order By
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		} else {
			$sql .= ' ORDER BY `ID`';
		}

		//Check Order Fields
		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		// Return Data
		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	/**
	 * Conditional sql
	 */
	public static function conditional_sql() {
		//Where conditional
		$where = false;

		// Event Type
		if ( isset( $_GET['type'] ) and ! empty( $_GET['type'] ) ) {
			$where[] = "`type` = '" . trim( $_GET['type'] ) . "'";
		}

		// User ID
		if ( isset( $_GET['user_id'] ) and ! empty( $_GET['user_id'] ) ) {
			$where[] = '`user_id` = ' . trim( $_GET['user_id'] );
		}

		// Check Date Time
		$regEx = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";

		// Check From time
		if ( isset( $_GET['from'] ) and ! isset( $_GET['to'] ) and preg_match( $regEx, $_GET['from'] ) ) {
			$where[] = "`date` >= '{$_GET['from']}'";
		}

		// Check To time
		if ( isset( $_GET['to'] ) and ! isset( $_GET['from'] ) and preg_match( $regEx, $_GET['to'] ) ) {
			$where[] = "`date` <= '{$_GET['to']}'";
		}

		// Check between Time
		if ( isset( $_GET['to'] ) and isset( $_GET['from'] ) and preg_match( $regEx, $_GET['to'] ) and preg_match( $regEx, $_GET['from'] ) ) {
			$where[] = " `date` BETWEEN '{$_GET['from']} 00:00:00' AND '{$_GET['to']} 23:59:59'";
		}

		//Check Search [ Disabled ]
		//if ( isset( $_GET['s'] ) and ! empty( $_GET['s'] ) ) {
			//$search  = sanitize_text_field( $_GET['s'] );
			//$where[] = "`` LIKE '%{$search}%'";
		//}

		return $where;
	}

	/**
	 * Delete a action record.
	 *
	 * @param int $id action ID
	 */
	public static function delete_action( $id ) {
		Event::remove( $id );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		// Table name
		$tbl = $wpdb->prefix . 'realtybloc_log';

		// Base SQL
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
			'cb'      => '<input type="checkbox" />',
			'event'   => __( 'Event', 'realty-bloc-log' ),
			'date'    => __( 'Date', 'realty-bloc-log' ),
			'user_id' => __( 'User', 'realty-bloc-log' ),
			'info'    => __( 'Information', 'realty-bloc-log' )
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

		//Default unknown Column Value
		$unknown = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'realty-bloc-log' ) . '</span>';

		// Get Event information
		$get_event_data = Event::get( $item['ID'] );

		switch ( $column_name ) {
			case 'event' :

				// row actions to ID
				$actions['id'] = '<span class="rbl-text-warning">#' . $item['ID'] . '</span>';

				// row actions to Delete
				$actions['trash'] = '<a href="' . add_query_arg( array( 'page' => Admin::$admin_page_slug, 'action' => 'delete', '_wpnonce' => wp_create_nonce( 'delete_nonce' ), 'del' => $item['ID'] ), admin_url( "admin.php" ) ) . '">' . __( 'Delete', 'realty-bloc-log' ) . '</a>';

				// show
				return Event::get_event_name( $item['type'] ) . $this->row_actions( $actions );
				break;

			case 'date' :
				$date                   = date_i18n( "j F Y", strtotime( $item['date'] ) );
				$actions['create_time'] = '<span class="rbl-text-muted">' . date_i18n( "H:i:s", strtotime( $item['date'] ) ) . '</span>';

				return $date . $this->row_actions( $actions );
				break;

			case 'user_id' :

				if ( User::exists( $item['user_id'] ) ) {

					$user_inf = User::get( $item['user_id'] );
					$t        = '<a class="wps-text-danger" target="_blank" href="' . add_query_arg( array( 'page' => Admin::$admin_page_slug, 'method' => 'user-history', 'user_id' => $item['user_id'] ), admin_url( "admin.php" ) ) . '">' . __( "ID", "realty-bloc-log" ) . ': ' . $item['user_id'] . '</a><br />';
					$t        .= '<div>' . __( "Email", "realty-bloc-log" ) . ': ' . $user_inf['user_email'] . '</div>';
					$t        .= '<div>' . __( "Name", "realty-bloc-log" ) . ': ' . User::get_name( $item['user_id'] ) . '</div>';
					return $t;
				} else {
					return $unknown;
				}
				break;

			case 'info' :

				switch ( $item['type'] ) {
					case 'login' :
						$type_login_list = Event\login::act_name();
						if ( isset( $type_login_list[ $item['value'] ] ) ) {
							return $type_login_list[ $item['value'] ];
						} else {
							return $unknown;
						}
						break;

					case 'form' :
						$t = '<div>' . __( "Form ID", "realty-bloc-log" ) . ': ' . $item['value'] . '</div>';
						$t .= '<div>' . __( "Form Title", "realty-bloc-log" ) . ': ' . ( Post::post_exist( $item['value'] ) ? get_the_title( $item['value'] ) : '-' ) . '</div>';
						$t .= '<a style="color: #494df5 !important;" target="_blank" href="' . Event\form::get_entry_link( $get_event_data['meta']['entry_id'] ) . '">' . __( "Show Entry", "realty-bloc-log" ) . '</a>';
						return $t;
						break;

					default:
						return $unknown;
						break;
				}

				break;
		}
	}

	/**
	 * Columns to make sortable.
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'event'   => array( 'type', false ),
			'date'    => array( 'date', false ),
			'user_id' => array( 'user_id', false )
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
		$all_url      = remove_query_arg( array( 'type', 's', 'paged', 'alert', 'user_id', 'from', 'to' ) );
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
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" style="vertical-align: 2px;padding: 0 5px;"><?php _e( "From", "realty-bloc-log" ); ?></label>
                <input type="text" size="18" name="from" data-wps-date-picker="from" value="<?php echo( isset( $_REQUEST['from'] ) ? $_REQUEST['from'] : '' ); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
                <label for="bulk-action-selector-top" style="vertical-align: 2px;padding: 0 5px;"><?php _e( "to", "realty-bloc-log" ); ?></label>
                <input type="text" size="18" name="to" data-wps-date-picker="to" value="<?php echo( isset( $_REQUEST['to'] ) ? $_REQUEST['to'] : '' ); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
				<?php
				$user_list = Helper::get_list_user_log();
				if ( count( $user_list ) > 0 ) {
					?>
                    <label for="bulk-action-selector-top" style="vertical-align: 2px;padding: 0 5px;"><?php _e( "User", "realty-bloc-log" ); ?></label>
                    <select name="user_id" style="float: none;margin: -5px 2px 0 2px;" data-type-show="select2">
                        <option value="0"><?php _e( "Select User", "realty-bloc-log" ); ?></option>
						<?php
						foreach ( Helper::get_list_user_log() as $user_id => $user_name ) {
							$selected = '';
							if ( isset( $_REQUEST['user_id'] ) and $_REQUEST['user_id'] == $user_id ) {
								$selected = "selected";
							}
							echo '<option value="' . $user_id . '" ' . $selected . '>' . $user_name . '</option>';
						}
						?>
                    </select>
				<?php } ?>
                <input type="submit" id="doaction" class="button action" value="<?php _e( "Filter", "realty-bloc-log" ); ?>" style="margin: 0px 5px 0px 5px;">
            </div>
			<?php
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'realty-bloc-log' ),
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
	}

	/**
	 * Bulk and Row Actions
	 */
	public function process_bulk_action() {

		// Row Action Delete
		if ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_nonce' ) ) {
				die( __( "You are not Permission for this action." ) );
			} else {
				self::delete_action( absint( $_REQUEST['del'] ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => Admin::$admin_page_slug, 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
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

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => Admin::$admin_page_slug, 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}
	}

}