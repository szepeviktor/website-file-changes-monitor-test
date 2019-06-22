<?php
/**
 * WFCM Post Types.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM Post Type Class.
 *
 * This class handles registeration of post type and taxonomy
 * used by the plugin to store file notifications.
 */
class WFCM_Post_Types {

	/**
	 * Initialize registration.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Register Post Type.
	 */
	public static function register_post_type() {
		// Do action before registering post type.
		do_action( 'wfcm_register_event_post_type' );

		/**
		 * Event Post Type.
		 *
		 * Register post type for file change events.
		 */
		register_post_type(
			'wfcm_file_event',
			apply_filters(
				'wfcm_register_event_post_type_args',
				array(
					'label'        => __( 'File Change Events', 'website-file-changes-monitor' ),
					'public'       => false,
					'hierarchical' => false,
					'supports'     => false,
					'rewrite'      => false,
				)
			)
		);

		// Do action after registering post type.
		do_action( 'wfcm_registered_event_post_type' );
	}
}

// Initialize post types.
WFCM_Post_Types::init();
