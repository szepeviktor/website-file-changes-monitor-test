<?php
/**
 * WFCM REST API.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM REST API Class.
 *
 * This class registers and handles the REST API requests of the plugin.
 */
class WFCM_REST_API {

	/**
	 * Monitor events base.
	 *
	 * @var string
	 */
	public static $monitor_base = '/monitor';

	/**
	 * Events base.
	 *
	 * @var string
	 */
	public static $events_base = '/monitor-events';

	/**
	 * Admin notices base.
	 *
	 * @var string
	 */
	public static $admin_notices = '/admin-notices';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_monitor_rest_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_events_rest_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_admin_notices_rest_routes' ) );
	}

	/**
	 * Register Rest Route for Scanning.
	 */
	public function register_monitor_rest_routes() {
		// Start scan route.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$monitor_base . '/start',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'scan_start' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Stop scan route.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$monitor_base . '/stop',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'scan_stop' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Register Rest Route for Scanning.
	 */
	public function register_events_rest_routes() {
		// Register rest route for getting events.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$events_base . '/(?P<event_type>[\S]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_events' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'validate_callback'   => function( $param ) {
					return in_array( $param, array( 'added', 'deleted', 'modified' ), true );
				},
			)
		);

		// Register rest route for removing an event.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$events_base . '/(?P<event_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_event' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'validate_callback'   => function( $param ) {
					return is_numeric( $param );
				},
				'args'                => array(
					'exclude' => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Whether to exclude the content in future scans or not', 'website-file-changes-monitor' ),
					),
				),
			)
		);
	}

	/**
	 * Register rest route for admin notices.
	 */
	public function register_admin_notices_rest_routes() {
		// Register rest route dismissing admin notice.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$admin_notices . '/(?P<noticeKey>[\S]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'dismiss_admin_notice' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'validate_callback'   => function( $param ) {
					return filter_var( $param, FILTER_SANITIZE_STRING );
				},
			)
		);
	}

	/**
	 * REST API callback for start scan request.
	 *
	 * @return boolean
	 */
	public function scan_start() {
		// Run a manual scan of all directories.
		for ( $dir = 0; $dir < 7; $dir++ ) {
			if ( ! $this->check_scan_stop() ) {
				wfcm_get_monitor()->scan_file_changes( true, $dir );
			} else {
				break;
			}
		}

		wfcm_delete_setting( 'scan-stop' );
		return true;
	}

	/**
	 * REST API callback for stop scan request.
	 *
	 * @return boolean
	 */
	public function scan_stop() {
		wfcm_save_setting( 'scan-stop', true );
		return true;
	}

	/**
	 * Check if scan stop flag option is set.
	 *
	 * @return string|null
	 */
	private function check_scan_stop() {
		global $wpdb;
		$options_table = $wpdb->prefix . 'options';
		return $wpdb->get_var( "SELECT option_value FROM $options_table WHERE option_name = 'wfcm-scan-stop'" ); // phpcs:ignore
	}

	/**
	 * REST API callback for fetching created file events.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 * @return WP_Error|string - JSON string of events.
	 */
	public function get_events( $rest_request ) {
		// Get event params from request object.
		$event_type = $rest_request->get_param( 'event_type' );
		$paged      = $rest_request->get_param( 'paged' );
		$per_page   = $rest_request->get_param( 'per-page' );

		// Validate request variables.
		$paged    = is_int( $paged ) ? $paged : (int) $paged;
		$per_page = 'false' === $per_page ? false : (int) $per_page;

		if ( ! $event_type ) {
			return new WP_Error( 'wfcm_empty_event_type', __( 'No event type specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Get event type stored per page option.
		$per_page_opt_name = $event_type . '-per-page';
		$stored_per_page   = wfcm_get_setting( $per_page_opt_name );

		if ( false === $per_page ) {
			if ( ! $stored_per_page ) {
				$per_page = 10;
			} else {
				$per_page = $stored_per_page;
			}
		} elseif ( $per_page !== $stored_per_page ) {
			wfcm_save_setting( $per_page_opt_name, $per_page );
		}

		// Set events query arguments.
		$event_args = array(
			'status'         => 'unread',
			'event_type'     => $event_type,
			'posts_per_page' => $per_page,
			'paginate'       => true,
			'paged'          => $paged,
		);

		// Query events.
		$events_query = wfcm_get_events( $event_args );

		// Convert events for JS response.
		$events_query->events = wfcm_get_events_for_js( $events_query->events );

		$response = new WP_REST_Response( $events_query );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * REST API callback for marking events as read.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_event( $rest_request ) {
		// Get event id from request.
		$event_id = $rest_request->get_param( 'event_id' );

		if ( ! $event_id ) {
			return new WP_Error( 'wfcm_empty_event_id', __( 'No event id specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Get request body to check if event is excluded.
		$request_body = $rest_request->get_body();
		$request_body = json_decode( $request_body );
		$is_excluded  = isset( $request_body->exclude ) ? $request_body->exclude : false;

		if ( $is_excluded ) {
			// Get event content type.
			$event        = wfcm_get_event( $event_id );
			$content_type = $event->get_content_type();

			if ( 'file' === $content_type ) {
				$excluded_content   = wfcm_get_setting( 'scan-exclude-files', array() );
				$excluded_content[] = basename( $event->get_event_title() );
				wfcm_save_setting( 'scan-exclude-files', $excluded_content );
			} elseif ( 'directory' === $content_type ) {
				$excluded_content   = wfcm_get_setting( 'scan-exclude-dirs', array() );
				$excluded_content[] = $event->get_event_title();
				wfcm_save_setting( 'scan-exclude-dirs', $excluded_content );
			}
		}

		// Delete the event.
		if ( wp_delete_post( $event_id, true ) ) {
			$response = array( 'success' => true );
		} else {
			$response = array( 'success' => false );
		}

		$response = new WP_REST_Response( $response );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * REST API callback for dismissing admin notice.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function dismiss_admin_notice( $rest_request ) {
		// Get admin notice id.
		$notice_key = $rest_request->get_param( 'noticeKey' );

		if ( ! $notice_key ) {
			return new WP_Error( 'wfcm_empty_admin_notice_id', __( 'No admin notice key specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( isset( $admin_notices[ $notice_key ] ) ) {
			// Unset the notice.
			unset( $admin_notices[ $notice_key ] );

			// Save notice option.
			wfcm_save_setting( 'admin-notices', $admin_notices );

			// Prepare response.
			$response = array( 'success' => true );
		} else {
			$response = array( 'success' => false );
		}

		return new WP_REST_Response( $response, 200 );
	}
}

new WFCM_REST_API();
