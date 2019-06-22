<?php
/**
 * WFCM Admin System.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin System Class.
 *
 * This class is responsible for handling system events
 * like wp core updates.
 */
class WFCM_Admin_System {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'wp_core_update' ) );
		add_action( 'automatic_updates_complete', array( $this, 'wp_core_automatic_update' ), 10, 1 );
	}

	/**
	 * Handle WP Core Update Request.
	 */
	public function wp_core_update() {
		global $pagenow;

		if ( 'update-core' !== basename( $pagenow, '.php' ) ) {
			return;
		}

		// Get action.
		// @codingStandardsIgnoreStart
		$action      = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false;
		$new_version = isset( $_POST['version'] ) ? sanitize_text_field( wp_unslash( $_POST['version'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( 'do-core-upgrade' === $action && $new_version ) {
			$old_version = get_bloginfo( 'version' );

			if ( $old_version !== $new_version ) {
				// Get `site_content` option.
				$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

				// Check if the option is instance of stdClass.
				if ( false !== $site_content ) {
					$site_content->skip_core = true; // Set skip core to true to skip file alerts after a core update.
					wfcm_save_setting( WFCM_Settings::$site_content, $site_content ); // Save the option.
				}
			}
		}
	}

	/**
	 * WordPress auto core update.
	 *
	 * @param array $automatic - Automatic update array.
	 */
	public function wp_core_automatic_update( $automatic ) {
		if ( isset( $automatic['core'][0] ) ) {
			$obj         = $automatic['core'][0];
			$old_version = get_bloginfo( 'version' );

			if ( $old_version !== $obj->item->version ) {
				// Get `site_content` option.
				$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

				// Check if the option is instance of stdClass.
				if ( false !== $site_content ) {
					$site_content->skip_core = true; // Set skip core to true to skip file alerts after a core update.
					wfcm_save_setting( WFCM_Settings::$site_content, $site_content ); // Save the option.
				}
			}
		}
	}
}

new WFCM_Admin_System();
