<?php
/**
 * Plugin Admin Class File.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin Class.
 *
 * Handles the admin side of the plugin.
 */
class WFCM_Admin {

	/**
	 * Plugin Admin Notices.
	 *
	 * @var array
	 */
	private static $admin_notices = array();

	/**
	 * Allowed HTML.
	 *
	 * @var array
	 */
	private static $allowed_html = array(
		'a' => array(
			'href'   => array(),
			'target' => array(),
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->set_admin_notices();
		add_action( 'init', array( $this, 'include_admin_files' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer_scripts' ) );
	}

	/**
	 * Set admin notices.
	 */
	private function set_admin_notices() {
		self::$admin_notices = apply_filters(
			'wfcm_admin_notices',
			array(
				'wsal' => array(
					'type'    => 'warning',
					'message' => sprintf(
						/* Translators: WordPress file scanning hyperlink */
						__( 'We noticed that the WP Security Audit Log plugin is installed on this website. WP Security Audit Log also alerts you of file changes on your website. Therefore we recommend you to either disable the %s or deactivate this plugin.', 'website-file-changes-monitor' ),
						'<a href="https://www.wpsecurityauditlog.com/support-documentation/wordpress-files-changes-warning-activity-logs/" target="_blank">' . __( 'WordPress file scanning on WP Security Audit Log plugin', 'website-file-changes-monitor' ) . '</a>'
					),
				),
			)
		);
	}

	/**
	 * Include Admin Files.
	 */
	public function include_admin_files() {
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-menus.php';
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-plugins.php';
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-themes.php';
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-system.php';
	}

	/**
	 * Show plugin admin notices (if any).
	 */
	public function show_admin_notices() {
		// Get admin notices option.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( isset( $admin_notices['wsal'] ) && $admin_notices['wsal'] ) {
			$this->display_notice( 'wsal' );
		}
	}

	/**
	 * Display notice.
	 *
	 * @param string $key - Notice key.
	 */
	private function display_notice( $key ) {
		$notice = self::$admin_notices[ $key ];
		?>
		<div id="wfcm-admin-notice-<?php echo esc_attr( $key ); ?>" class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> wfcm-admin-notice is-dismissible">
			<p><?php echo wp_kses( $notice['message'], self::$allowed_html ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render admin footer scripts (if needed).
	 */
	public function admin_footer_scripts() {
		// Check for debug mode.
		$suffix = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? '' : '.min';

		wp_register_script(
			'wfcm-common',
			WFCM_BASE_URL . 'assets/js/dist/common' . $suffix . '.js',
			array(),
			( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/js/dist/common.js' ) : WFCM_VERSION,
			true
		);

		wp_localize_script(
			'wfcm-common',
			'wfcmData',
			array(
				'restNonce'         => wp_create_nonce( 'wp_rest' ),
				'restAdminEndpoint' => rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$admin_notices ),
			)
		);

		wp_enqueue_script( 'wfcm-common' );
	}
}

new WFCM_Admin();
