import React from 'react';
import ReactDOM from 'react-dom';
import { searchResultDiv } from '../../utils';
import SearchResultsComponent from './SearchResultsComponent';

/**
 * Render search result component with ReactDom.createPortal.
 */
export default class SearchResults extends React.Component {
  constructor(props) {
    super(props);
    // Create a div that we'll render the search results into. Because each
    // Modal component has its own element, we can render multiple
    // modal components into the modal container.
    this.el = document.createElement('div');
  }

  componentDidMount() {
    // Append the element into the DOM on mount. We'll render
    // into the modal container element (see the HTML tab).
    searchResultDiv.appendChild(this.el);
  }

  // Remove the element from the DOM when we unmount.
  componentWillUnmount() {
    searchResultDiv.removeChild(this.el);
  }

  render() {
    return ReactDOM.createPortal(
      <React.Fragment>
        <div className="block block-core block-page-title-block">
          <h1 className="c-page-title">{Drupal.t('Search results')}</h1>
        </div>
        <SearchResultsComponent query={this.props.query}/>
      </React.Fragment>,
      this.el
    );
  }
}
