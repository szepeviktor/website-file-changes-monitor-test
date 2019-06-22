<?php
/**
 * About page.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * About page class.
 */
class WFCM_Admin_About {

	/**
	 * Page tabs.
	 *
	 * @var array
	 */
	private static $tabs = array();

	/**
	 * Set tabs of the page.
	 */
	private static function set_tabs() {
		self::$tabs = apply_filters(
			'wfcm_admin_about_page_tabs',
			array(
				'help'        => array(
					'title' => __( 'Help', 'website-file-changes-monitor' ),
					'link'  => self::get_page_url(),
					'view'  => 'html-admin-help.php',
				),
				'about'       => array(
					'title' => __( 'About', 'website-file-changes-monitor' ),
					'link'  => add_query_arg( 'tab', 'about', self::get_page_url() ),
					'view'  => 'html-admin-about.php',
				),
				'system-info' => array(
					'title' => __( 'System Info', 'website-file-changes-monitor' ),
					'link'  => add_query_arg( 'tab', 'system-info', self::get_page_url() ),
					'view'  => 'html-admin-system-info.php',
				),
			)
		);
	}

	/**
	 * Get active tab.
	 *
	 * @return string
	 */
	private static function get_active_tab() {
		return isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'help'; // phpcs:ignore
	}

	/**
	 * Return page url.
	 *
	 * @return string
	 */
	public static function get_page_url() {
		return add_query_arg( 'page', 'wfcm-about', admin_url( 'admin.php' ) );
	}

	/**
	 * Display the page.
	 */
	public static function output() {
		self::set_tabs();

		$suffix = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? '' : '.min'; // Check for debug mode.

		wp_enqueue_style(
			'wfcm-settings-styles',
			WFCM_BASE_URL . 'assets/css/dist/build.settings' . $suffix . '.css',
			array(),
			( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/css/dist/build.settings.css' ) : WFCM_VERSION
		);

		require_once trailingslashit( dirname( __FILE__ ) ) . 'views/html-admin-about-wrapper.php';
	}

	/**
	 * Get system info.
	 *
	 * @return string
	 */
	private static function get_system_info() {
		// System info.
		global $wpdb;

		$sysinfo = '### System Info → Begin ###' . "\n\n";

		// Start with the basics.
		$sysinfo .= '-- Site Info --' . "\n\n";
		$sysinfo .= 'Site URL (WP Address):    ' . site_url() . "\n";
		$sysinfo .= 'Home URL (Site Address):  ' . home_url() . "\n";
		$sysinfo .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

		// Browser information.
		if ( ! class_exists( 'WFCM_Helper_Browser' ) && file_exists( trailingslashit( dirname( __FILE__ ) ) . 'about/class-wfcm-helper-browser.php' ) ) {
			require_once trailingslashit( dirname( __FILE__ ) ) . 'about/class-wfcm-helper-browser.php';

			$browser  = new WFCM_Helper_Browser();
			$sysinfo .= "\n" . '-- User Browser --' . "\n\n";
			$sysinfo .= $browser;
		}

		// Get theme info.
		$theme_data   = wp_get_theme();
		$theme        = $theme_data->Name . ' ' . $theme_data->Version; // phpcs:ignore
		$parent_theme = $theme_data->Template; // phpcs:ignore
		if ( ! empty( $parent_theme ) ) {
			$parent_theme_data = wp_get_theme( $parent_theme );
			$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version; // phpcs:ignore
		}

		// Language information.
		$locale = get_locale();

		// WordPress configuration.
		$sysinfo .= "\n" . '-- WordPress Configuration --' . "\n\n";
		$sysinfo .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
		$sysinfo .= 'Language:                 ' . ( ! empty( $locale ) ? $locale : 'en_US' ) . "\n";
		$sysinfo .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
		$sysinfo .= 'Active Theme:             ' . $theme . "\n";
		if ( $parent_theme !== $theme ) {
			$sysinfo .= 'Parent Theme:             ' . $parent_theme . "\n";
		}
		$sysinfo .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

		// Only show page specs if frontpage is set to 'page'.
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$front_page_id = (int) get_option( 'page_on_front' );
			$blog_page_id  = (int) get_option( 'page_for_posts' );

			$sysinfo .= 'Page On Front:            ' . ( 0 !== $front_page_id ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
			$sysinfo .= 'Page For Posts:           ' . ( 0 !== $blog_page_id ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
		}

		$sysinfo .= 'ABSPATH:                  ' . ABSPATH . "\n";
		$sysinfo .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
		$sysinfo .= 'WP Memory Limit:          ' . WP_MEMORY_LIMIT . "\n";

		// Get plugins that have an update.
		$updates = get_plugin_updates();

		// Must-use plugins.
		// NOTE: MU plugins can't show updates!
		$muplugins = get_mu_plugins();
		if ( count( $muplugins ) > 0 ) {
			$sysinfo .= "\n" . '-- Must-Use Plugins --' . "\n\n";

			foreach ( $muplugins as $plugin => $plugin_data ) {
				$sysinfo .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
			}
		}

		// WordPress active plugins.
		$sysinfo .= "\n" . '-- WordPress Active Plugins --' . "\n\n";

		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( ! in_array( $plugin_path, $active_plugins ) ) { // phpcs:ignore
				continue;
			}

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		// WordPress inactive plugins.
		$sysinfo .= "\n" . '-- WordPress Inactive Plugins --' . "\n\n";

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $active_plugins ) ) { // phpcs:ignore
				continue;
			}

			$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		if ( is_multisite() ) {
			// WordPress Multisite active plugins.
			$sysinfo .= "\n" . '-- Network Active Plugins --' . "\n\n";

			$plugins        = wp_get_active_network_plugins();
			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

			foreach ( $plugins as $plugin_path ) {
				$plugin_base = plugin_basename( $plugin_path );

				if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
					continue;
				}

				$update   = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
				$plugin   = get_plugin_data( $plugin_path );
				$sysinfo .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
			}
		}

