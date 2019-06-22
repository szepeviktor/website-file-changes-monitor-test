/**
 * Events Table.
 */
import React from 'react';
import { EventsContext } from '../context/EventsContext';
import EventsTable from './EventsTable';

export default () => (
	<EventsContext.Consumer>
		{ ({events, selectAll, getFileEvents, selectAllEvents}) => (
			<EventsTable
				events={events}
				selectAll={selectAll}
				getFileEvents={getFileEvents}
				selectAllEvents={selectAllEvents}
			/>
		) }
	</EventsContext.Consumer>
);
