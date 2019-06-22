<?php
/**
 * Autoloader Class.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Autoloader Class.
 */
class WFCM_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
		$this->include_path = WFCM_BASE_DIR . 'includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class - Class name.
	 * @return string
	 */
	private function get_file_name( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path - File path.
	 * @return bool
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}
		return false;
	}

	/**
	 * Auto-load classes.
	 *
	 * @param string $class - Class name.
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'wfcm_' ) ) {
			return;
		}

		$file = $this->get_file_name( $class );
		$path = '';

		if ( 0 === strpos( $class, 'wfcm_admin' ) ) {
			$path = $this->include_path . 'admin/';
		} elseif ( 0 === strpos( $class, 'wfcm_event' ) ) {
			$path = $this->include_path . 'event/';
		}

		if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new WFCM_Autoloader();
