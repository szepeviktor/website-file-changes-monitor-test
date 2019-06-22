<?php
/**
 * WFCM Directory Event.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM Directory Event Class.
 *
 * Handle the events of directories like when a plugin
 * is installed, updated, or removed, etc.
 */
class WFCM_Event_Directory extends WFCM_Event {

	/**
	 * Constructor.
	 *
	 * @param int|bool $event_id - (Optional) Event id.
	 */
	public function __construct( $event_id = false ) {
		$this->data['content_type']  = 'directory'; // Content type.
		$this->data['event_context'] = ''; // Event context.
		parent::__construct( $event_id );
	}

	/**
	 * Set content type.
	 *
	 * @param string $content_type - Content type.
	 * @return string
	 */
	public function set_content_type( $content_type ) {
		return $this->set_meta( 'content_type', $content_type );
	}

	/**
	 * Returns content type.
	 *
	 * @return string
	 */
	public function get_content_type() {
		return $this->get_meta( 'content_type' );
	}

	/**
	 * Set content type.
	 *
	 * @param string $event_context - Content type.
	 * @return string
	 */
	public function set_event_context( $event_context ) {
		return $this->set_meta( 'event_context', $event_context );
	}

	/**
	 * Returns event context.
	 *
	 * @return string
	 */
	public function get_event_context() {
		return $this->get_meta( 'event_context' );
	}
}
