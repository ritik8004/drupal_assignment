import React, { useRef, useEffect } from 'react';
import {
  Configure,
  connectInfiniteHits,
  connectSearchBox,
  InstantSearch,
  Stats,
  connectStats
} from 'react-instantsearch-dom';
import { searchClient } from '../config/SearchClient';
import Teaser from '../components/teaser/Teaser';
import Filters from '../filters/Filters';
import SelectedFilters from '../filters/selectedfilters/SelectedFilters';
import AllFilters from '../filters/AllFilters';
import GridButtons from './GridButtons';
import withURLSync from '../URLSync';
import ProgressBar from '../filters/widgets/ProgressBar';

// Create a dummy search box to generate result.
const VirtualSearchBox = connectSearchBox(() => (null));

// Stats with pagination.
const PaginationStats = connectStats(({nbHits, currentResults}) => {
  return (
    <div>
      <span class="ais-Stats-text">{`showing ${currentResults} of ${nbHits} items`}</span>
      <ProgressBar completed={((currentResults * 100)/nbHits)}/>
    </div>
  );
});

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
        <ul className="js-pager__items pager">
          <li className="pager__item">
            <PaginationStats currentResults={hits.length} />
          </li>
          {hasMore ? (
            <li className="pager__item">
              <button
                className="button"
                title="Load morer products"
                rel="next"
                onClick={refineNext}
              >
                Load more products
              </button>
            </li>
          ) : (
            null
          )}
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
        <div className="sticky-filter-wrapper">
          <div className="container-without-product">
            <Filters indexName={indexName} />
          </div>
        </div>
        <AllFilters>
          <Filters indexName={indexName} />
        </AllFilters>
        <div className="block block-alshaya-search-api block-alshaya-grid-count-block">
          <div className="total-result-count">
            <div className="view-header search-count tablet">
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
        <SelectedFilters />
        <SearchResultHits />
      </div>
    </InstantSearch>
  );
}

export default withURLSync(SearchResults);
