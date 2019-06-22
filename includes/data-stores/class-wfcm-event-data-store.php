<?php
/**
 * WFCM Event Post Type Data Store.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Event post type data store.
 */
class WFCM_Event_Data_Store {

	/**
	 * Event meta keys.
	 *
	 * @var array
	 */
	private $meta_keys = array(
		'event_type',
		'status',
	);

	/**
	 * Returns WP_Query arguements.
	 *
	 * @param array $query_args - Query arguments.
	 * @return array
	 */
	private function get_wp_query_vars( $query_args ) {
		$wp_query_args = array(
			'meta_query' => array(), // phpcs:ignore
		);

		foreach ( $query_args as $key => $value ) {
			if ( 'meta_query' === $key ) {
				continue;
			}

			if ( in_array( $key, $this->meta_keys, true ) ) {
				if ( ! $value ) {
					continue;
				}

				$wp_query_args['meta_query'][] = array(
					'key'     => $key,
					'value'   => $value,
					'compare' => is_array( $value ) ? 'IN' : '=',
				);
			} else {
				$wp_query_args[ $key ] = $value;
			}
		}

		return $wp_query_args;
	}

	/**
	 * Query events.
	 *
	 * @param array $query_args - Query arguments.
	 * @return array|object
	 */
	public function query( $query_args ) {
		$wp_query_args = $this->get_wp_query_vars( $query_args );

		$query = new WP_Query( $wp_query_args );

		$events = isset( $query->posts ) ? array_map( 'wfcm_get_event', $query->posts ) : array();

		if ( isset( $query_args['paginate'] ) && $query_args['paginate'] ) {
			return (object) array(
				'events'        => $events,
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			);
		}

		return $events;
	}
}
