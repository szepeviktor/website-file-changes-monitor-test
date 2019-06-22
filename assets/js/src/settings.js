/**
 * Settings JS.
 */
window.addEventListener( 'load', function() {

	const $ = document.querySelector.bind( document );
	const keepLog = document.querySelectorAll( 'input[name="wfcm-settings[keep-log]"]' );
	const frequencySelect = $( 'select[name="wfcm-settings[scan-frequency]"]' );
	const scanDay = $( 'select[name="wfcm-settings[scan-day]"]' ).parentNode;
	const scanDate = $( 'select[name="wfcm-settings[scan-date]"]' ).parentNode;
	const excludeAdd = document.querySelectorAll( '.wfcm-files-container .add' );
	const excludeRemove = document.querySelectorAll( '.wfcm-files-container .remove' );
	const manualScanStart = $( '#wfcm-scan-start' );
	const manualScanStop = $( '#wfcm-scan-stop' );

	// Frequency handler.
	frequencySelect.addEventListener( 'change', function() {
		showScanFields( this.value );
	});

	// Manage appearance on load.
	showScanFields( frequencySelect.value );

	/**
	 * Show Scan Time fields according to selected frequency.
	 *
	 * @param {string} frequency - Scan frequency.
	 */
	function showScanFields( frequency ) {
		scanDay.classList.add( 'hidden' );
		scanDate.classList.add( 'hidden' );

		if ( 'weekly' === frequency ) {
			scanDay.classList.remove( 'hidden' );
		} else if ( 'monthly' === frequency ) {
			scanDate.classList.remove( 'hidden' );
		}
	}

	// Add Exclude Item.
	[ ...excludeAdd ].forEach( excludeAddButton => {
		excludeAddButton.addEventListener( 'click', addToExcludeList );
	});

	// Remove Exclude Item(s).
	[ ...excludeRemove ].forEach( excludeRemoveButton => {
		excludeRemoveButton.addEventListener( 'click', removeFromExcludeList );
	});

	/**
	 * Add item to exclude list.
	 *
	 * @param {Event} e Event object.
	 */
	function addToExcludeList( e ) {
		let pattern = '';
		const excludeType = e.target.dataset.excludeType;

		if ( 'dirs' === excludeType ) {
			pattern = /^\s*[a-z-._\d,\s/]+\s*$/i;
		} else if ( 'files' === excludeType ) {
			pattern = /^\s*[a-z-._\d,\s]+\s*$/i;
		} else if ( 'exts' === excludeType ) {
			pattern = /^\s*[a-z-._\d,\s]+\s*$/i;
		}

		const excludeList = $( `#wfcm-exclude-${excludeType}-list` );
		const excludeNameInput = e.target.parentNode.querySelector( '.name' );
		const excludeName = excludeNameInput.value;

		if ( excludeName.match( pattern ) ) {
			const excludeItem = document.createElement( 'span' );
			const excludeItemInput = document.createElement( 'input' );
			const excludeItemLabel = document.createElement( 'label' );

			excludeItemInput.type = 'checkbox';
			excludeItemInput.checked = true;
			excludeItemInput.name = `wfcm-settings[scan-exclude-${excludeType}][]`;
			excludeItemInput.id = excludeName;
			excludeItemInput.value = excludeName;

			excludeItemLabel.setAttribute( 'for', excludeName );
			excludeItemLabel.innerHTML = excludeName;

			excludeItem.appendChild( excludeItemInput );
			excludeItem.appendChild( excludeItemLabel );
			excludeList.appendChild( excludeItem );
			excludeNameInput.value = '';
		} else {
			if ( 'dirs' === excludeType ) {
				alert( wfcmSettingsData.dirInvalid ); // eslint-disable-line no-undef
			} else if ( 'files' === excludeType ) {
				alert( wfcmSettingsData.fileInvalid ); // eslint-disable-line no-undef
			} else if ( 'exts' === excludeType ) {
				alert( wfcmSettingsData.extensionInvalid ); // eslint-disable-line no-undef
			}
		}
	}

	/**
	 * Remove item from exclude list.
	 *
	 * @param {Event} e Event object.
	 */
	function removeFromExcludeList( e ) {
		const excludeItems = [ ...e.target.parentNode.querySelectorAll( '.exclude-list input[type=checkbox]' ) ];
		let removedValues = [];

		for ( let index = 0; index < excludeItems.length; index++ ) {
			if ( ! excludeItems[ index ].checked ) {
				removedValues.push( excludeItems[ index ].value );
			}
		}

		if ( removedValues.length ) {
			for ( let index = 0; index < removedValues.length; index++ ) {
				let excludeItem = $( 'input[value="' + removedValues[ index ] + '"]' );
				if ( excludeItem ) {
					excludeItem.parentNode.remove();
				}
			}
		}
	}

	// Update settings state when keep log options change.
	[ ...keepLog ].forEach( toggle => {
		toggle.addEventListener( 'change', function() {
			toggleSettings( this.value );
		});
	});

	/**
	 * Toggle Plugin Settings State.
	 *
	 * @param {string} settingValue - Keep log setting value.
	 */
	function toggleSettings( settingValue ) {
		const settingFields = [ ...document.querySelectorAll( '.wfcm-table fieldset' ) ];

		settingFields.forEach( setting => {
			if ( 'no' === settingValue ) {
				setting.disabled = true;
			} else {
				setting.disabled = false;
			}
		});
	}

	/**
	 * Send request to start manual scan.
	 */
	manualScanStart.addEventListener( 'click', function( e ) {
		e.target.value = wfcmSettingsData.scanButtons.scanning; // eslint-disable-line no-undef
		e.target.disabled = true;
		manualScanStop.disabled = false;

		// Rest request object.
		const request = new Request( wfcmSettingsData.monitor.start, { // eslint-disable-line no-undef
			method: 'GET',
			headers: {
				'X-WP-Nonce': wfcmSettingsData.restRequestNonce // eslint-disable-line no-undef
			}
		});

		// Send the request.
		fetch( request )
			.then( response => response.json() )
			.then( data => {
				if ( data ) {
					e.target.value = wfcmSettingsData.scanButtons.scanNow; // eslint-disable-line no-undef
					e.target.disabled = false;
					manualScanStop.disabled = true;
				}
			})
			.catch( error => {
				e.target.value = wfcmSettingsData.scanButtons.scanFailed; // eslint-disable-line no-undef
				e.target.disabled = false;
				manualScanStop.disabled = true;
				console.log( error ); // eslint-disable-line no-console
			});
	});

	/**
	 * Send request to stop manual scan.
	 */
	manualScanStop.addEventListener( 'click', function( e ) {
		e.target.value = wfcmSettingsData.scanButtons.stopping; // eslint-disable-line no-undef
		e.target.disabled = true;

		// Rest request object.
		const request = new Request( wfcmSettingsData.monitor.stop, { // eslint-disable-line no-undef
			method: 'GET',
			headers: {
				'X-WP-Nonce': wfcmSettingsData.restRequestNonce // eslint-disable-line no-undef
			}
		});

		// Send the request.
		fetch( request )
			.then( response => response.json() )
			.then( data => {
				if ( data ) {
					e.target.value = wfcmSettingsData.scanButtons.scanStop; // eslint-disable-line no-undef
					manualScanStart.disabled = false;
				}
			})
			.catch( error => {
				console.log( error ); // eslint-disable-line no-console
			});
	});
});
