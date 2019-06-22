/**
 * Show Items.
 */
import React, { Component } from 'react';

export default class ShowItems extends Component {

	constructor() {
		super();

		this.state = {
			selected: false,
			options: [ 10, 25, 50, 100 ]
		};
	}

	componentDidMount() {
		const showItems = wfcmFileChanges.showItems[this.props.eventsType];
		this.setState({selected: showItems});
	}

	handleShowItems( element ) {
		const showItems = Number( element.target.value );
		this.props.handleShowItems( showItems );
		this.setState({selected: showItems});
	}

	render() {
		const options = this.state.options.reduce( ( html, option ) => {
			return `${html}<option value="${option}"${option === this.state.selected ? ' selected' : ''}>${option}</option>`;
		}, '' );

		return (
			<div className="alignleft actions">
				<label htmlFor="show-items" className="screen-reader-text">Show items</label>
				<select id="show-items" onChange={this.handleShowItems.bind( this )} dangerouslySetInnerHTML={{__html: options}} />
			</div>
		);
	}
}
