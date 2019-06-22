/**
 * Events Table Bulk Actions.
 */
import React from 'react';
import { EventsContext } from '../context/EventsContext';
import BulkActions from './BulkActions';
import Pagination from './Pagination';
import ShowItems from './ShowItems';

const Navigation = ( props ) => {
	const position = props.position;

	return (
		<React.Fragment>
		{
			'top' === position ?
			<EventsContext.Consumer>
				{ ({totalItems, maxPages, paged, goToPage, handleBulkAction}) => (
					<div className="tablenav top">
						<BulkActions handleBulkAction={handleBulkAction} />
						<Pagination totalItems={totalItems} maxPages={maxPages} paged={paged} goToPage={goToPage} />
					</div>
				) }
			</EventsContext.Consumer> :
			<EventsContext.Consumer>
				{ ({handleShowItems}) => (
					<div className="tablenav botton">
						<ShowItems handleShowItems={handleShowItems} eventsType={props.eventsType} />
					</div>
				) }
			</EventsContext.Consumer>
		}
		</React.Fragment>
	);
};

export default Navigation;
