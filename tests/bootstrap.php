<?php

define( 'WPINC', 'wp-includes' );
define( 'WP_CONTENT_DIR', 'wp-content' );
define( 'WP_PLUGIN_DIR', 'wp-content/plugins' );
define( 'WP_MEMORY_LIMIT', 0 );

define( 'WFCM_PLUGIN_FILE', dirname( __DIR__ ) . '/website-file-changes-monitor.php' );

define( 'WFCM_VERSION', '1.0.0' );
define( 'WFCM_BASE_NAME', 'website-file-changes-monitor.php' );
define( 'WFCM_BASE_URL', '' );
define( 'WFCM_BASE_DIR', WFCM_PLUGIN_FILE );
define( 'WFCM_REST_NAMESPACE', 'website-file-changes-monitor/v1' );
define( 'WFCM_OPT_PREFIX', 'wfcm-' );
define( 'WFCM_MIN_PHP_VERSION', '5.5.0' );

function wfcm_instance() { return Website_File_Changes_Monitor::instance(); }

class WpSecurityAuditLog {
    static function GetInstance() { return new WpSecurityAuditLog(); }
    function GetGlobalOption( $param1 ) { return ''; }
}
