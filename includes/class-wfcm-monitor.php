<?php
/**
 * File Changes Monitor.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * File Changes Monitor Class.
 *
 * This class is responsible for monitoring
 * the file changes on the server.
 */
class WFCM_Monitor {

	/**
	 * Sensor Instance.
	 *
	 * @var WFCM_Monitor
	 */
	protected static $instance = null;

	/**
	 * WP Root Path.
	 *
	 * @var string
	 */
	private $root_path = '';

	/**
	 * Paths to exclude during scan.
	 *
	 * @var array
	 */
	private $excludes = array();

	/**
	 * View settings.
	 *
	 * @var array
	 */
	public $scan_settings = array();

	/**
	 * Frequency daily hour.
	 *
	 * For testing change hour here [01 to 23]
	 *
	 * @var array
	 */
	private static $daily_hour = array( '04' );

	/**
	 * Frequency weekly date.
	 *
	 * For testing change date here [1 (for Monday) through 7 (for Sunday)]
	 *
	 * @var string
	 */
	private static $weekly_day = '1';

	/**
	 * Frequency montly date.
	 *
	 * For testing change date here [01 to 31]
	 *
	 * @var string
	 */
	private static $monthly_day = '01';

	/**
	 * Schedule hook name.
	 *
	 * @var string
	 */
	public static $schedule_hook = 'wfcm_monitor_file_changes';

	/**
	 * Scan files counter during a scan.
	 *
	 * @var int
	 */
	private $scan_file_count = 0;

	/**
	 * Scan files limit reached.
	 *
	 * @var bool
	 */
	private $scan_limit_file = false;

	/**
	 * Stored files to exclude.
	 *
	 * @var array
	 */
	private $files_to_exclude = array();

	/**
	 * WP uploads directory.
	 *
	 * @var array
	 */
	private $uploads_dir = array();

	/**
	 * Scan changes count.
	 *
	 * @var array
	 */
	private $scan_changes_count = array();

	/**
	 * Class constants.
	 */
	const SCAN_DAILY      = 'daily';
	const SCAN_WEEKLY     = 'weekly';
	const SCAN_MONTHLY    = 'monthly';
	const SCAN_FILE_LIMIT = 1000000;

	/**
	 * Return WFCM_Monitor Instance.
	 *
	 * Ensures only one instance of monitor is loaded or can be loaded.
	 *
	 * @return WFCM_Monitor
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->root_path = trailingslashit( ABSPATH );
		$this->register_hooks();
		$this->load_settings();
		$this->schedule_file_changes_monitor();
	}

	/**
	 * Register Hooks.
	 */
	public function register_hooks() {
		add_filter( 'cron_schedules', array( $this, 'add_recurring_schedules' ) ); // phpcs:ignore
		add_filter( 'wfcm_file_scan_stored_files', array( $this, 'filter_scan_files' ), 10, 2 );
		add_filter( 'wfcm_file_scan_scanned_files', array( $this, 'filter_scan_files' ), 10, 2 );
		add_action( 'wfcm_after_file_scan', array( $this, 'empty_skip_file_alerts' ), 10, 1 );
		add_action( 'wfcm_last_scanned_directory', array( $this, 'reset_core_updates_flag' ), 10, 1 );
	}

	/**
	 * Load File Change Monitor Settings.
	 */
	public function load_settings() {
		$this->scan_settings = wfcm_get_monitor_settings();

		// Set the scan hours.
		if ( ! empty( $this->scan_settings['hour'] ) ) {
			$saved_hour = (int) $this->scan_settings['hour'];
			$next_hour  = $saved_hour + 1;
			$hours      = array( $saved_hour, $next_hour );
			foreach ( $hours as $hour ) {
				$daily_hour[] = str_pad( $hour, 2, '0', STR_PAD_LEFT );
			}
			self::$daily_hour = $daily_hour;
		}

		// Set weekly day.
		if ( ! empty( $this->scan_settings['day'] ) ) {
			self::$weekly_day = $this->scan_settings['day'];
		}

		// Set monthly date.
		if ( ! empty( $this->scan_settings['date'] ) ) {
			self::$monthly_day = $this->scan_settings['date'];
		}
	}

	/**
	 * Schedule file changes monitor cron.
	 */
	public function schedule_file_changes_monitor() {
		// Schedule file changes if the feature is enabled.
		if ( is_multisite() && ! is_main_site() ) {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		} elseif ( 'yes' === $this->scan_settings['enabled'] ) {
			// Hook scheduled method.
			add_action( self::$schedule_hook, array( $this, 'scan_file_changes' ) );

			// Schedule event if there isn't any already.
			if ( ! wp_next_scheduled( self::$schedule_hook ) ) {
				wp_schedule_event(
					time(),              // Timestamp.
					'tenminutes',        // Frequency.
					self::$schedule_hook // Scheduled event.
				);
			}
		} else {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		}
	}

	/**
	 * Add time intervals for scheduling.
	 *
	 * @param  array $schedules - Array of schedules.
	 * @return array
	 */
	public function add_recurring_schedules( $schedules ) {
		$schedules['tenminutes'] = array(
			'interval' => 600,
			'display'  => __( 'Every 10 minutes', 'website-file-changes-monitor' ),
		);
		return $schedules;
	}

