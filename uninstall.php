<?php
/**
 * WFCM Uninstall
 *
 * Uninstalling WFCM deletes monitoring data and options.
 *
 * @package wfcm
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'wfcm_monitor_file_changes' );

if ( get_option( 'wfcm-delete-data', false ) ) {
	global $wpdb;

	// Delete wfcm options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wfcm\-%';" );

	// Delete wfcm_file_event posts + data.
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'wfcm_file_event';" );
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );
}
