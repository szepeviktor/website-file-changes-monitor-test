/**
 * File Events Helper Functions.
 */

/**
 * Get request object for REST request.
 *
 * @param {string} method REST method: GET, POST, PATCH, DELETE.
 * @param {string} url REST url.
 */
function getRestRequestObject( method, url, body = false ) {

	// Request object params.
	let requestParams = { // eslint-disable-line no-undef
		method: method,
		headers: {
			'X-WP-Nonce': wfcmFileChanges.security // eslint-disable-line no-undef
		}
	};

	// If there is a body then add it to the request object.
	if ( body ) {
		requestParams.body = body;
	}

	// Return the request object.
	return new Request( url, requestParams );
}

/**
 * Get events via REST request.
 *
 * @param {string} eventType Event type: added, modified, deleted.
 * @param {integer} paged Page number.
 * @param {integer} perPage Number of events per page.
 */
async function getEvents( eventType, paged, perPage ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.get}/${eventType}?paged=${paged}&per-page=${perPage}`;
	const request = getRestRequestObject( 'GET', requestUrl ); // Get REST request object.

	// Send the request.
	let response = await fetch( request );
	let events = await response.json();
	return events;
}

/**
 * Mark event as read.
 *
 * @param {integer} id Event id.
 */
async function markEventAsRead( id ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.delete}/${id}`;
	const request = getRestRequestObject( 'DELETE', requestUrl ); // Get REST request object.

	// Send the request.
	let response = await fetch( request );
	response = await response.json();
	return response;
}

/**
 * Exclude event from scanning.
 *
 * @param {integer} id Event id.
 */
async function excludeEvent( id ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.delete}/${id}`;
	const requestBody = JSON.stringify({
		exclude: true
	});
	const request = getRestRequestObject( 'DELETE', requestUrl, requestBody );

	// Send the request.
	let response = await fetch( request );
	response = await response.json();
	return response;
}

export default {
	getRestRequestObject,
	getEvents,
	markEventAsRead,
	excludeEvent
};
