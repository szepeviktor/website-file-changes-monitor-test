<?php
/**
 * Website File Changes Monitor.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Plugin Class.
 */
final class Website_File_Changes_Monitor {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.1';

	/**
	 * Single instance of the plugin.
	 *
	 * @var Website_File_Changes_Monitor
	 */
	protected static $instance = null;

	/**
	 * Main WP File Changes Monitor Instance.
	 *
	 * Ensures only one instance of WP File Changes Monitor is loaded or can be loaded.
	 *
	 * @return Website_File_Changes_Monitor
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Contructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->register_hooks();
		do_action( 'website_file_changes_monitor_loaded' );
	}

	/**
	 * Define constants.
	 */
	public function define_constants() {
		$this->define( 'WFCM_VERSION', $this->version );
		$this->define( 'WFCM_BASE_NAME', plugin_basename( WFCM_PLUGIN_FILE ) );
		$this->define( 'WFCM_BASE_URL', trailingslashit( plugin_dir_url( WFCM_PLUGIN_FILE ) ) );
		$this->define( 'WFCM_BASE_DIR', trailingslashit( plugin_dir_path( WFCM_PLUGIN_FILE ) ) );
		$this->define( 'WFCM_REST_NAMESPACE', 'website-file-changes-monitor/v1' );
		$this->define( 'WFCM_OPT_PREFIX', 'wfcm-' );
		$this->define( 'WFCM_MIN_PHP_VERSION', '5.5.0' );
	}

	/**
	 * Define constant if not defined already.
	 *
	 * @param string $name  - Constant name.
	 * @param string $value - Constant value.
	 */
	public function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include plugin files.
	 */
	public function includes() {
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-autoloader.php';
		require_once WFCM_BASE_DIR . 'includes/wfcm-functions.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-post-types.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-monitor.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-rest-api.php';

		// Data stores.
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-data-store.php';
		require_once WFCM_BASE_DIR . 'includes/data-stores/class-wfcm-event-data-store.php';

		if ( is_admin() ) {
			require_once WFCM_BASE_DIR . 'includes/admin/class-wfcm-admin.php';
		}
	}

	/**
	 * Register Hooks.
	 */
	public function register_hooks() {
		register_activation_hook( WFCM_PLUGIN_FILE, 'wfcm_install' );
		add_action( 'admin_init', array( $this, 'redirect_on_activation' ) );
	}

	/**
	 * Redirect on activation.
	 */
	public function redirect_on_activation() {
		if ( wfcm_get_setting( 'redirect-on-activate', false ) ) {
			wfcm_delete_setting( 'redirect-on-activate' );
			$redirect_url = add_query_arg( 'page', 'wfcm-file-changes', admin_url( 'admin.php' ) );
			wp_safe_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Error Logger
	 *
	 * Logs given input into debug.log file in debug mode.
	 *
	 * @param mixed $message - Error message.
	 */
	public function error_log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}
}
