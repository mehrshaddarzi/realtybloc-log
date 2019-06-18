<?php

namespace REALTY_BLOC_LOG;

use REALTY_BLOC_LOG\Admin\User_History;
use REALTY_BLOC_LOG\Admin\Wp_List_Table_Event_Log;

class Admin {
	/**
	 * WP_List_Table object
	 */
	public $event_log_wp_list_table;

	/**
	 * Admin Page slug
	 */
	public static $admin_page_slug;

	/**
	 * Admin_Page constructor.
	 */
	public function __construct() {
		/*
		 * Set Page slug Admin
		 */
		self::$admin_page_slug = 'realtybloc';
		/*
		 * Setup Admin Menu
		 */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		/*
		 * Register Script in Admin Area
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		/**
		 * Set Screen Option For WP_List_Table
		 */
		if ( self::in_page( self::$admin_page_slug ) and ! isset( $_GET['method'] ) ) {
			add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );
		}
		/**
		 * Admin Notice
		 */
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		/**
		 * Set List Table Redirect
		 */
		add_action( 'admin_init', array( $this, 'set_list_table_redirect' ) );
		/**
		 * Admin List Table Css
		 */
		add_action( 'admin_head', array( $this, 'wp_list_table_css' ) );
		/**
		 * Remove Screen Option for custom Page
		 */
		if ( self::in_page( self::$admin_page_slug ) and isset( $_GET['method'] ) ) {
			add_filter( 'screen_options_show_screen', '__return_false' );
		}
		/**
		 * Disable All Notice From Plugins
		 */
		add_action( 'admin_print_scripts', array( $this, 'disable_all_admin_notices' ) );
	}

	/**
	 * Screen Option
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Admin Link
	 *
	 * @param $page
	 * @param array $args
	 * @return string
	 */
	public static function admin_link( $page, $args = array() ) {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . $page ) );
	}

	/**
	 * If in Page in Admin
	 *
	 * @param $page_slug
	 * @return bool
	 */
	public static function in_page( $page_slug ) {
		global $pagenow;
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and $_GET['page'] == $page_slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Load assets file in admin
	 *
	 * @param $hook
	 */
	public function admin_assets( $hook ) {

		//Add Admin Asset
		if ( self::in_page( self::$admin_page_slug ) ) {

			// Load Date Picker
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_localize_script( 'jquery-ui-datepicker', 'wps_i18n_jquery_datepicker', self::localize_jquery_datepicker() );

			// Add Chart Js
			wp_enqueue_script( 'chart.js', \REALTY_BLOC_LOG::$plugin_url . '/dist/js/chartjs/chart.bundle.min.js', false, '2.8.0', false );

			// Select 2
			wp_enqueue_script( 'select2', \REALTY_BLOC_LOG::$plugin_url . '/dist/js/select2/select2.full.min.js', array( 'jquery' ), '4.0.7' );
			wp_enqueue_style( 'select2', \REALTY_BLOC_LOG::$plugin_url . '/dist/css/select2/select2.min.css', array(), '4.0.7' );

			// Load Main Script
			wp_enqueue_style( 'realty-bloc-log', \REALTY_BLOC_LOG::$plugin_url . '/dist/css/admin.min.css', array(), \REALTY_BLOC_LOG::$plugin_version, 'all' );
			wp_enqueue_script( 'realty-bloc-log', \REALTY_BLOC_LOG::$plugin_url . '/dist/js/admin.min.js', array( 'jquery' ), self::version(), false );
			wp_localize_script( 'realty-bloc-log', 'rbl_global', self::global_var_js( $hook ) );
		}

	}

	/*
	 * Set init redirect in wp_list_table
	 */
	public function set_list_table_redirect() {
		if ( self::in_page( Admin::$admin_page_slug ) and ! isset( $_GET['method'] ) ) {

			//Redirect For $_POST Form Performance
			foreach ( array( "s", "user_id", "from", "to" ) as $post ) {
				if ( isset( $_POST[ $post ] ) and ! empty( $_POST[ $post ] ) ) {

					// Create Base Url
					$args = array( 'page' => Admin::$admin_page_slug );

					// Push Arg
					foreach ( array( "s", "user_id", "from", "to" ) as $parameter ) {
						if ( isset( $_POST[ $parameter ] ) and ! empty( $_POST[ $parameter ] ) ) {
							$args[ $parameter ] = urlencode( $_POST[ $parameter ] );
						}
					}

					// Check SUB SUB
					if ( isset( $_REQUEST['type'] ) ) {
						$args['type'] = urlencode( $_REQUEST['type'] );
					}

					// Redirect
					wp_redirect( add_query_arg( $args, admin_url( "admin.php" ) ) );
					exit;
				}
			}

			//Remove Admin Notice From Pagination
			if ( isset( $_GET['alert'] ) and isset( $_GET['paged'] ) ) {
				wp_redirect( remove_query_arg( array( 'alert' ) ) );
				exit;
			}

		}
	}

	/**
	 * Wp List Table Column Css
	 */
	public function wp_list_table_css() {
		if ( self::in_page( self::$admin_page_slug ) ) {
			echo '<style>';
			if ( ! isset( $_GET['method'] ) ) {
				echo '
				table.widefat th.column-event, table.widefat th.column-date {width: 210px;}
				fieldset.columns-prefs { display: none; }
				';
			}
			echo '</style>';
		}
	}

	/**
	 * Admin Notice
	 */
	public static function admin_notice() {
		if ( self::in_page( self::$admin_page_slug ) and isset( $_GET['alert'] ) ) {
			switch ( $_GET['alert'] ) {
				case "delete":
					\REALTY_BLOC_LOG\Core\Utility\Admin::wp_admin_notice( __( "Selected item has been Deleted.", 'realty-bloc-log' ), "success" );
					break;
			}
		}
	}

	/**
	 * Localize jquery datepicker
	 *
	 * @see https://gist.github.com/mehrshaddarzi/7f661baeb5d801961deb8b821157e820
	 */
	public static function localize_jquery_datepicker() {
		global $wp_locale;

		return array(
			'closeText'       => __( 'Done', 'realty-bloc-log' ),
			'currentText'     => __( 'Today', 'realty-bloc-log' ),
			'monthNames'      => Helper::strip_array_indices( $wp_locale->month ),
			'monthNamesShort' => Helper::strip_array_indices( $wp_locale->month_abbrev ),
			'monthStatus'     => __( 'Show a different month', 'realty-bloc-log' ),
			'dayNames'        => Helper::strip_array_indices( $wp_locale->weekday ),
			'dayNamesShort'   => Helper::strip_array_indices( $wp_locale->weekday_abbrev ),
			'dayNamesMin'     => Helper::strip_array_indices( $wp_locale->weekday_initial ),
			'dateFormat'      => 'yy-mm-dd', // Format time for Jquery UI
			'firstDay'        => get_option( 'start_of_week' ),
			'isRTL'           => (int) $wp_locale->is_rtl(),
		);
	}

	/**
	 * Get Version of File
	 *
	 * @param $ver
	 * @return bool
	 */
	public static function version( $ver = false ) {
		if ( $ver ) {
			return $ver;
		} else {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				return time();
			} else {
				return \REALTY_BLOC_LOG::$plugin_version;
			}
		}
	}

	/**
	 * Prepare global WP-Statistics data for use Admin Js
	 *
	 * @param $hook
	 * @return mixed
	 */
	public static function global_var_js( $hook ) {

		//Global Option
		$list['options'] = array(
			'rtl'       => ( is_rtl() ? 1 : 0 ),
			'gutenberg' => ( \REALTY_BLOC_LOG\Core\Utility\Admin::is_gutenberg() ? 1 : 0 )
		);

		// WordPress Current Page
		$list['page'] = $hook;

		// WordPress Admin Page request Params
		if ( isset( $_GET ) ) {
			foreach ( $_GET as $key => $value ) {
				if ( $key != "page" ) {
					$list['request'][ $key ] = $value;
				}
			}
		}

		// Global Lang
		$list['i18n'] = array(
			'more_detail' => __( 'More Details', 'wp-statistics' ),
			'reload'      => __( 'Reload', 'wp-statistics' ),
			'please_wait' => __( 'Please Wait ...', 'wp-statistics' ),
		);

		// Rest-API Meta Box Url
		$list['ajax_url'] = admin_url( 'admin-ajax.php' );
		$list['wp_nonce'] = wp_create_nonce( 'wp_rest' );

		// Return Data JSON
		return $list;
	}

	/**
	 * Set Admin Menu
	 */
	public function admin_menu() {
		// Base Menu
		add_menu_page( __( 'RealtyBloc', 'realty-bloc-log' ), __( 'RealtyBloc', 'realty-bloc-log' ), 'manage_options', self::$admin_page_slug, array( $this, 'admin_page' ), 'dashicons-album', 6 );

		// Event Log
		$hook = add_submenu_page( self::$admin_page_slug, __( 'Event Log', 'realty-bloc-log' ), __( 'Event Log', 'realty-bloc-log' ), 'manage_options', self::$admin_page_slug, array( $this, 'admin_page' ) );
		add_action( "load-$hook", array( $this, 'screen_option' ) );

		// Setting Page
		add_submenu_page( self::$admin_page_slug, __( 'Settings', 'realty-bloc-log' ), __( 'Settings', 'realty-bloc-log' ), 'manage_options', self::$admin_page_slug . '-option', array( Settings::instance(), 'wedevs_plugin_page' ) );
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		if ( ! isset( $_GET['method'] ) ) {

			//Set Screen Option
			$option = 'per_page';
			$args   = array( 'label' => __( "Item Per Page", 'realty-bloc-log' ), 'default' => 10, 'option' => 'rbl_event_log_per_page' );
			add_screen_option( $option, $args );

			//Load WP_List_Table
			$this->event_log_wp_list_table = new Wp_List_Table_Event_Log();
			$this->event_log_wp_list_table->prepare_items();
		}

	}

	/**
	 * Admin Page
	 */
	public function admin_page() {
		if ( ! isset( $_GET['method'] ) ) {
			self::wp_list_table( $this->event_log_wp_list_table );
		} else {

			// Check User History Page
			if ( $_GET['method'] == "user-history" and isset( $_GET['user_id'] ) and is_numeric( $_GET['user_id'] ) ) {

				// Check Number User Event
				$number = Event::get_event_number( array( 'user_id' => $_GET['user_id'] ) );
				if ( $number > 0 ) {
					User_History::view();
				}
			}
		}
	}

	/**
	 * WP List Table static
	 * @param $obj
	 */
	public static function wp_list_table( $obj ) {
		?>
        <div class="wrap wps_actions">
            <h1 class="rbl-heading-inline"><?php echo __( "Event Log", 'realty-bloc-log' ); ?></h1>
            <hr class="wp-header-end">

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns">
                    <div>
                        <div class="meta-box-sortables ui-sortable">
							<?php $obj->views(); ?>
                            <form method="post" action="<?php echo remove_query_arg( array( 'alert' ) ); ?>">
								<?php
								$obj->display();
								?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
		<?php
	}

	/**
	 * Disable All Admin Notice in list table page
	 */
	public function disable_all_admin_notices() {
		global $wp_filter;
		if ( ( self::in_page( 'realtybloc-option' ) || self::in_page( self::$admin_page_slug ) ) and ! isset( $_GET['alert'] ) ) {
			if ( isset( $wp_filter['user_admin_notices'] ) ) {
				unset( $wp_filter['user_admin_notices'] );
			}
			if ( isset( $wp_filter['admin_notices'] ) ) {
				unset( $wp_filter['admin_notices'] );
			}
			if ( isset( $wp_filter['all_admin_notices'] ) ) {
				unset( $wp_filter['all_admin_notices'] );
			}
		}
	}

}