/**
 * File Changes
 */
import ReactDOM from 'react-dom';
import FileChanges from './file-changes/index';

const fileChanges = document.getElementById( 'wfcm-file-changes-view' );
ReactDOM.render( <FileChanges fileChangesView={fileChanges.dataset.view} />, fileChanges );
