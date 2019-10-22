import React from 'react';
import {
  Configure,
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
import { hasCategoryFilter } from '../utils';

/**
 * Render search results elements facets, filters and sorting etc.
 */
const SearchResults = props => {
  const { query } = props;
  // Do not show out of stock products.
  const stockFilter = drupalSettings.algoliaSearch.filterOos === true ? 'stock > 0' : [];
  const indexName = drupalSettings.algoliaSearch.indexName;
  Drupal.algoliaReact.facetEffects();


  return (
    <InstantSearch
      searchClient={searchClient}
      indexName={indexName}
      searchState={props.searchState}
      createURL={props.createURL}
      onSearchStateChange={props.onSearchStateChange}
    >
      <Configure clickAnalytics />
      <Configure hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} filters={stockFilter} query={query}/>
      {hasCategoryFilter() && (
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
      )}
      <MainContent>
        <StickyFilter>
          <Filters indexName={indexName} />
          <div className="show-all-filters-algolia">
            <span className="desktop">{Drupal.t('all filters')}</span>
            <span className="upto-desktop">{Drupal.t('filter & sort')}</span>
          </div>
        </StickyFilter>
        <AllFilters>
          <Filters indexName={indexName} />
        </AllFilters>
        <GridAndCount>
          <Stats
            translations={{
              stats(nbHits, timeSpentMS) {
                return Drupal.t('@total items', {'@total': nbHits});
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
              <Pagination {...paginationArgs}>{Drupal.t('Load more products')}</Pagination>
            )}
          </SearchResultInfiniteHits>
        </div>
        <NoResults />
      </MainContent>
    </InstantSearch>
  );
}

export default withURLSync(SearchResults);
