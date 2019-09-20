import React from 'react';
import ReactDOM from 'react-dom';
import {
  Configure,
  Hits,
  connectSearchBox
} from 'react-instantsearch-dom';
import InstantSearchComponent from './InstantSearchComponent';

const searchResultDiv = document.getElementById('alshaya-algolia-search');

// Create a dummy search box to generate result.
const VirtualSearchBox = connectSearchBox(() => null);

/**
 * Render search results elements facets, filters and sorting etc...
 */
class SearchResults extends React.Component {
  constructor(props) {
    super(props);
    // Create a div that we'll render the modal into. Because each
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

  /**
   * Template of search element.
   *
   * @param {*} props
   *  The properties we get for hitComponent callback; hit object.
   */
  hitDetail(props) {
    return (
      <div>
        <div className="title">{props.hit.title}</div>
      </div>
    );
  }

  render() {
    const { query } = this.props;

    return  ReactDOM.createPortal(
      <InstantSearchComponent indexName={drupalSettings.algoliaSearch.indexName}>
        <Configure hitsPerPage={16} />
        <VirtualSearchBox defaultRefinement={query} />
        <Hits hitComponent={this.hitDetail} />
      </InstantSearchComponent>,
      this.el
    );
  }
}

export default SearchResults;
