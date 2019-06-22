/**
 * Scan Modal.
 */
import React, { Component } from 'react';
import Modal from 'react-modal';
import fileEvents from '../helper/FileEvents';

export default class ScanModal extends Component {

	/**
	 * Constructor.
	 */
	constructor() {
		super();

		this.state = {
			modalIsOpen: true,
			scanning: false,
			scanComplete: false
		};

		this.openModal = this.openModal.bind( this );
		this.closeModal = this.closeModal.bind( this );
		this.startScan = this.startScan.bind( this );
	}

	/**
	 * Open modal.
	 */
	openModal() {
		this.setState({modalIsOpen: true});
	}

	/**
	 * Close modal.
	 */
	closeModal() {
		this.setState({modalIsOpen: false});

		const requestUrl = `${wfcmFileChanges.scanModal.adminAjax}?action=wfcm_dismiss_instant_scan_modal&security=${wfcmFileChanges.security}`;
		let requestParams = { method: 'GET' };
		fetch( requestUrl, requestParams );
	}

	/**
	 * Start the scan.
	 */
	async startScan( element ) {
		this.setState({scanning: true});
		const targetElement = element.target;

		const scanRequest = fileEvents.getRestRequestObject( 'GET', wfcmFileChanges.monitor.start );
		let response = await fetch( scanRequest );
		response = await response.json();

		if ( response ) {
			this.setState( () => ({
				scanning: false,
				scanComplete: true
			}) );
		} else {
			targetElement.value = wfcmFileChanges.scanModal.scanFailed;
		}
	}

	/**
	 * Render the modal.
	 */
	render() {
		return (
			<React.Fragment>
				<Modal isOpen={this.state.modalIsOpen} onRequestClose={this.closeModal} style={modalStyles} contentLabel={wfcmFileChanges.scanModal.scanNow}>
					<div className="wfcm-modal-header">
						<span>
							<img src={wfcmFileChanges.scanModal.logoSrc} alt="WFCM" className="logo" />
							<h2>
								{
									! this.state.scanComplete ?
									wfcmFileChanges.scanModal.scanNow :
									wfcmFileChanges.scanModal.headingComplete
								}
							</h2>
						</span>
					</div>
					<div className="wfcm-modal-body">
						<p>
							{
								! this.state.scanComplete ?
								wfcmFileChanges.scanModal.initialMsg :
								wfcmFileChanges.scanModal.afterScanMsg
							}
						</p>
						<p>
							{
								! this.state.scanComplete ?
								<input type="button" className="button-primary" value={! this.state.scanning ? wfcmFileChanges.scanModal.scanNow : wfcmFileChanges.scanModal.scanning} onClick={this.startScan} disabled={this.state.scanning} /> :
								<input type="button" className="button-primary" value={wfcmFileChanges.scanModal.ok} onClick={this.closeModal} />
							}
							&nbsp;
							{
								! this.state.scanComplete ?
								<input type="button" className="button" value={wfcmFileChanges.scanModal.scanDismiss} onClick={this.closeModal} disabled={this.state.scanning} /> :
								null
							}
						</p>
					</div>
				</Modal>
			</React.Fragment>
		);
	}
}

const modalStyles = {
	content: {
		top: '35%',
		left: '50%',
		right: 'auto',
		bottom: 'auto',
		marginRight: '-50%',
		transform: 'translate(-40%, -30%)',
		border: 'none',
		borderRadius: '0',
		padding: '0 16px 16px',
		width: '500px'
	}
};

Modal.defaultStyles.overlay.backgroundColor = 'rgba(0,0,0,0.5)';
Modal.setAppElement( '#wfcm-file-changes-view' );
