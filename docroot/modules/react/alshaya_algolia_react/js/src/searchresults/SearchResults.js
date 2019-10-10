import React from 'react';
import {
  Configure,
  connectSearchBox,
  InstantSearch,
  Stats,
} from 'react-instantsearch-dom';

import { searchClient } from '../config/SearchClient';

import NoResults from '../components/NoResults';
import SearchResultInfiniteHits from '../components/algolia/SearchResultInfiniteHits';

import CurrentRefinements from '../filters/selectedfilters/CurrentRefinements';
import Filters from '../filters/Filters';

import AllFilters from '../panels/AllFilters';
import GridAndCount from '../panels/GridAndCount';
import MainContent from '../panels/MainContent';
import SelectedFilters from '../panels/SelectedFilters';
import SideBar from '../panels/SideBar';
import StickyFilter from '../panels/StickyFilter';

import withURLSync from '../URLSync';
import Pagination from '../components/algolia/Pagination';
import HierarchicalMenu from '../filters/widgets/HierarchicalMenu';

// Create a dummy search box to generate result.
const VirtualSearchBox = connectSearchBox(() => (null));

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
      <Configure clickAnalytics />
      <Configure hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} filters={stockFilter} query={query}/>
      <VirtualSearchBox currentRefinement={query}  />
      <SideBar>
        <ul>
          <li>
            <HierarchicalMenu
              attributes={[
                'field_category_name.lvl0',
                'field_category_name.lvl1',
                'field_category_name.lvl2',
              ]}
            />
          </li>
        </ul>
      </SideBar>
      <MainContent>
        <StickyFilter>
          <Filters indexName={indexName} />
        </StickyFilter>
        <AllFilters>
          <Filters indexName={indexName} />
        </AllFilters>
        <GridAndCount>
          <Stats
            translations={{
              stats(nbHits, timeSpentMS) {
                return `${nbHits} items`;
              },
            }}
          />
        </GridAndCount>
        <SelectedFilters>
          <CurrentRefinements />
        </SelectedFilters>
        <div id="hits" className="c-products-list product-small view-search">
          <SearchResultInfiniteHits>
            {(paginationArgs) => (
              <Pagination {...paginationArgs}>Load more products</Pagination>
            )}
          </SearchResultInfiniteHits>
        </div>
        <SelectedFilters />
        <SearchResultHits />
        <NoResults />
      </MainContent>
    </InstantSearch>
  );
}

export default withURLSync(SearchResults);
