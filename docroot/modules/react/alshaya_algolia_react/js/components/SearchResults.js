import React, { useRef, useEffect } from 'react';
import ReactDOM from 'react-dom';
import {
  Configure,
  connectHits,
  connectSearchBox
} from 'react-instantsearch-dom';
import InstantSearchComponent from './InstantSearchComponent';
import Teaser from './teaser/Teaser';

// Search result div wrapper to render results.
const searchResultDiv = document.getElementById('alshaya-algolia-search');

// Create a dummy search box to generate result.
const VirtualSearchBox = connectSearchBox(() => null);

const SearchResultHits = connectHits(({ hits }) => {
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  // Get height of each article and set the max height to all article tags.
  useEffect(
    () => {
      var elements = teaserRef.current.getElementsByTagName('article');
      if (elements.length > 0) {
        var heights = [];
        Array.prototype.forEach.call(elements, element => heights.push(element.parentElement.offsetHeight));
        var maxheight = Math.max(...heights);

        if (maxheight > 0) {
          Array.prototype.forEach.call(elements, element => {
            element.parentElement.style.height = maxheight + 'px'; //= maxheight;
          });
        }
      }
    },
    [hits]
  );

  const hs = hits.map(hit => <Teaser key={hit.objectID} hit={hit} />);
  return <div id="hits" className="c-products-list product-small view-search" ref={teaserRef}>{hs}</div>;
});

/**
 * Render search results elements facets, filters and sorting etc.
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
    return (<Teaser hit={props.hit} />);
  }

  render() {
    const { query } = this.props;
    // Filter out of stock products.
    const stockFilter = drupalSettings.algoliaSearch.filterOos === true ? ['stock > 0'] : [];

    return  ReactDOM.createPortal(
      <InstantSearchComponent indexName={drupalSettings.algoliaSearch.indexName}>
        <Configure hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} numericFilters={stockFilter}/>
        <VirtualSearchBox defaultRefinement={query} />
        <SearchResultHits />
      </InstantSearchComponent>,
      this.el
    );
  }
}

export default SearchResults;
