<?php
/**
 * Plugin Name: Website File Changes Monitor
 * Plugin URI: https://www.wpwhitesecurity.com/website-file-changes-monitor/
 * Description: A hassle-free way to get alerted of file changes on your WordPress site & boost security.
 * Author: WP White Security
 * Contributors: WP White Security
 * Version: 1.1
 * Text Domain: website-file-changes-monitor
 * Author URI: http://www.wpwhitesecurity.com/
 * License: GPL3
 *
 * @package wfcm
 */

/*
	Website Files Monitor
	Copyright(c) 2019  WP White Security  (email : info@wpwhitesecurity.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 3, as
	published by the Free Software Foundation.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin file.
if ( ! defined( 'WFCM_PLUGIN_FILE' ) ) {
	define( 'WFCM_PLUGIN_FILE', __FILE__ );
}

// Include main plugin class.
if ( ! class_exists( 'Website_File_Changes_Monitor' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-website-file-changes-monitor.php';
}

/**
 * Main instance of Website File Changes Monitor.
 *
 * Returns the main instance of the plugin.
 *
 * @return Website_File_Changes_Monitor
 */
function wfcm_instance() {
	return Website_File_Changes_Monitor::instance();
}
wfcm_instance();
