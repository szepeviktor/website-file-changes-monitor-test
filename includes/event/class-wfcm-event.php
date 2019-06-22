<?php
/**
 * WFCM Event.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM Event Abstract Class.
 *
 * This is the base class for the file change events.
 */
abstract class WFCM_Event {

	/**
	 * Event ID.
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Event Title.
	 *
	 * @var string
	 */
	protected $event_title = '';

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	protected $event_type = 'wfcm_file_event';

	/**
	 * Event Data.
	 *
	 * @var array
	 */
	protected $data = array(
		'event_type' => '',       // Event type: added, modified, or deleted.
		'status'     => 'unread', // Event status.
		'content'    => '',       // Content.
	);

	/**
	 * Event Post Object.
	 *
	 * @var WP_Post
	 */
	public $event_post = null;

	/**
	 * Constructor.
	 *
	 * @param int|WP_Post|bool $event - (Optional) Event id.
	 */
	public function __construct( $event = false ) {
		if ( is_numeric( $event ) ) {
			$this->id          = (int) $event;
			$this->event_post  = get_post( $this->id );
			$this->event_title = $this->event_post->post_title;
			$this->load_event_data();
		} elseif ( $event instanceof WP_Post ) {
			$this->id          = $event->ID;
			$this->event_post  = $event;
			$this->event_title = $this->event_post->post_title;
			$this->load_event_data();
		}
	}

	/**
	 * Load event data.
	 */
	protected function load_event_data() {
		$this->reset_event_data();

		foreach ( $this->data as $key => $value ) {
			$get_meta = "get_$key";
			$this->$get_meta();
		}
	}

	/**
	 * Reset event data.
	 */
	protected function reset_event_data() {
		foreach ( $this->data as $key => $value ) {
			$set_meta = "set_$key";
			$this->$set_meta( '' );
		}
	}

	/**
	 * Save event.
	 *
	 * Event is saved in WordPress post table and
	 * event meta in the postmeta table.
	 */
	public function save() {
		// Event post data.
		$event_data = array(
			'post_type'   => $this->event_type,
			'post_title'  => $this->event_title,
			'post_status' => 'private',
			'guid'        => '',
		);

		// Insert new event.
		$this->id = wp_insert_post( $event_data );

		// Set event meta.
		foreach ( $this->data as $meta_key => $value ) {
			$this->save_meta( $meta_key, $value );
		}
	}

	/**
	 * Save Event Meta.
	 *
	 * @param string $key   - Meta key.
	 * @param mixed  $value - Meta value.
	 */
	protected function save_meta( $key, $value ) {
		update_post_meta( $this->id, $key, $value );
	}

	/*********************************************************
	 * Event Setters.
	 *********************************************************/

	/**
	 * Set event id.
	 *
	 * @param string $id - Event id.
	 */
	public function set_event_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Set event title.
	 *
	 * @param string $title - Event title.
	 */
	public function set_event_title( $title ) {
		$this->event_title = $title;
	}

	/**
	 * Set Event Meta.
	 *
	 * @param string $key   - Meta key.
	 * @param mixed  $value - Meta value.
	 * @return mixed
	 */
	protected function set_meta( $key, $value ) {
		if ( isset( $this->data[ $key ] ) ) {
			$this->data[ $key ] = $value;
			return $value;
		}
		return new WP_Error( 'wfcm_invalid_event_data', __( 'Invalid event data.', 'website-file-changes-monitor' ) );
	}

	/**
	 * Set event type; added, modified, or deleted.
	 *
	 * @param string $event_type - Event type.
	 * @return string
	 */
	public function set_event_type( $event_type ) {
		return $this->set_meta( 'event_type', $event_type );
	}

	/**
	 * Set event status; unread or read.
	 *
	 * @param string $status - Event status.
	 * @return string
	 */
	public function set_status( $status ) {
		return $this->set_meta( 'status', $status );
	}

	/**
	 * Set content of event.
	 *
	 * @param stdClass $content - Event content.
	 * @return stdClass
	 */
	public function set_content( $content ) {
		return $this->set_meta( 'content', $content );
	}

	/**
	 * Set content type; file or directory.
	 *
	 * @param string $content_type - Content type.
	 * @return string
	 */
	public function set_content_type( $content_type ) {
		return $this->set_meta( 'content_type', $content_type );
	}

	/*********************************************************
	 * Event Getters.
	 *********************************************************/

	/**
	 * Get event id.
	 *
	 * @return int
	 */
	public function get_event_id() {
		return $this->id;
	}

	/**
	 * Get event title.
	 *
	 * @return string
	 */
	public function get_event_title() {
		return $this->event_title;
	}

	/**
	 * Get Event Meta.
	 *
	 * @param string $key - Meta key.
	 */
	protected function get_meta( $key ) {
		if ( empty( $this->data[ $key ] ) ) {
			$this->data[ $key ] = get_post_meta( $this->id, $key, true );
		}

		return $this->data[ $key ];
	}

	/**
	 * Returns event type.
	 *
	 * @return string
	 */
	public function get_event_type() {
		return $this->get_meta( 'event_type' );
	}

	/**
	 * Returns event status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->get_meta( 'status' );
	}

	/**
	 * Returns content of event.
	 *
	 * @return string
	 */
	public function get_content() {
		return $this->get_meta( 'content' );
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
