/**
 * WordPress Modal.
 */
import React, { Component } from 'react';
import Modal from 'react-modal';

export default class ContentModal extends Component {

	/**
	 * Constructor.
	 */
	constructor() {
		super();

		this.state = {
			modalIsOpen: false
		};

		this.openModal = this.openModal.bind( this );
		this.closeModal = this.closeModal.bind( this );
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
	}

	/**
	 * Render component.
	 */
	render() {
		const { eventFiles } = this.props;

		const filesTable = eventFiles.reduce( ( table, singleFile ) => {
			table.push( <tr><td>{singleFile.file}</td></tr> );
			return table;
		}, []);

		return (
			<React.Fragment>
				<button className="btn-event-content" onClick={this.openModal}><span className="dashicons dashicons-info"></span></button>
				<Modal isOpen={this.state.modalIsOpen} onRequestClose={this.closeModal} style={modalStyles} contentLabel="WFCM Event File Changes">
					<div className="wfcm-modal-header">
						<h2>List of Event Files</h2>
						<button className="button" onClick={this.closeModal}><span class="dashicons dashicons-no-alt"></span></button>
					</div>
					<div className="wfcm-modal-body wfcm-modal-body--scrollable">
						<p>Number of files: {eventFiles.length}</p>
						<table className="wp-list-table widefat fixed striped">
							<thead><td>Filename</td></thead>
							<tbody>{filesTable}</tbody>
						</table>
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
		width: '650px',
		width: 'calc(100vw - 30%)'
	}
};

Modal.defaultStyles.overlay.backgroundColor = 'rgba(0,0,0,0.5)';
Modal.setAppElement( '#wfcm-file-changes-view' );
