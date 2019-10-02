import React, { useRef, useEffect } from 'react';
import ReactDOM from 'react-dom';
import {
  Configure,
  connectHits,
  connectSearchBox
} from 'react-instantsearch-dom';
import InstantSearchComponent from '../components/algolia/InstantSearchComponent';
import Teaser from '../components/teaser/Teaser';
import Filters from './filters/Filters';
import SelectedFilters from './filters/selectedfilters/SelectedFilters';
import { searchResultDiv } from './SearchUtility';

// Create a dummy search box to generate result.
const VirtualSearchBox = connectSearchBox(() => null);

const SearchResultHits = connectHits(({ hits }) => {
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  // Get height of each article and set the max height to all article tags.
  useEffect(
    () => {
      if (typeof teaserRef.current != 'undefined') {
        setTimeout(() => {
          var elements = teaserRef.current.getElementsByTagName('article');
          if (elements.length > 0) {
            Array.prototype.forEach.call(elements, element => {
              element.parentElement.style.height = '';
            });

            var heights = [];
            Array.prototype.forEach.call(elements, element => heights.push(element.parentElement.offsetHeight));
            var maxheight = Math.max(...heights);

            if (maxheight > 0) {
              Array.prototype.forEach.call(elements, element => {
                element.parentElement.style.height = maxheight + 'px'; //= maxheight;
              });
            }
          }
        }, 500);
      }
    }
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
    // Do not show out of stock products.
    const stockFilter = drupalSettings.algoliaSearch.filterOos === true ? 'stock > 0' : [];
    const indexName = drupalSettings.algoliaSearch.indexName;

    return ReactDOM.createPortal(
      <InstantSearchComponent indexName={indexName} createURL={this.props.createURL}>
        <Configure hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} filters={stockFilter}/>
        <VirtualSearchBox defaultRefinement={query} />
        <div className="container-wrapper">
          <section className="sticky-filter-wrapper">
            <div class="site-brand-home">
              <a href="/en/" title="H&amp;M Kuwait" rel="home" class="logo">
                <img src="/themes/custom/transac/alshaya_hnm/site-logo.svg?sjhgdf7v" alt="H&M Kuwait" />
              </a>
            </div>
            <Filters indexName={indexName} />
          </section>
          <div id="block-filterbar" className="block block-facets-summary block-facets-summary-blockfilter-bar">
            <span class="filter-list-label">selected filters</span>
            <SelectedFilters />
          </div>
          <SearchResultHits />
        </div>
      </InstantSearchComponent>,
      this.el
    );
  }
}

export default SearchResults;
