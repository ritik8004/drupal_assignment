import React, { useRef, useEffect } from 'react';
import {
  Configure,
  connectInfiniteHits,
  connectSearchBox,
  InstantSearch,
  Stats
} from 'react-instantsearch-dom';
import { searchClient } from '../config/SearchClient';
import Teaser from '../components/teaser/Teaser';
import Filters from './filters/Filters';
import SelectedFilters from './filters/selectedfilters/SelectedFilters';
import withURLSync from '../URLSync';
import GridButtons from './GridButtons';

// Create a dummy search box to generate result.
const VirtualSearchBox = connectSearchBox(() => (null));

const SearchResultHits = connectInfiniteHits(props => {
  const { hits, hasMore, refineNext } = props;
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
    }, [hits]
  );

  const hs = hits.map(hit => <Teaser key={hit.objectID} hit={hit} />);
  return (
    <div id="hits" className="c-products-list product-small view-search" ref={teaserRef}>
      <div className="view-content">{hs}</div>
      <ul class="js-pager__items pager">
        <li class="pager__item">
          <button
            class="button"
            title="Load morer products"
            rel="next"
            onClick={refineNext}
            disabled={!hasMore}
          >
            Load more products
          </button>
        </li>
      </ul>
    </div>
  );
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
      <Configure hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} filters={stockFilter} query={query}/>
      <VirtualSearchBox currentRefinement={query}  />
      <div className="container-wrapper">
        <div class="views-exposed-form bef-exposed-form block block-views block-views-exposed-filter-blocksearch-page" data-bef-auto-submit-full-form="" data-drupal-selector="views-exposed-form-search-page" data-msg-required="Please enter your This field." id="block-exposedformsearchpage-3" data-block-plugin-id="views_exposed_filter_block:search-page">
          <Filters indexName={indexName} />
        </div>
        <div class="block block-alshaya-search-api block-alshaya-grid-count-block">
          <div class="total-result-count">
            <div class="view-header search-count tablet">
              <Stats
                translations={{
                  stats(nbHits, timeSpentMS) {
                    return `${nbHits} items`;
                  },
                }}
              />
            </div>
          </div>
          <GridButtons />
        </div>

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