	/**
	 * Scan File Changes.
	 *
	 * @param bool $manual       - Set to true for manual scan.
	 * @param int  $last_scanned - Last scanned directory index of server directories.
	 *                             Helpful in performing manual scan.
	 */
	public function scan_file_changes( $manual = false, $last_scanned = null ) {
		// Check scan time frequency & last scanned directory list.
		if ( ! $manual && ! $this->check_start_scan( $this->scan_settings['frequency'] ) ) {
			return;
		}

		// Check if a scan is already in progress.
		if ( wfcm_get_setting( 'scan-in-progress', false ) ) {
			return;
		}

		// Set the scan in progress to true because the scan has started.
		wfcm_save_setting( 'scan-in-progress', true );

		// Check last scanned for manual scan.
		if ( ! $manual && is_null( $last_scanned ) ) {
			// Replace the last scanned value with the setting value
			// if the scan is not manual and last scan value is null.
			$last_scanned = wfcm_get_setting( 'last-scanned', false );
		}

		// Get directories to be scanned.
		$directories = $this->scan_settings['directories'];

		// Set the next directory to scan.
		if ( ! $manual ) {
			if ( false === $last_scanned || $last_scanned > 5 ) {
				$next_to_scan = 0;
			} elseif ( 'root' === $last_scanned ) {
				$next_to_scan = 1;
			} else {
				$next_to_scan = $last_scanned + 1;
			}
		} else {
			$next_to_scan = $last_scanned;
		}

		// Set the options name for file list.
		$file_list = "local-files-$next_to_scan";

		// Server directories.
		$server_dirs = wfcm_get_server_directories();

		// Get directory path to scan.
		$path_to_scan = $server_dirs[ $next_to_scan ];

		if ( ( empty( $path_to_scan ) && in_array( 'root', $directories, true ) ) || ( $path_to_scan && in_array( $path_to_scan, $directories, true ) ) ) {
			// Exclude everything else.
			unset( $server_dirs[ $next_to_scan ] );
			$this->excludes = $server_dirs;

			// Get list of files to scan from DB.
			$stored_files = wfcm_get_setting( $file_list, array() );

			// Set scan changes count.
			$this->scan_changes_count();

			/**
			 * `Filter`: Stored files filter.
			 *
			 * @param array  $stored_files – Files array already saved in DB from last scan.
			 * @param string $path_to_scan – Path currently being scanned.
			 */
			$filtered_stored_files = apply_filters( 'wfcm_file_scan_stored_files', $stored_files, $path_to_scan );

			// Get array of already directories scanned from DB.
			$scanned_dirs = wfcm_get_setting( 'scanned-dirs', array() );

			// If already scanned directories don't exist then it marks the start of a scan.
			if ( ! $manual && empty( $scanned_dirs ) ) {
				wfcm_save_setting( 'last-scan-start', time() );
			}

			/**
			 * Before file scan action hook.
			 *
			 * @param string $path_to_scan - Directory path to scan.
			 */
			do_action( 'wfcm_before_file_scan', $path_to_scan );

			// Reset scan counter.
			$this->reset_scan_counter();

			// Scan the path.
			$scanned_files = $this->scan_path( $path_to_scan );

			/**
			 * `Filter`: Scanned files filter.
			 *
			 * @param array  $scanned_files – Files array already saved in DB from last scan.
			 * @param string $path_to_scan  – Path currently being scanned.
			 */
			$filtered_scanned_files = apply_filters( 'wfcm_file_scan_scanned_files', $scanned_files, $path_to_scan );

			// Add the currently scanned path to scanned directories.
			$scanned_dirs[] = $path_to_scan;

			/**
			 * After file scan action hook.
			 *
			 * @param string $path_to_scan - Directory path to scan.
			 */
			do_action( 'wfcm_after_file_scan', $path_to_scan );

			// Get initial scan setting.
			$initial_scan = wfcm_get_setting( "is-initial-scan-$next_to_scan", 'yes' );

			// If the scan is not initial then.
			if ( 'yes' !== $initial_scan ) {
				// Compare the results to find out about file added and removed.
				$files_added   = array_diff_key( $filtered_scanned_files, $filtered_stored_files );
				$files_removed = array_diff_key( $filtered_stored_files, $filtered_scanned_files );

				/**
				 * File changes.
				 *
				 * To scan the files with changes, we need to
				 *
				 *  1. Remove the newly added files from scanned files – no need to add them to changed files array.
				 *  2. Remove the deleted files from already logged files – no need to compare them since they are removed.
				 *  3. Then start scanning for differences – check the difference in hash.
				 */
				$scanned_files_minus_added  = array_diff_key( $filtered_scanned_files, $files_added );
				$stored_files_minus_deleted = array_diff_key( $filtered_stored_files, $files_removed );

				// Changed files array.
				$files_changed = array();

				// Go through each newly scanned file.
				foreach ( $scanned_files_minus_added as $file => $file_hash ) {
					// Check if it exists in already stored array of files, ignore if the key does not exists.
					if ( array_key_exists( $file, $stored_files_minus_deleted ) ) {
						// If key exists, then check if the file hash is set and compare it to already stored hash.
						if (
							! empty( $file_hash ) && ! empty( $stored_files_minus_deleted[ $file ] )
							&& 0 !== strcmp( $file_hash, $stored_files_minus_deleted[ $file ] )
						) {
							// If the file hashes don't match then store the file in changed files array.
							$files_changed[ $file ] = $file_hash;
						}
					}
				}

				// Files added alert.
				if ( in_array( 'added', $this->scan_settings['type'], true ) && count( $files_added ) > 0 ) {
					// Get excluded site content.
					$site_content = wfcm_get_setting( WFCM_Settings::$site_content );

					// Add the file count.
					$this->scan_changes_count['files_added'] += count( $files_added );

					// Log the alert.
					foreach ( $files_added as $file => $file_hash ) {
						// Get directory name.
						$directory_name = dirname( $file );

						// Check if the directory is in excluded directories list.
						if ( ! empty( $site_content->skip_dirs ) && in_array( $directory_name, $site_content->skip_dirs, true ) ) {
							continue; // If true, then skip the loop.
						}

						// Get filename from file path.
						$filename = basename( $file );

						// Check if the filename is in excluded files list.
						if ( ! empty( $site_content->skip_files ) && in_array( $filename, $site_content->skip_files, true ) ) {
							continue; // If true, then skip the loop.
						}

						// Check for allowed extensions.
						if ( ! empty( $site_content->skip_exts ) && in_array( pathinfo( $filename, PATHINFO_EXTENSION ), $site_content->skip_exts, true ) ) {
							continue; // If true, then skip the loop.
						}

						// Created file event.
						wfcm_create_event( 'added', $file, $file_hash );
					}
				}

				// Files removed alert.
				if ( in_array( 'deleted', $this->scan_settings['type'], true ) && count( $files_removed ) > 0 ) {
					// Add the file count.
					$this->scan_changes_count['files_deleted'] += count( $files_removed );

					// Log the alert.
					foreach ( $files_removed as $file => $file_hash ) {
						// Get directory name.
						$directory_name = dirname( $file );

						// Check if directory is in excluded directories list.
						if ( in_array( $directory_name, $this->scan_settings['exclude-dirs'], true ) ) {
							continue; // If true, then skip the loop.
						}

						// Get filename from file path.
						$filename = basename( $file );

						// Check if the filename is in excluded files list.
						if ( in_array( $filename, $this->scan_settings['exclude-files'], true ) ) {
							continue; // If true, then skip the loop.
						}

						// Check for allowed extensions.
						if ( in_array( pathinfo( $filename, PATHINFO_EXTENSION ), $this->scan_settings['exclude-exts'], true ) ) {
							continue; // If true, then skip the loop.
						}

						// Removed file event.
						wfcm_create_event( 'deleted', $file, $file_hash );
					}
				}

				// Files edited alert.
				if ( in_array( 'modified', $this->scan_settings['type'], true ) && count( $files_changed ) > 0 ) {
					// Add the file count.
					$this->scan_changes_count['files_modified'] += count( $files_changed );

					foreach ( $files_changed as $file => $file_hash ) {
						// Create event for each changed file.
						wfcm_create_event( 'modified', $file, $file_hash );
					}
				}

				// Check for files limit alert.
				if ( $this->scan_limit_file ) {
					$admin_notices = wfcm_get_setting( 'admin-notices', array() );

					if ( ! isset( $admin_notices['files-limit'] ) || ! is_array( $admin_notices['files-limit'] ) ) {
						$admin_notices['files-limit'] = array();
					}

					if ( ! in_array( $path_to_scan, $admin_notices['files-limit'], true ) ) {
						array_push( $admin_notices['files-limit'], $path_to_scan );
					}

					wfcm_save_setting( 'admin-notices', $admin_notices );
				}

				$this->scan_changes_count( 'save' );

				/**
				 * `Action`: Last scanned directory.
				 *
				 * @param int $next_to_scan – Last scanned directory.
				 */
				do_action( 'wfcm_last_scanned_directory', $next_to_scan );
			} else {
				wfcm_save_setting( "is-initial-scan-$next_to_scan", 'no' ); // Initial scan check set to false.
			}

			// Store scanned files list.
			wfcm_save_setting( $file_list, $scanned_files );

			if ( ! $manual ) {
				wfcm_save_setting( 'scanned-dirs', $scanned_dirs );
			}
		}

		/**
		 * Update last scanned directory.
		 *
		 * IMPORTANT: This option is saved outside start scan check
		 * so that if the scan is skipped, then the increment of
		 * next to scan is not disturbed.
		 */
		if ( ! $manual ) {
			if ( 0 === $next_to_scan ) {
				wfcm_save_setting( 'last-scanned', 'root' );

				do_action( 'wfcm_files_monitoring_started' );
			} elseif ( 6 === $next_to_scan ) {
				wfcm_save_setting( 'last-scanned', $next_to_scan );

				do_action( 'wfcm_files_monitoring_ended' );
			} else {
				wfcm_save_setting( 'last-scanned', $next_to_scan );
			}
		}

		// Set the scan in progress to false because scan is complete.
		wfcm_save_setting( 'scan-in-progress', false );
	}

