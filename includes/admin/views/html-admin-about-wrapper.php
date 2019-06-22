<?php
/**
 * Help & About page.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$active_tab = self::get_active_tab();
$page_tabs  = self::$tabs;

?>

<div class="wrap about-wrap full-width-layout">
	<h1><?php /* Translators: Plugin version. */ echo sprintf( esc_html__( 'Website File Changes Monitor %s', 'website-file-changes-monitor' ), esc_html( WFCM_VERSION ) ); ?></h1>
	<p class="about-text"><?php esc_html_e( 'A hassle-free way to get alerted of file changes on your WordPress site & boost security.', 'website-file-changes-monitor' ); ?></p>
	<div class="wp-badge"><?php /* Translators: Plugin version. */ echo sprintf( esc_html__( 'Version %s', 'website-file-changes-monitor' ), esc_html( WFCM_VERSION ) ); ?></div>
	<h2 class="nav-tab-wrapper wp-clearfix">
		<?php foreach ( $page_tabs as $slug => $page_tab ) : ?>
			<a href="<?php echo esc_url( $page_tab['link'] ); ?>" class="nav-tab<?php echo $slug === $active_tab ? ' nav-tab-active' : false; ?>"><?php echo esc_html( $page_tab['title'] ); ?></a>
		<?php endforeach; ?>
	</h2>
	<div class="wfcm-about-body">
		<?php
		if ( isset( $page_tabs[ $active_tab ] ) ) {
			require_once trailingslashit( dirname( __FILE__ ) ) . $page_tabs[ $active_tab ]['view'];
		}
		?>
	</div>
</div>
