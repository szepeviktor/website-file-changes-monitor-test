<?php
/**
 * WFCM Data Store.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Data Store Class.
 */
class WFCM_Data_Store {

	/**
	 * Data Store Instance.
	 *
	 * @var object
	 */
	private $instance = null;

	/**
	 * Data stores.
	 *
	 * @var array
	 */
	private $stores = array(
		'event' => 'WFCM_Event_Data_Store',
	);

	/**
	 * Query object.
	 *
	 * @var string
	 */
	private $queries_object = null;

	/**
	 * Constructor.
	 *
	 * @param string $queried_object - Queried object.
	 * @throws Exception - When validation fails.
	 */
	public function __construct( $queried_object ) {
		$this->queries_object = $queried_object;

		if ( array_key_exists( $queried_object, $this->stores ) ) {
			$store = $this->stores[ $queried_object ];

			if ( ! class_exists( $store ) ) {
				throw new Exception( __( 'Data store does not exist.', 'website-file-changes-monitor' ) );
			} else {
				$this->instance = new $store();
			}
		} else {
			throw new Exception( __( 'Invalid data store.', 'website-file-changes-monitor' ) );
		}
	}

	/**
	 * Returns the instance of data store of the queried object.
	 *
	 * @param string $queried_object - Queries object.
	 * @throws Exception - When validation fails.
	 * @return WFCM_Data_Store
	 */
	public static function load( $queried_object ) {
		return new WFCM_Data_Store( $queried_object );
	}

	/**
	 * Get event content type.
	 *
	 * @param int $event_id - Event id.
	 * @return string
	 */
	public function get_event_content_type( $event_id ) {
		return get_post_meta( $event_id, 'content_type', true );
	}

	/**
	 * Query data.
	 *
	 * @param array $args - Array of query arguments.
	 * @return array|object
	 */
	public function query( $args ) {
		return $this->instance->query( $args );
	}
}
