/**
 * Events Table Head
 */
import React, { Component } from 'react';

export default class EventsTableHead extends Component {

	render() {
		return (
			<thead>
				<td className="check-column"><input type="checkbox" name="select-all" checked={this.props.selectAll} onChange={this.props.selectAllEvents} /></td>
				<th>{wfcmFileChanges.table.path}</th>
				<th className="column-event-name">{wfcmFileChanges.table.name}</th>
				<th className="column-content-type">{wfcmFileChanges.table.type}</th>
				<th className="column-event-action">{wfcmFileChanges.table.markAsRead}</th>
				<th className="column-event-exclude">{wfcmFileChanges.table.exclude}</th>
				<th className="column-event-content"></th>
			</thead>
		);
	}
}
