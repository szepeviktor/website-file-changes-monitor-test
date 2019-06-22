/**
 * File Changes Main.
 */
import React, { Component } from 'react';
import AddedFilesTable from './components/tables/AddedFilesTable';
import DeletedFilesTable from './components/tables/DeletedFilesTable';
import ModifiedFilesTable from './components/tables/ModifiedFilesTable';

export default class FileChanges extends Component {
	render() {
		const fileChangesView = this.props.fileChangesView;

		return (
			<React.Fragment>
			{
				'deleted-files' === fileChangesView ?
				<DeletedFilesTable /> :
				'modified-files' === fileChangesView ?
				<ModifiedFilesTable /> :
				<AddedFilesTable />
			}
			</React.Fragment>
		);
	}
}