	/**
	 * Check scan frequency.
	 *
	 * Scan start checks:
	 *   1. Check frequency is not empty.
	 *   2. Check if there is any directory left to scan.
	 *     2a. If there is a directory left, then proceed to check frequency.
	 *     2b. Else check if 24 hrs limit is passed or not.
	 *   3. Check frequency of the scan set by user and decide to start the scan or not.
	 *
	 * @param string $frequency - Frequency of the scan.
	 * @return bool True if scan is a go, false if not.
	 */
	public function check_start_scan( $frequency ) {
		// If empty then return false.
		if ( empty( $frequency ) ) {
			return false;
		}

		/**
		 * When there are no directories left to scan then:
		 *
		 * 1. Get the last scan start time.
		 * 2. Check for 24 hrs limit.
		 * 3a. If the limit has passed then remove options related to last scan.
		 * 3b. Else return false.
		 */
		if ( ! $this->dir_left_to_scan( $this->scan_settings['directories'] ) ) {
			// Get last scan time.
			$last_scan_start = wfcm_get_setting( 'last-scan-start', false );

			if ( ! empty( $last_scan_start ) ) {
				// Check for minimum 24 hours.
				$scan_hrs = $this->hours_since_last_scan( $last_scan_start );

				// If scan hours difference has passed 24 hrs limit then remove the options.
				if ( $scan_hrs > 23 ) {
					wfcm_delete_setting( 'scanned-dirs' ); // Delete already scanned directories option.
					wfcm_delete_setting( 'last-scan-start' ); // Delete last scan complete timestamp option.
				} else {
					// Else if they have not passed their limit, then return false.
					return false;
				}
			}
		}

		// Scan check.
		$scan = false;

		// Frequency set by user on the settings page.
		switch ( $frequency ) {
			case self::SCAN_DAILY: // Daily scan.
				if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
					$scan = true;
				}
				break;
			case self::SCAN_WEEKLY: // Weekly scan.
				$weekly_day = $this->calculate_weekly_day();
				$scan       = ( self::$weekly_day === $weekly_day ) ? true : false;
				break;
			case self::SCAN_MONTHLY: // Monthly scan.
				$str_date = $this->calculate_monthly_day();
				if ( ! empty( $str_date ) ) {
					$scan = ( date( 'Y-m-d' ) == $str_date ) ? true : false;
				}
				break;
		}
		return $scan;
	}

	/**
	 * Check to determine if there is any directory left to scan.
	 *
	 * @param array $scan_directories - Array of directories to scan set by user.
	 * @return bool
	 */
	public function dir_left_to_scan( $scan_directories ) {
		// False if $scan_directories is empty.
		if ( empty( $scan_directories ) ) {
			return false;
		}

		// If multisite then remove all the subsites uploads of multisite from scan directories.
		if ( is_multisite() ) {
			$uploads_dir         = wfcm_get_server_directory( $this->get_uploads_dir_path() );
			$mu_uploads_site_dir = $uploads_dir . '/sites'; // Multsite uploads directory.

			foreach ( $scan_directories as $index => $dir ) {
				if ( false !== strpos( $dir, $mu_uploads_site_dir ) ) {
					unset( $scan_directories[ $index ] );
				}
			}
		}

		// Get array of already directories scanned from DB.
		$already_scanned_dirs = wfcm_get_setting( 'scanned-dirs', array() );

		// Check if already scanned directories has `root` directory.
		if ( in_array( '', $already_scanned_dirs, true ) ) {
			// If found then search for `root` in the directories to be scanned.
			$key = array_search( 'root', $scan_directories, true );
			if ( false !== $key ) {
				// If key is found then remove it from directories to be scanned array.
				unset( $scan_directories[ $key ] );
			}
		}

		// Check the difference in directories.
		$diff = array_diff( $scan_directories, $already_scanned_dirs );

		// If the diff array has 1 or more value then scan needs to run.
		if ( is_array( $diff ) && count( $diff ) > 0 ) {
			return true;
		} elseif ( empty( $diff ) ) {
			return false;
		}
		return false;
	}

	/**
	 * Get number of hours since last file changes scan.
	 *
	 * @param float $created_on - Timestamp of last scan.
	 * @return bool|int         - False if $created_on is empty | Number of hours otherwise.
	 */
	public function hours_since_last_scan( $created_on ) {
		// If $created_on is empty, then return.
		if ( ! $created_on ) {
			return false;
		}

		// Last alert date.
		$created_date = new DateTime( date( 'Y-m-d H:i:s', $created_on ) );

		// Current date.
		$current_date = new DateTime( 'NOW' );

		// Calculate time difference.
		$time_diff = $current_date->diff( $created_date );
		$diff_days = $time_diff->d; // Difference in number of days.
		$diff_hrs  = $time_diff->h; // Difference in number of hours.
		$total_hrs = ( $diff_days * 24 ) + $diff_hrs; // Total number of hours.

		// Return difference in hours.
		return $total_hrs;
	}

	/**
	 * Calculate and return hour of the day based on WordPress timezone.
	 *
	 * @return string - Hour of the day.
	 */
	private function calculate_daily_hour() {
		return date( 'H', time() + ( get_option( 'gmt_offset' ) * ( 60 * 60 ) ) );
	}

	/**
	 * Calculate and return day of the week based on WordPress timezone.
	 *
	 * @return string|bool - Day of the week or false.
	 */
	private function calculate_weekly_day() {
		if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
			return date( 'w' );
		}
		return false;
	}

	/**
	 * Calculate and return day of the month based on WordPress timezone.
	 *
	 * @return string|bool - Day of the week or false.
	 */
	private function calculate_monthly_day() {
		if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
			return date( 'Y-m-' ) . self::$monthly_day;
		}
		return false;
	}

	/**
	 * Reset file and directory counter for scan.
	 */
	public function reset_scan_counter() {
		$this->scan_file_count = 0;
		$this->scan_limit_file = false;
	}

	/**
	 * Scan path for files.
	 *
	 * @param string $path - Directory path to scan.
	 * @return array       - Array of files present in $path.
	 */
	private function scan_path( $path = '' ) {
		// Check excluded paths.
		if ( in_array( $path, $this->excludes ) ) {
			return array();
		}

		// Set the directory path.
		$dir_path = $this->root_path . $path;
		$files    = array(); // Array of files to return.

		// Open directory.
		$dir_handle = @opendir( $dir_path );

		if ( false === $dir_handle ) {
			return $files; // Return if directory fails to open.
		}

		$is_multisite     = is_multisite();                               // Multsite checks.
		$directories      = $this->scan_settings['directories'];          // Get directories to be scanned.
		$file_size_limit  = $this->scan_settings['file-size'];            // Get file size limit.
		$file_size_limit  = $file_size_limit * 1048576;                   // Calculate file size limit in bytes; 1MB = 1024 KB = 1024 * 1024 bytes = 1048576 bytes.
		$files_over_limit = array();                                      // Array of files which are over their file size limit.
		$admin_notices    = wfcm_get_setting( 'admin-notices', array() ); // Get admin notices.

		$uploads_dir         = wfcm_get_server_directory( $this->get_uploads_dir_path() );
		$mu_uploads_site_dir = $uploads_dir . '/sites'; // Multsite uploads directory.

		// Scan the directory for files.
		while ( false !== ( $item = @readdir( $dir_handle ) ) ) {
			// Ignore `.` and `..` from directory.
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			// Filter valid filename.
			if ( preg_match( '/[^A-Za-z0-9 _ .-]/', $item ) > 0 ) {
				continue;
			}

			// Ignore .git, .svn, & node_modules from scan.
			if ( false !== strpos( $item, '.git' ) || false !== strpos( $item, '.svn' ) || false !== strpos( $item, 'node_modules' ) ) {
				continue;
			}

			// Set item paths.
			if ( ! empty( $path ) ) {
				$relative_name = $path . '/' . $item;     // Relative file path w.r.t. the location in 7 major folders.
				$absolute_name = $dir_path . '/' . $item; // Complete file path w.r.t. ABSPATH.
			} else {
				// If path is empty then it is root.
				$relative_name = $path . $item;     // Relative file path w.r.t. the location in 7 major folders.
				$absolute_name = $dir_path . $item; // Complete file path w.r.t. ABSPATH.
			}

			// If we're on root then ignore `wp-admin`, `wp-content` & `wp-includes`.
			if ( empty( $path ) && ( false !== strpos( $absolute_name, 'wp-admin' ) || false !== strpos( $absolute_name, WP_CONTENT_DIR ) || false !== strpos( $absolute_name, WPINC ) ) ) {
				continue;
			}

			// Check for directory.
			if ( is_dir( $absolute_name ) ) {
				/**
				 * `Filter`: Directory name filter before opening it for scan.
				 *
				 * @param string $item - Directory name.
				 */
				$item = apply_filters( 'wcfm_directory_before_file_scan', $item );
				if ( ! $item ) {
					continue;
				}

				// Check if the directory is in excluded directories list.
				if ( in_array( $absolute_name, $this->scan_settings['exclude-dirs'], true ) ) {
					continue; // Skip the directory.
				}

				// If not multisite then simply scan.
				if ( ! $is_multisite ) {
					$files = array_merge( $files, $this->scan_path( $relative_name ) );
				} else {
					/**
					 * Check if `wp-content/uploads/sites` is present in the
					 * relative name of the directory & it is allowed to scan.
					 */
					if ( false !== strpos( $relative_name, $mu_uploads_site_dir ) && in_array( $mu_uploads_site_dir, $directories, true ) ) {
						$files = array_merge( $files, $this->scan_path( $relative_name ) );
					} elseif ( false !== strpos( $relative_name, $mu_uploads_site_dir ) && ! in_array( $mu_uploads_site_dir, $directories, true ) ) {
						// If `wp-content/uploads/sites` is not allowed to scan then skip the loop.
						continue;
					} else {
						$files = array_merge( $files, $this->scan_path( $relative_name ) );
					}
				}
			} else {
				/**
				 * `Filter`: File name filter before scan.
				 *
				 * @param string $item – File name.
				 */
				$item = apply_filters( 'wfcm_filename_before_file_scan', $item );
				if ( ! $item ) {
					continue;
				}

				// Check if the item is in excluded files list.
				if ( in_array( $item, $this->scan_settings['exclude-files'], true ) ) {
					continue; // If true, then skip the loop.
				}

				// Check for allowed extensions.
				if ( in_array( pathinfo( $item, PATHINFO_EXTENSION ), $this->scan_settings['exclude-exts'], true ) ) {
					continue; // If true, then skip the loop.
				}

				// Check files count.
				if ( $this->scan_file_count > self::SCAN_FILE_LIMIT ) { // If file limit is reached.
					$this->scan_limit_file = true; // Then set the limit flag.
					break; // And break the loop.
				}

				// Check file size limit.
				if ( ! is_link( $absolute_name ) && filesize( $absolute_name ) < $file_size_limit ) {
					$this->scan_file_count++;

					// File data.
					$files[ $absolute_name ] = @md5_file( $absolute_name ); // File hash.
				} elseif ( is_link( $absolute_name ) ) {
					$files[ $absolute_name ] = '';
				} else {
					if ( ! isset( $admin_notices['filesize-limit'] ) || ! in_array( $absolute_name, $admin_notices['filesize-limit'], true ) ) {
						// File size is more than the limit.
						array_push( $files_over_limit, $absolute_name );
					}

					// File data.
					$files[ $absolute_name ] = '';
				}
			}
		}

		// Close the directory.
		@closedir( $dir_handle );

		if ( ! empty( $files_over_limit ) ) {
			if ( ! isset( $admin_notices['filesize-limit'] ) || ! is_array( $admin_notices['filesize-limit'] ) ) {
				$admin_notices['filesize-limit'] = array();
			}

			$admin_notices['filesize-limit'] = array_merge( $admin_notices['filesize-limit'], $files_over_limit );

			wfcm_save_setting( 'admin-notices', $admin_notices );
		}

		// Return files data.
		return $files;
	}

	/**
	 * Filter scan files before file changes comparison. This
	 * function filters both stored & scanned files.
	 *
	 * Filters:
	 *     1. wp-content/plugins (Plugins).
	 *     2. wp-content/themes (Themes).
	 *     3. wp-admin (WP Core).
	 *     4. wp-includes (WP Core).
	 *
	 * Hooks using this function:
	 *     1. wfcm_file_scan_stored_files.
	 *     2. wfcm_file_scan_scanned_files.
	 *
	 * @param array  $scan_files   - Scan files array.
	 * @param string $path_to_scan - Path currently being scanned.
	 * @return array
	 */
	public function filter_scan_files( $scan_files, $path_to_scan ) {
		// If the path to scan is of plugins.
		if ( false !== strpos( $path_to_scan, wfcm_get_server_directory( WP_PLUGIN_DIR ) ) ) {
			// Filter plugin files.
			$scan_files = $this->filter_excluded_scan_files( $scan_files, 'plugins' );
		} elseif ( false !== strpos( $path_to_scan, wfcm_get_server_directory( get_theme_root() ) ) ) { // And if the path to scan is of themes then.
			// Filter theme files.
			$scan_files = $this->filter_excluded_scan_files( $scan_files, 'themes' );
		} elseif (
			empty( $path_to_scan )                           // Root path.
			|| false !== strpos( $path_to_scan, 'wp-admin' ) // WP Admin.
			|| false !== strpos( $path_to_scan, WPINC )      // WP Includes.
		) {
			// Get `site_content` option.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content );

			// If the `skip_core` is set and its value is equal to true then.
			if ( isset( $site_content->skip_core ) && true === $site_content->skip_core ) {
				// Check the create events for wp-core file updates.
				$this->filter_excluded_scan_files( $scan_files, $path_to_scan );

				// Empty the scan files.
				$scan_files = array();
			}
		}

		// Return the filtered scan files.
		return $scan_files;
	}

	/**
	 * Filter different types of content from scan files.
	 *
	 * Excluded types:
	 *  1. Plugins.
	 *  2. Themes.
	 *
	 * @param array  $scan_files    - Array of scan files.
	 * @param string $excluded_type - Type to be excluded.
	 * @return array
	 */
	private function filter_excluded_scan_files( $scan_files, $excluded_type ) {
		if ( empty( $scan_files ) ) {
			return $scan_files;
		}

		// Get list of excluded plugins/themes.
		$excluded_contents = wfcm_get_setting( WFCM_Settings::$site_content );

		// If excluded files exists then.
		if ( ! empty( $excluded_contents ) ) {
			// Get the array of scan files.
			$files = array_keys( $scan_files );

			// An array of files to exclude from scan files array.
			$files_to_exclude = array();

			// Type of content to skip.
			$skip_type = 'skip_' . $excluded_type; // Possitble values: `plugins` or `themes`.

			// Get current filter.
			$current_filter = current_filter();

			if (
				in_array( $excluded_type, array( 'plugins', 'themes' ), true ) // Only two skip types are allowed.
				&& isset( $excluded_contents->$skip_type )                     // Skip type array exists.
				&& is_array( $excluded_contents->$skip_type )                  // Skip type is array.
				&& ! empty( $excluded_contents->$skip_type )                   // And is not empty.
			) {
				// Go through each plugin to be skipped.
				foreach ( $excluded_contents->$skip_type as $content => $context ) {
					// Path of plugin to search in stored files.
					$search_path = '/' . $excluded_type . '/' . $content;

					// An array of content to be stored as meta for event.
					$event_content = array();

					// Get array of files to exclude of plugins from scan files array.
					foreach ( $files as $file ) {
						if ( false !== strpos( $file, $search_path ) ) {
							$files_to_exclude[] = $file;

							$event_content[ $file ] = (object) array(
								'file' => $file,
								'hash' => isset( $scan_files[ $file ] ) ? $scan_files[ $file ] : false,
							);
						}
					}

					if ( 'update' === $context ) {
						if ( 'wfcm_file_scan_stored_files' === $current_filter ) {
							$this->files_to_exclude[ $search_path ] = $event_content;
						} elseif ( 'wfcm_file_scan_scanned_files' === $current_filter ) {
							$this->check_directory_for_updates( $event_content, $search_path );
						}
					}

					if ( ! empty( $event_content ) ) {
						$dir_path = untrailingslashit( WP_CONTENT_DIR ) . $search_path;

						if ( in_array( 'added', $this->scan_settings['type'], true ) && 'wfcm_file_scan_scanned_files' === $current_filter && 'install' === $context ) {
							$event_context = '';
							if ( 'plugins' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Plugin Install', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['plugin_installs'] += 1;
							} elseif ( 'themes' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Theme Install', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['theme_installs'] += 1;
							}

							wfcm_create_directory_event( 'added', $dir_path, array_values( $event_content ), $event_context );
						} elseif ( in_array( 'deleted', $this->scan_settings['type'], true ) && 'wfcm_file_scan_stored_files' === $current_filter && 'uninstall' === $context ) {
							$event_context = '';
							if ( 'plugins' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Plugin Uninstall', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['plugin_uninstalls'] += 1;
							} elseif ( 'themes' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Theme Uninstall', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['theme_uninstalls'] += 1;
							}

							wfcm_create_directory_event( 'deleted', $dir_path, array_values( $event_content ), $event_context );
						}
					}
				}
			} elseif ( ! $excluded_type || in_array( $excluded_type, array( 'wp-admin', WPINC ), true ) ) {
				// An array of content to be stored as meta for event.
				$event_content = array();

				$directory = trailingslashit( ABSPATH ) . $excluded_type;

				foreach ( $scan_files as $file => $file_hash ) {
					$event_content[ $file ] = (object) array(
						'file' => $file,
						'hash' => $file_hash,
					);
				}

				if ( ! empty( $event_content ) ) {
					if ( 'wfcm_file_scan_stored_files' === $current_filter ) {
						$this->files_to_exclude[ $directory ] = $event_content;
					} elseif ( 'wfcm_file_scan_scanned_files' === $current_filter ) {
						$this->check_directory_for_updates( $event_content, $directory );
					}
				}
			}

			// If there are files to be excluded then.
			if ( ! empty( $files_to_exclude ) ) {
				// Go through each file to be excluded and unset it from scan files array.
				foreach ( $files_to_exclude as $file_to_exclude ) {
					if ( array_key_exists( $file_to_exclude, $scan_files ) ) {
						unset( $scan_files[ $file_to_exclude ] );
					}
				}
			}
		}

		return $scan_files;
	}

	/**
	 * Empty skip file alerts array after scanning the path.
	 *
	 * @param string $path_to_scan - Path currently being scanned.
	 * @return void
	 */
	public function empty_skip_file_alerts( $path_to_scan ) {
		// Check path to scan is not empty.
		if ( empty( $path_to_scan ) ) {
			return;
		}

		// If path to scan is of plugins then empty the skip plugins array.
		if ( false !== strpos( $path_to_scan, wfcm_get_server_directory( WP_PLUGIN_DIR ) ) ) {
			// Get contents list.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// Empty skip plugins array.
			$site_content->skip_plugins = array();

			// Save it.
			wfcm_save_setting( WFCM_Settings::$site_content, $site_content );

			// If path to scan is of themes then empty the skip themes array.
		} elseif ( false !== strpos( $path_to_scan, wfcm_get_server_directory( get_theme_root() ) ) ) {
			// Get contents list.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// Empty skip themes array.
			$site_content->skip_themes = array();

			// Save it.
			wfcm_save_setting( WFCM_Settings::$site_content, $site_content );
		}
	}

	/**
	 * Reset core file changes flag.
	 *
	 * @param int $last_scanned_dir - Last scanned directory.
	 */
	public function reset_core_updates_flag( $last_scanned_dir ) {
		// Check if last scanned directory exists and it is at last directory.
		if ( ! empty( $last_scanned_dir ) && 6 === $last_scanned_dir ) {
			// Get `site_content` option.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// Check WP core update.
			if ( isset( $site_content->skip_core ) && $site_content->skip_core ) {
				$this->scan_changes_count['wp_core_update'] = 1;
			}

			// Send email notification.
			wfcm_send_changes_email( $this->scan_changes_count );

			// Delete changes count for this scan.
			$this->scan_changes_count( 'delete' );

			// Check if the option is instance of stdClass.
			if ( false !== $site_content && $site_content instanceof stdClass ) {
				$site_content->skip_core  = false;   // Reset skip core after the scan is complete.
				$site_content->skip_files = array(); // Empty the skip files at the end of the scan.
				$site_content->skip_exts  = array(); // Empty the skip extensions at the end of the scan.
				$site_content->skip_dirs  = array(); // Empty the skip directories at the end of the scan.
				wfcm_save_setting( WFCM_Settings::$site_content, $site_content ); // Save the option.
			}
		}
	}

	/**
	 * Check directory for file change events after updates.
	 *
	 * @param array  $scanned_files - Array of excluded scanned files.
	 * @param string $directory     - Name of the directory.
	 */
	public function check_directory_for_updates( $scanned_files, $directory ) {
		// Get the files previously stored in the directory.
		$stored_files = $this->files_to_exclude[ $directory ];

		// Compare the results to find out about file added and removed.
		$files_added   = array_diff_key( $scanned_files, $stored_files );
		$files_removed = array_diff_key( $stored_files, $scanned_files );

		/**
		 * File changes.
		 *
		 * To scan the files with changes, we need to
		 *
		 *  1. Remove the newly added files from scanned files – no need to add them to changed files array.
		 *  2. Remove the deleted files from already logged files – no need to compare them since they are removed.
		 *  3. Then start scanning for differences – check the difference in hash.
		 */
		$scanned_files_minus_added  = array_diff_key( $scanned_files, $files_added );
		$stored_files_minus_deleted = array_diff_key( $stored_files, $files_removed );

		// Changed files array.
		$files_changed = array();

		// Go through each newly scanned file.
		foreach ( $scanned_files_minus_added as $file => $file_obj ) {
			// Check if it exists in already stored array of files, ignore if the key does not exists.
			if ( array_key_exists( $file, $stored_files_minus_deleted ) ) {
				// If key exists, then check if the file hash is set and compare it to already stored hash.
				if (
					! empty( $file_obj->hash ) && ! empty( $stored_files_minus_deleted[ $file ] )
					&& 0 !== strcmp( $file_obj->hash, $stored_files_minus_deleted[ $file ]->hash )
				) {
					// If the file hashes don't match then store the file in changed files array.
					$files_changed[ $file ] = $file_obj;
				}
			}
		}

		$dirname       = ABSPATH !== $directory ? dirname( $directory ) : $directory;
		$dir_path      = '';
		$event_context = '';

		if ( '/plugins' === $dirname ) {
			$dir_path      = untrailingslashit( WP_CONTENT_DIR ) . $directory;
			$event_context = __( 'Plugin Update', 'website-file-changes-monitor' );

			// Set the count.
			$this->scan_changes_count['plugin_updates'] += 1;
		} elseif ( '/themes' === $dirname ) {
			$dir_path      = untrailingslashit( WP_CONTENT_DIR ) . $directory;
			$event_context = __( 'Theme Update', 'website-file-changes-monitor' );

			// Set the count.
			$this->scan_changes_count['theme_updates'] += 1;
		} elseif ( ABSPATH === $directory || false !== strpos( $directory, 'wp-admin' ) || false !== strpos( $directory, WPINC ) ) {
			$dir_path      = $directory;
			$event_context = __( 'Core Update', 'website-file-changes-monitor' );
		}

		if ( in_array( 'added', $this->scan_settings['type'], true ) && count( $files_added ) > 0 ) {
			wfcm_create_directory_event( 'added', $dir_path, array_values( $files_added ), $event_context );
		}

		if ( in_array( 'deleted', $this->scan_settings['type'], true ) && count( $files_removed ) > 0 ) {
			wfcm_create_directory_event( 'deleted', $dir_path, array_values( $files_removed ), $event_context );
		}

		if ( in_array( 'modified', $this->scan_settings['type'], true ) && count( $files_changed ) > 0 ) {
			wfcm_create_directory_event( 'modified', $dir_path, array_values( $files_changed ), $event_context );
		}
	}

	/**
	 * Returns the path of WP uploads directory.
	 *
	 * @return string
	 */
	private function get_uploads_dir_path() {
		if ( empty( $this->uploads_dir ) ) {
			$this->uploads_dir = wp_upload_dir(); // Get WP uploads directory.
		}
		return $this->uploads_dir['basedir'];
	}

	/**
	 * Scan changes count; get, save, or delete.
	 *
	 * @param string $action - Count action; get, save, or delete.
	 */
	private function scan_changes_count( $action = 'get' ) {
		if ( 'get' === $action ) {
			$this->scan_changes_count = get_transient( 'wfcm-scan-changes-count' );

			if ( false === $this->scan_changes_count ) {
				$this->scan_changes_count = array(
					'files_added'       => 0,
					'files_deleted'     => 0,
					'files_modified'    => 0,
					'plugin_installs'   => 0,
					'plugin_updates'    => 0,
					'plugin_uninstalls' => 0,
					'theme_installs'    => 0,
					'theme_updates'     => 0,
					'theme_uninstalls'  => 0,
					'wp_core_update'    => 0,
				);
			}
		} elseif ( 'save' === $action ) {
			set_transient( 'wfcm-scan-changes-count', $this->scan_changes_count, DAY_IN_SECONDS );
		} elseif ( 'delete' === $action ) {
			delete_transient( 'wfcm-scan-changes-count' );
		}
	}
}

wfcm_get_monitor();
