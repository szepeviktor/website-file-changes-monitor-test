<?php
/**
 * System info tab.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<h3><?php esc_html_e( 'System Information', 'website-file-changes-monitor' ); ?></h3>
<p>
	<textarea id="wfcm-system-info-textarea" readonly="readonly" onclick="this.focus(); this.select()"><?php echo esc_html( self::get_system_info() ); ?></textarea>
	<?php submit_button( __( 'Download System Info File', 'website-file-changes-monitor' ), 'primary', 'wfcm-download-sysinfo' ); ?>
</p>
<script type="text/javascript">
/**
 * Create and download a temporary file.
 *
 * @param {string} filename - File name.
 * @param {string} text - File content.
 */
function download( filename, text ) {
	// Create temporary element.
	var element = document.createElement( 'a' );
	element.setAttribute( 'href', 'data:text/plain;charset=utf-8,' + encodeURIComponent( text ) );
	element.setAttribute( 'download', filename );

	// Set the element to not display.
	element.style.display = 'none';
	document.body.appendChild( element );

	// Simlate click on the element.
	element.click();

	// Remove temporary element.
	document.body.removeChild( element );
}

window.addEventListener( 'load', function() {
	document.getElementById('wfcm-download-sysinfo').addEventListener( 'click', function() {
		download( 'wfcm-system-info.txt', jQuery( '#wfcm-system-info-textarea' ).val() );
	});
});
</script>
