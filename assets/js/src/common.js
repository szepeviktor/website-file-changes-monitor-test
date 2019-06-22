/**
 * Common JS
 */
window.addEventListener( 'load', function() {

	// Dismiss buttons.
	const dismissBtns = document.querySelectorAll( '.wfcm-admin-notice .notice-dismiss' );

	// Add Exclude Item.
	[ ...dismissBtns ].forEach( dismissBtn => {
		dismissBtn.addEventListener( 'click', wfcmDismissAdminNotice );
	});
});

/**
 * Send dismiss request to remove admin notice.
 *
 * @param {Event} e Event object.
 */
function wfcmDismissAdminNotice( e ) {
	const noticeKey = e.target.parentNode.id.substring( 18 ); // Get notice key from id of the notice.

	// Rest request object.
	const request = new Request( `${wfcmData.restAdminEndpoint}/${noticeKey}`, {
		method: 'GET',
		headers: {
			'X-WP-Nonce': wfcmData.restNonce
		}
	});

	// Send the request.
	fetch( request )
		.then( response => response.json() )
		.then( data => {
			if ( data.success ) {
				document.getElementById( `wfcm-admin-notice-${noticeKey}` ).style.display = 'none';
			}
		})
		.catch( error => {
			console.log( error );
		});
}
