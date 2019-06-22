<?php
/**
 * WFCM File Event.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM File Event Class.
 *
 * Handle the event of a single file.
 */
class WFCM_Event_File extends WFCM_Event {

	/**
	 * Constructor.
	 *
	 * @param int|bool $event_id - (Optional) Event id.
	 */
	public function __construct( $event_id = false ) {
		$this->data['content_type'] = 'file'; // Content type.
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
}
