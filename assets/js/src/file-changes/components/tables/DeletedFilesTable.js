/**
 * Deleted Files Table
 */
import React, { Component } from 'react';
import EventsTable from '../events-table';
import Navigation from '../navigation';
import { EventsProvider } from '../context/EventsContext';

export default class DeletedFilesTable extends Component {
	render() {
		return (
			<section>
				<EventsProvider eventsType="deleted">
					<h2>{wfcmFileChanges.labels.deletedFiles}</h2>
					<Navigation position="top" />
					<EventsTable />
					<Navigation position="bottom" eventsType="deleted" />
				</EventsProvider>
			</section>
		);
	}
}
