/**
 * Bulk Actions.
 */
import React, { Component } from 'react';

export default class BulkActions extends Component {

	constructor( props ) {
		super( props );

		this.state = {
			selectedAction: '-1'
		};
	}

	selectAction( e ) {
		const {value} = e.target;
		this.setState({selectedAction: value});
	}

	render() {
		return (
			<div className="alignleft actions">
				<label htmlFor="bulk-action-selector-top" className="screen-reader-text">{wfcmFileChanges.bulkActions.screenReader}</label>
				<select id="bulk-action-selector-top" name="bulk-action" onChange={this.selectAction.bind( this )}>
					<option value="-1">{wfcmFileChanges.bulkActions.bulkActions}</option>
					<option value="mark-as-read">{wfcmFileChanges.bulkActions.markAsRead}</option>
					<option value="exclude">{wfcmFileChanges.bulkActions.exclude}</option>
				</select>
				<input type="submit" className="button action" value={wfcmFileChanges.bulkActions.apply} onClick={this.props.handleBulkAction.bind( this, this.state.selectedAction )} />
			</div>
		);
	}
}