		// Server configuration.
		$sysinfo .= "\n" . '-- Webserver Configuration --' . "\n\n";
		$sysinfo .= 'PHP Version:              ' . PHP_VERSION . "\n";
		$sysinfo .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";

		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : false;

		if ( $server_software ) {
			$sysinfo .= 'Webserver Info:           ' . $server_software . "\n";
		} else {
			$sysinfo .= 'Webserver Info:           Global $_SERVER array is not set.' . "\n";
		}

		// PHP configs.
		$sysinfo .= "\n" . '-- PHP Configuration --' . "\n\n";
		$sysinfo .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
		$sysinfo .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
		$sysinfo .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
		$sysinfo .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
		$sysinfo .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
		$sysinfo .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
		$sysinfo .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

		// WFCM options.
		$sysinfo .= "\n" . '-- WFCM Options --' . "\n\n";
		$options  = self::get_wfcm_options();

		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $name => $value ) {
				$sysinfo .= 'Option: ' . $name . "\n";
				$sysinfo .= 'Value:  ' . $value . "\n\n";
			}
		}

		$sysinfo .= "\n" . '### System Info → End ###' . "\n\n";

		return $sysinfo;
	}

	/**
	 * Query WFCM Options from DB.
	 *
	 * @return array - WFCM Options array.
	 */
	private static function get_wfcm_options() {
		// Get options transient.
		$wfcm_options = get_transient( 'wfcm_options' );

		// If options transient is not set then query and set options.
		if ( false === $wfcm_options ) {
			// Get raw options from DB.
			$raw_options = self::query_wfcm_options();

			$wfcm_options = array();

			if ( ! empty( $raw_options ) && is_array( $raw_options ) ) {
				foreach ( $raw_options as $option ) {
					if ( isset( $option->option_name ) && isset( $option->option_value ) ) {
						if ( false !== strpos( $option->option_name, 'wfcm-local-files-' ) ) {
							$wfcm_options[ $option->option_name ] = count( maybe_unserialize( $option->option_value ) );
						} else {
							$wfcm_options[ $option->option_name ] = $option->option_value;
						}
					}
				}
			}

			// Store the results in a transient.
			set_transient( 'wfcm_options', $wfcm_options, DAY_IN_SECONDS );
		}

		return $wfcm_options;
	}

	/**
	 * Query WFCM Options from DB.
	 *
	 * @return array - Array of options.
	 */
	private static function query_wfcm_options() {
		global $wpdb;

		// Set table name.
		$options_table = $wpdb->prefix . 'options';

		// Query the options.
		return $wpdb->get_results( "SELECT * FROM $options_table WHERE option_name LIKE '%wfcm%'" ); // phpcs:ignore
	}
}
