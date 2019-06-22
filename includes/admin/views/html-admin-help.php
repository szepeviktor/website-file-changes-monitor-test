<?php
/**
 * Help tab.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<h3><?php esc_html_e( 'Getting Started', 'website-file-changes-monitor' ); ?></h3>
<p><?php esc_html_e( 'Once you install the plugin it will automatically scan for file changes. When the plugin detects a new, modified or deleted file it will notify you with an orange icon as shown below:', 'website-file-changes-monitor' ); ?></p>
<p><img src="<?php echo esc_url( WFCM_BASE_URL . 'assets/img/about/website-file-changes-monitor-view.jpg' ); ?>" alt="<?php esc_html_e( 'Website File Changes Monitor View', 'website-file-changes-monitor' ); ?>"></p>
<p><strong><?php esc_html_e( 'How often does the plugin scan for file changes?', 'website-file-changes-monitor' ); ?></strong></p>
<p><?php esc_html_e( 'By default, the plugin scans the website once a week. You can change the file integrity scan frequency and other properties of the scan from the plugin’s settings.', 'website-file-changes-monitor' ); ?></p>
<h3><?php esc_html_e( 'Plugin Support', 'website-file-changes-monitor' ); ?></h3>
<p><?php esc_html_e( 'Have you encountered or noticed any issues while using WP File Changes Monitor? Or do you want to report something to us? Use any of the options below to get in touch with us.', 'website-file-changes-monitor' ); ?></p>
<p>
	<a href="https://wordpress.org/support/plugin/website-file-changes-monitor/" class="button" target="_blank"><?php esc_html_e( 'POST ON FREE SUPPORT FORUM', 'website-file-changes-monitor' ); ?></a>
	<a href="mailto:support@wpwhitesecurity.com" class="button" target="_blank"><?php esc_html_e( 'FREE EMAIL SUPPORT', 'website-file-changes-monitor' ); ?></a>
</p>
<h3><?php esc_html_e( 'Rate WP File Changes Monitor', 'website-file-changes-monitor' ); ?></h3>
<p><?php esc_html_e( 'We work really hard to develop a good plugin with which you can be alerted of file changes. It involves thousands of man-hours and an endless amount of dedication to research, develop, and maintain this plugin. Therefore if you like what you see, and find WP File Changes Monitor useful we ask you nothing more than to please rate our plugin. We appreciate every star!', 'website-file-changes-monitor' ); ?></p>
<p>
	<a href="https://wordpress.org/plugins/website-file-changes-monitor/#reviews" class="rating-link" target="_blank">
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
	</a>
	<a href="https://wordpress.org/plugins/website-file-changes-monitor/#reviews" class="button" target="_blank"><?php esc_html_e( 'Rate Plugin', 'website-file-changes-monitor' ); ?></a>
</p>
