<?php
/**
 * WFCM Events Query.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Events Query Class.
 */
class WFCM_Event_Query {

	/**
	 * Query variables.
	 *
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Constructor.
	 *
	 * @param array $args - Array of query arguments.
	 */
	public function __construct( $args = array() ) {
		$this->query_vars = wp_parse_args( $args, $this->get_default_query_vars() );
	}

	/**
	 * Returns query arguments.
	 *
	 * @return array
	 */
	private function get_args() {
		return $this->query_vars;
	}

	/**
	 * Returns default arguments for quering events from WordPress.
	 *
	 * @return array
	 */
	private function get_default_query_vars() {
		return array(
			'post_status'    => array( 'draft', 'pending', 'private', 'publish' ),
			'post_type'      => 'wfcm_file_event',

			'posts_per_page' => -1,
			'paginate'       => false,

			'order'          => 'DESC',
			'orderby'        => 'date',

			'return'         => 'objects',

			'event_type'     => '', // Event type: added, modified, or deleted.
			'status'         => '', // Event status.
		);
	}

	/**
	 * Get events from WordPress.
	 *
	 * @return array|object
	 */
	public function get_events() {
		$args   = $this->get_args();
		$events = WFCM_Data_Store::load( 'event' )->query( $args );
		return $events;
	}
}
