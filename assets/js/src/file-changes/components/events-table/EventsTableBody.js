/**
 * Events Table Body
 */
import React, { Component } from 'react';
import EventsTableRow from './EventsTableRow';
import { EventsContext } from '../context/EventsContext';

export default class EventsTableBody extends Component {
	render() {
		if ( 0 < this.props.monitorEvents.length ) {
			return (
				<EventsContext.Consumer>
					{ ({ events, selectEvent, markEventAsRead, excludeEvent }) => (
						<tbody>
							{ events.map( singleEvent => (
								<EventsTableRow event={singleEvent} selectEvent={selectEvent} markEventAsRead={markEventAsRead} excludeEvent={excludeEvent} />
							) ) }
						</tbody>
					) }
				</EventsContext.Consumer>
			);
		} else {
			return (
				<tbody><tr><td colSpan="7">{wfcmFileChanges.table.noEvents}</td></tr></tbody>
			);
		}
	}
};
