import React, { useRef, useEffect } from 'react';
import ReactDOM from 'react-dom';
import {
  Configure,
  connectHits,
  connectSearchBox,
  InstantSearch
} from 'react-instantsearch-dom';
import { searchClient } from '../config/SearchClient';
import Teaser from '../components/teaser/Teaser';
import Filters from './filters/Filters';
import SelectedFilters from './filters/selectedfilters/SelectedFilters';
import withURLSync from '../URLSync';

// Create a dummy search box to generate result.
const VirtualSearchBox = connectSearchBox(props => {
  props.refine(props.currentRefinement);
  return (null);
});

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
const SearchResults = props => {
  const { query } = props;
  // Do not show out of stock products.
  const stockFilter = drupalSettings.algoliaSearch.filterOos === true ? 'stock > 0' : [];
  const indexName = drupalSettings.algoliaSearch.indexName;

  const onSearchStateChange = searchState => {
    searchState.query = query;
    props.onSearchStateChange(searchState);
  };

  return (
    <InstantSearch
      indexName={ `${drupalSettings.algoliaSearch.indexName}_query` }
      searchClient={searchClient}
      indexName={indexName}
      searchState={props.searchState}
      createURL={props.createURL}
      onSearchStateChange={onSearchStateChange}
    >
      <Configure hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} filters={stockFilter}/>
      <VirtualSearchBox defaultRefinement={query} currentRefinement={query} />
      <div className="container-wrapper">
        <section className="sticky-filter-wrapper">
          <div class="site-brand-home">
            <a href="/en/" title="H&M Kuwait" rel="home" class="logo">
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
    </InstantSearch>
  );
}

export default withURLSync(SearchResults);
