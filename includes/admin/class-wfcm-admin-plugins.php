<?php
/**
 * WFCM Admin Plugins.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin Plugins Class.
 *
 * This class monitors the plugin install, uninstall, and
 * update events for file changes monitoring.
 */
class WFCM_Admin_Plugins {

	/**
	 * List of plugins already installed.
	 *
	 * @var array
	 */
	private $old_plugins = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$has_permission = ( current_user_can( 'install_plugins' ) || current_user_can( 'delete_plugins' ) || current_user_can( 'update_plugins' ) );

		if ( $has_permission ) {
			add_action( 'admin_init', array( $this, 'set_old_plugins' ) );
			add_action( 'shutdown', array( $this, 'monitor_plugin_events' ) );
		}
	}

	/**
	 * Set Old Plugins.
	 */
	public function set_old_plugins() {
		$this->old_plugins = get_plugins();
	}

	/**
	 * Monitor Plugin Events.
	 */
	public function monitor_plugin_events() {
		global $pagenow;

		// Set initial variables.
		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : false; // @codingStandardsIgnoreLine
		$is_plugins_page = false;
		$is_update_page  = false;

		if ( 'update.php' === $pagenow ) {
			$action         = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false; // @codingStandardsIgnoreLine
			$is_update_page = true;
		} elseif ( 'plugins.php' === $pagenow ) {
			$is_plugins_page = true;
		}

		$install_actions = array( 'install-plugin', 'upload-plugin' );
		$update_actions  = array( 'upgrade-plugin', 'update-plugin', 'update-selected' );

		// Handle plugin install event.
		if ( in_array( $action, $install_actions, true ) && current_user_can( 'install_plugins' ) ) {
			// Get installed plugin.
			$plugin       = array_values( array_diff( array_keys( get_plugins() ), array_keys( $this->old_plugins ) ) );
			$added_plugin = reset( $plugin );

			if ( false !== $added_plugin ) {
				wfcm_add_site_plugin( dirname( $added_plugin ) );
			}
		}

		// Handle plugin uninstall event.
		if ( 'delete-plugin' === $action && current_user_can( 'delete_plugins' ) && isset( $_POST['plugin'] ) ) { // @codingStandardsIgnoreLine
			$deleted_plugin = sanitize_text_field( wp_unslash( $_POST['plugin'] ) ); // @codingStandardsIgnoreLine
			$deleted_plugin = dirname( $deleted_plugin );

			if ( $deleted_plugin ) {
				wfcm_skip_plugin_scan( $deleted_plugin, 'uninstall' );
				wfcm_remove_site_plugin( $deleted_plugin );
			}
		} elseif ( $is_plugins_page && isset( $_POST['verify-delete'] ) && 'delete-selected' === $action && isset( $_POST['checked'] ) ) { // phpcs:ignore
			// Get plugins.
			$plugins = array_map( 'sanitize_text_field', wp_unslash( $_POST['checked'] ) ); // phpcs:ignore

			foreach ( $plugins as $plugin ) {
				$deleted_plugin = dirname( $plugin );

				if ( $deleted_plugin ) {
					wfcm_skip_plugin_scan( $deleted_plugin, 'uninstall' );
					wfcm_remove_site_plugin( $deleted_plugin );
				}
			}
		}

		// Handle plugin update event.
		if ( in_array( $action, $update_actions, true ) && current_user_can( 'update_plugins' ) && isset( $_POST['plugin'] ) ) { // @codingStandardsIgnoreLine
			$updated_plugin = sanitize_text_field( wp_unslash( $_POST['plugin'] ) ); // @codingStandardsIgnoreLine
			$updated_plugin = dirname( $updated_plugin );

			if ( $updated_plugin ) {
				wfcm_skip_plugin_scan( $updated_plugin, 'update' );
			}
		} elseif ( $is_update_page && in_array( $action, $update_actions, true ) && current_user_can( 'update_plugins' ) && isset( $_GET['plugins'] ) ) { // phpcs:ignore
			$plugins = sanitize_text_field( wp_unslash( $_GET['plugins'] ) ); // phpcs:ignore
			$plugins = explode( ',', $plugins );

			foreach ( $plugins as $plugin ) {
				$updated_plugin = dirname( $plugin );

				if ( $updated_plugin ) {
					wfcm_skip_plugin_scan( $updated_plugin, 'update' );
				}
			}
		}
	}
}

new WFCM_Admin_Plugins();
