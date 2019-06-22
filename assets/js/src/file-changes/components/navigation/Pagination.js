/**
 * Events Pagination Component.
 */
import React, { Component } from 'react';

export default class Pagination extends Component {

	goToPageNumber( e ) {
		const pageNumber = Number( e.target.value );

		if ( 0 < pageNumber && pageNumber <= this.props.maxPages ) {
			this.props.goToPage( pageNumber );
		}
	}

	render() {
		const {totalItems, maxPages, paged} = this.props;
		const pageLinks = [];

		let disableFirst, disableLast, disablePrev, disableNext;
		disableFirst = disableLast = disablePrev = disableNext = false;

		if ( 1 === paged ) {
			disableFirst = true;
			disablePrev = true;
		}
		if ( 2 === paged ) {
			disableFirst = true;
		}
		if ( paged === maxPages ) {
			disableLast = true;
			disableNext = true;
		}
		if ( paged === ( maxPages - 1 ) ) {
			disableLast = true;
		}

		if ( 1 < maxPages && disableFirst ) {
			pageLinks.push( <span className="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span> );
		} else {
			pageLinks.push(
				<button className="first-page button" onClick={this.props.goToPage.bind( this, 1 )}>
					<span className="screen-reader-text">{wfcmFileChanges.pagination.firstPage}</span><span aria-hidden="true">&laquo;</span>
				</button>
			);
		}

		if ( 1 < maxPages && disablePrev ) {
			pageLinks.push( <span className="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span> );
		} else {
			pageLinks.push(
				<button className="prev-page button" onClick={this.props.goToPage.bind( this, Math.max( 1, paged - 1 ) )}>
					<span className="screen-reader-text">{wfcmFileChanges.pagination.previousPage}</span><span aria-hidden="true">&lsaquo;</span>
				</button>
			);
		}

		if ( 1 < maxPages ) {
			pageLinks.push(
				<span className="paging-input">
					<label htmlFor="current-page-selector" className="screen-reader-text">Current page</label>
					<input type="number" className="current-page" id="current-page-selector" value={paged} onChange={this.goToPageNumber.bind( this )} aria-describedby="table-paging" />
					<span className="tablenav-paging-text"> of <span className="total-pages">{maxPages}</span></span>
				</span>
			);
		}

		if ( 1 < maxPages && disableNext ) {
			pageLinks.push( <span className="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span> );
		} else {
			pageLinks.push(
				<button className="next-page button" onClick={this.props.goToPage.bind( this, Math.min( maxPages, paged + 1 ) )}>
					<span className="screen-reader-text">{wfcmFileChanges.pagination.nextPage}</span><span aria-hidden="true">&rsaquo;</span>
				</button>
			);
		}

		if ( 1 < maxPages && disableLast ) {
			pageLinks.push( <span className="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span> );
		} else {
			pageLinks.push(
				<button className="last-page button" onClick={this.props.goToPage.bind( this, maxPages )}>
					<span className="screen-reader-text">{wfcmFileChanges.pagination.lastPage}</span><span aria-hidden="true">&raquo;</span>
				</button>
			);
		}

		if ( 1 < maxPages ) {
			return (
				<div className="tablenav-pages">
					<span className="displaying-num">{totalItems} {wfcmFileChanges.pagination.fileChanges}</span>
					<span className="pagination-links">{pageLinks}</span>
				</div>
			);
		} else {
			return (
				<div className="tablenav-pages"><span className="displaying-num">{totalItems} {wfcmFileChanges.pagination.fileChanges}</span></div>
			);
		}
	}
}
