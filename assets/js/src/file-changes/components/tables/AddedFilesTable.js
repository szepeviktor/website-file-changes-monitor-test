/**
 * Created Files Table
 */
import React, { Component } from 'react';
import EventsTable from '../events-table';
import Navigation from '../navigation';
import { EventsProvider } from '../context/EventsContext';
import ScanModal from '../modal/ScanModal';

export default class AddedFilesTable extends Component {
	render() {
		return (
			<section>
				<EventsProvider eventsType="added">
					<h2>{wfcmFileChanges.labels.addedFiles}</h2>
					<Navigation position="top" />
					<EventsTable />
					<Navigation position="bottom" eventsType="added" />
				</EventsProvider>
				{
					! wfcmFileChanges.scanModal.dismiss ?
					<ScanModal /> :
					null
				}
			</section>
		);
	}
}
