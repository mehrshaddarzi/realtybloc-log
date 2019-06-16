<?php

namespace REALTY_BLOC_LOG;

use REALTY_BLOC_LOG\Core\SettingAPI;
use REALTY_BLOC_LOG\Core\Utility\Post;

/**
 * Class Settings
 * @see https://github.com/tareq1988/wordpress-settings-api-class
 */
class Settings {
	/**
	 * Setting Page
	 *
	 * @var mixed
	 */
	public $setting;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Admin_Setting_Api constructor.
	 */
	public function __construct() {
		/**
		 * Set Admin Setting
		 */
		add_action( 'admin_init', array( $this, 'wedevs_admin_init' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function wedevs_plugin_page() {

		echo '<div class="wrap">';
		settings_errors();

		$this->setting->show_navigation();
		$this->setting->show_forms();

		echo '</div>';
	}

	/**
	 * Registers settings section and fields
	 */
	public function wedevs_admin_init() {
		$sections = array(
			array(
				'id'    => 'wp_reviews_email_opt',
				'desc'  => __( 'Basic email settings', 'realty-bloc-log' ),
				'title' => __( 'Email', 'realty-bloc-log' )
			),
			array(
				'id'    => 'wp_reviews_insurance_opt',
				'title' => __( 'General', 'realty-bloc-log' )
			),
			array(
				'id'    => 'wp_plugin_help',
				'save'  => false,
				'title' => __( 'Help', 'realty-bloc-log' )
			),
		);

		$fields = array(
			'wp_reviews_email_opt'     => array(
				array(
					'name'    => 'from_email',
					'label'   => __( 'From Email', 'realty-bloc-log' ),
					'type'    => 'text',
					'default' => get_option( 'admin_email' )
				),
				array(
					'name'    => 'from_name',
					'label'   => __( 'From Name', 'realty-bloc-log' ),
					'type'    => 'text',
					'default' => get_option( 'blogname' )
				),
				array(
					'name'         => 'email_logo',
					'label'        => __( 'Email Logo', 'realty-bloc-log' ),
					'type'         => 'file',
					'button_label' => 'choose logo image'
				),
				array(
					'name'    => 'email_body',
					'label'   => __( 'Email Body', 'realty-bloc-log' ),
					'type'    => 'wysiwyg',
					'default' => '<p>Hi, [fullname] </p> For Accept Your Reviews Please Click Bottom Link : <p> [link]</p>',
					'desc'    => 'Use This Shortcode :<br /> [fullname] : User Name <br /> [link] : Accept email link'
				),
				array(
					'name'    => 'email_footer',
					'label'   => __( 'Email Footer Text', 'realty-bloc-log' ),
					'type'    => 'wysiwyg',
					'default' => 'All rights reserved',
				)
			),
			'wp_reviews_insurance_opt' => array(
				array(
					'name'    => 'is_auth_ip',
					'label'   => __( 'IP Validation', 'realty-bloc-log' ),
					'type'    => 'select',
					'desc'    => 'Each user can only have one vote',
					'options' => array(
						'0' => 'No',
						'1' => 'yes'
					)
				),
				array(
					'name'    => 'email_auth',
					'label'   => __( 'Confirmation email', 'realty-bloc-log' ),
					'type'    => 'select',
					'desc'    => 'The user must click confirmation email',
					'options' => array(
						'0' => 'No',
						'1' => 'yes'
					)
				),
				array(
					'name'    => 'star_color',
					'label'   => __( 'Star Rating color', 'realty-bloc-log' ),
					'type'    => 'color',
					'default' => '#f2b01e'
				),
				array(
					'name'    => 'thanks_text',
					'label'   => __( 'Thanks you Text', 'realty-bloc-log' ),
					'type'    => 'wysiwyg',
					'default' => 'Thanks you for this vote.'
				),
				array(
					'name'    => 'error_ip',
					'label'   => __( 'Duplicate ip error', 'realty-bloc-log' ),
					'type'    => 'textarea',
					'default' => 'Each user can only have one vote'
				),
				array(
					'name'    => 'email_subject',
					'label'   => __( 'Email subject for Confirm', 'realty-bloc-log' ),
					'type'    => 'text',
					'default' => 'confirm your reviews',
					'desc'    => 'Use This Shortcode :</br> [fullname] : User Name<br /> [sitename] : Site Name',
				),
				array(
					'name'    => 'email_thanks_text',
					'label'   => __( 'Thanks Confirm Text', 'realty-bloc-log' ),
					'type'    => 'text',
					'default' => 'Thank You For Your Reviews.',
				),
				array(
					'name'    => 'thanks_you_page_submit',
					'label'   => __( 'Thanks submit page', 'realty-bloc-log' ),
					'desc'   => __( 'Redirect To this Page after submit reviews by user', 'realty-bloc-log' ),
					'type'    => 'select',
					'options' => Post::get_list_post( 'page' ),
				),
				array(
					'name'    => 'thanks_you_page_confirm',
					'label'   => __( 'Thanks confirm page', 'realty-bloc-log' ),
					'desc'   => __( 'Redirect To this Page after confirm email by user', 'realty-bloc-log' ),
					'type'    => 'select',
					'options' => Post::get_list_post( 'page' ),
				),
			),
			'wp_plugin_help'           => array(
				array(
					'name'  => 'html_help_shortcode',
					'label' => 'ShortCode List',
					'desc'  => 'You Can using bottom shortcode in wordpress : <br /><br />
 <table border="0" class="widefat">
  <tr>
 <td> [reviews-form]</td>
 <td>For Show Review Form</td>
</tr>
 <tr>
 <td>[reviews-insurance]</td>
 <td>List Of insurance With Rating Averag e.g : [reviews-insurance order="DESC"] <br />
 	To display alphabetically use this code : [reviews-insurance order="DESC" from="A" to="K"]
 </td>
</tr>
<tr>
 <td>[reviews-list]</td>
 <td>List Of Review For Custom insurance . e.g : [reviews-list insurance_id=10 order="ASC" number="50"]</td>
</tr>
<tr>
 <td>[last-comments]</td>
 <td>show Last Comment From Company Post Type . e.g : [last-comments number="50"]</td>
</tr>
</table>
',
					'type'  => 'html'
				),
				array(
					'name'  => 'html_help_custom template',
					'label' => 'Custom Template',
					'desc'  => 'for Custom Template according to your theme style : <br /> <br />
 <table border="0" class="widefat">
  <tr>
  <td>Copy `template` folder to root dir theme and rename folder to `wp-reviews`. then change your html code. :)</td>
  </tr>
  </table>
',
					'type'  => 'html'
				),
			)
		);

		$this->setting = new SettingAPI();

		//set sections and fields
		$this->setting->set_sections( $sections );
		$this->setting->set_fields( $fields );

		//initialize them
		$this->setting->admin_init();
	}

}