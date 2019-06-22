/**
 * Events Table Rows.
 */
import React, { Component } from 'react';
import ContentModal from '../modal/ContentModal';

export default class EventsTableRow extends Component {
	render() {
		const event = this.props.event;
		const contentType = event.contentType.toLowerCase();

		return (
			<tr>
				<td><input type="checkbox" value={event.id} checked={event.checked} onChange={this.props.selectEvent.bind( this, event.id )} /></td>
				<td>{event.path}</td>
				<td>{event.filename}</td>
				<td>
					<span className={`content-type ${contentType}`}>
					{
						'directory' === contentType && event.eventContext ?
						event.eventContext :
						event.contentType
					}
					</span>
				</td>
				<td><input className="button-primary" type="button" value="Mark as Read" onClick={this.props.markEventAsRead.bind( this, event.id )} /></td>
				<td><input className="button-secondary" type="button" value="Exclude" onClick={this.props.excludeEvent.bind( this, event.id )} /></td>
				<td>
				{
					'directory' === contentType ?
					<ContentModal eventFiles={event.content} /> :
					null
				}
				</td>
			</tr>
		);
	}
}
