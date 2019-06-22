<?php
/**
 * About tab.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<br>
<p class="wfcm-about-logo"><a href="https://www.wpwhitesecurity.com/" target="_blank"><img src="<?php echo esc_url( WFCM_BASE_URL . 'assets/img/wp-white-security-full.svg' ); ?>" alt="<?php esc_attr_e( 'WP White Security', 'website-file-changes-monitor' ); ?>"></a></p>
<p><?php /* Translators: Contact form hyperlink */ echo sprintf( esc_html__( 'The WP File Changes Monitor plugin is developed by WP White Security, the developers of other WordPress admin and security plugins, including the most comprehensive activity log plugin for WordPress. Please use our %s if you’d like to get in touch.', 'website-file-changes-monitor' ), '<a href="https://www.wpwhitesecurity.com/contact-wp-white-security/" target="_blank">' . esc_html__( 'contact form', 'website-file-changes-monitor' ) . '</a>' ); ?></p>
<h3><?php esc_html_e( 'Other Products', 'website-file-changes-monitor' ); ?></h3>
<div class="wfcm-about-products">
	<div class="wfcm-about-products__col-6">
		<a href="https://www.wpsecurityauditlog.com/" target="_blank">
			<img src="<?php echo esc_url( WFCM_BASE_URL . 'assets/img/about/wp-security-audit-log.jpg' ); ?>" alt="<?php esc_attr_e( 'WP Security Audit Log', 'website-file-changes-monitor' ); ?>">
		</a>
	</div>
	<div class="wfcm-about-products__col-6">
		<a href="http://www.wpwhitesecurity.com/plugins-password-policy-manager-wordpress/" target="_blank">
			<img src="<?php echo esc_url( WFCM_BASE_URL . 'assets/img/about/wp-password-policy-manager.jpg' ); ?>" alt="<?php esc_attr_e( 'WP Password Policy Manager', 'website-file-changes-monitor' ); ?>">
		</a>
	</div>
</div>
