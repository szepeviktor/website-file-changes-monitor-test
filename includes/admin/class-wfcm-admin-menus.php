<?php
/**
 * Admin Menus.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin Admin Menus Class.
 */
class WFCM_Admin_Menus {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 10 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 20 );
		add_action( 'admin_menu', array( $this, 'about_menu' ), 30 );
		add_action( 'admin_menu', array( $this, 'add_events_count' ), 40 );

		add_action( 'admin_print_styles', array( $this, 'admin_styles' ) );
		add_filter( 'plugin_action_links_' . WFCM_BASE_NAME, array( $this, 'shortcut_links' ), 10, 1 );
		add_action( 'wp_ajax_wfcm_dismiss_instant_scan_modal', array( $this, 'dismiss_instant_scan_modal' ) );
	}

	/**
	 * Add Plugin Admin Menu.
	 *
	 * Admin menu pages and sub-pages:
	 *
	 * 1. Files Monitor.
	 * 2. Settings.
	 * 3. Help & About.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Website File Changes Monitor', 'website-file-changes-monitor' ),
			__( 'Files Monitor', 'website-file-changes-monitor' ),
			'manage_options',
			'wfcm-file-changes',
			null,
			WFCM_BASE_URL . 'assets/img/wfcm-menu-icon.svg',
			'75'
		);

		add_submenu_page( 'wfcm-file-changes', __( 'Website File Changes Monitor', 'website-file-changes-monitor' ), __( 'Files Monitor', 'website-file-changes-monitor' ), 'manage_options', 'wfcm-file-changes', array( $this, 'file_changes_page' ) );
	}

	/**
	 * Add Settings Menu.
	 */
	public function settings_menu() {
		$settings_page = add_submenu_page( 'wfcm-file-changes', __( 'Settings', 'website-file-changes-monitor' ), __( 'Settings', 'website-file-changes-monitor' ), 'manage_options', 'wfcm-settings', array( $this, 'settings_page' ) );
		add_action( "load-$settings_page", array( $this, 'settings_page_init' ) );
	}

	/**
	 * Add About Menu.
	 */
	public function about_menu() {
		add_submenu_page( 'wfcm-file-changes', __( 'Help & About', 'website-file-changes-monitor' ), __( 'Help & About', 'website-file-changes-monitor' ), 'manage_options', 'wfcm-about', array( $this, 'about_page' ) );
	}

	/**
	 * Files Monitor Page.
	 */
	public function file_changes_page() {
		WFCM_Admin_File_Changes::output();
	}

	/**
	 * Settings Page.
	 */
	public function settings_page() {
		WFCM_Admin_Settings::output();
	}

	/**
	 * Settings Page Initialized.
	 */
	public function settings_page_init() {
		if ( ! empty( $_POST['submit'] ) ) { // @codingStandardsIgnoreLine
			WFCM_Admin_Settings::save();
		}
	}

	/**
	 * About Page.
	 */
	public function about_page() {
		WFCM_Admin_About::output();
	}

	/**
	 * Add events count to menu.
	 */
	public function add_events_count() {
		global $menu;

		$events_count = wp_count_posts( 'wfcm_file_event' );

		if ( isset( $events_count->private ) && $events_count->private ) {
			$count_html = '<span class="update-plugins"><span class="events-count">' . $events_count->private . '</span></span>';

			foreach ( $menu as $key => $value ) {
				if ( 'wfcm-file-changes' === $menu[ $key ][2] ) {
					$menu[ $key ][0] .= ' ' . $count_html; // phpcs:ignore
					break;
				}
			}
		}
	}

	/**
	 * Print admin styles.
	 */
	public function admin_styles() {
		?>
		<style>#adminmenu .toplevel_page_wfcm-file-changes .wp-menu-image img { padding: 5px 0 0 0; }</style>
		<?php
	}

	/**
	 * Add shortcut links to plugins page.
	 *
	 * @param array $old_links - Array of old links.
	 * @return array
	 */
	public function shortcut_links( $old_links ) {
		$new_links[] = '<a href="' . add_query_arg( 'page', 'wfcm-file-changes', admin_url( 'admin.php' ) ) . '">' . __( 'See File Changes', 'website-file-changes-monitor' ) . '</a>';
		$new_links[] = '<a href="' . add_query_arg( 'page', 'wfcm-settings', admin_url( 'admin.php' ) ) . '">' . __( 'Settings', 'website-file-changes-monitor' ) . '</a>';
		$new_links[] = '<a href="' . add_query_arg( 'page', 'wfcm-about', admin_url( 'admin.php' ) ) . '">' . __( 'Support', 'website-file-changes-monitor' ) . '</a>';
		return array_merge( $new_links, $old_links );
	}

	/**
	 * Ajax handler to dismiss instant scan modal.
	 */
	public function dismiss_instant_scan_modal() {
		check_admin_referer( 'wp_rest', 'security' );
		wfcm_save_setting( 'dismiss-instant-scan-modal', true );
		die();
	}
}

new WFCM_Admin_Menus();
