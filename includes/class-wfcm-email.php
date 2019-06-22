<?php
/**
 * WFCM Emailer.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Emailer class.
 */
class WFCM_Email {

	/**
	 * Send Email.
	 *
	 * @param string $to      - Email to.
	 * @param string $subject - Email subject.
	 * @param string $message - Email message.
	 * @return bool
	 */
	public static function send( $to, $subject, $message ) {
		add_filter( 'wp_mail_content_type', array( __CLASS__, 'set_html_content_type' ) );

		$result = wp_mail( $to, $subject, $message );

		remove_filter( 'wp_mail_content_type', array( __CLASS__, 'set_html_content_type' ) );
		return $result;
	}

	/**
	 * Filter the mail content type.
	 */
	public static function set_html_content_type() {
		return 'text/html';
	}
}
