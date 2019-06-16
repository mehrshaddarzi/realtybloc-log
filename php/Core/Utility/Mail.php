<?php

namespace REALTY_BLOC_LOG\Core\Utility;
use REALTY_BLOC_LOG\Core\WP_Mail;

class Mail {
	/**
	 * Send Email
	 *
	 * @param $to
	 * @param $subject
	 * @param $content
	 * @return bool
	 */
	public static function send_mail( $to, $subject, $content ) {

		//Email Template
		$email_template = wp_normalize_path( \REALTY_BLOC_LOG::$plugin_path . '/template/email.php' );
		if ( trim( \REALTY_BLOC_LOG::$Template_Engine ) != "" ) {
			$template = wp_normalize_path( path_join( get_template_directory(), '/includes/my_file.php' ) );
			if ( file_exists( $template ) ) {
				$email_template = $template;
			}
		}

		//Get option Send Mail
		$opt = get_option( 'wp_reviews_email_opt' );

		//Set To Admin
		if ( $to == "admin" ) {
			$to = get_bloginfo( 'admin_email' );
		}

		//Email from
		$from_name  = $opt['from_name'];
		$from_email = $opt['from_email'];

		//Template Arg
		$template_arg = array(
			'title'       => $subject,
			'logo'        => $opt['email_logo'],
			'content'     => $content,
			'site_url'    => home_url(),
			'site_title'  => get_bloginfo( 'name' ),
			'footer_text' => $opt['email_footer'],
			'is_rtl'      => ( is_rtl() ? true : false )
		);

		//Send Email
		try {
			WP_Mail::init()->from( '' . $from_name . ' <' . $from_email . '>' )->to( $to )->subject( $subject )->template( $email_template, $template_arg )->send();
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

}