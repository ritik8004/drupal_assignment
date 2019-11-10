import React from 'react';
import {
  Configure,
  InstantSearch,
  Stats,
} from 'react-instantsearch-dom';

import { searchClient } from '../../config/SearchClient';

import NoResults from '../algolia/NoResults';
import SearchResultInfiniteHits from '../algolia/SearchResultInfiniteHits';

import CurrentRefinements from '../algolia/current-refinements';
import Filters from '../filters';

import AllFilters from '../panels/AllFilters';
import GridAndCount from '../panels/GridAndCount';
import MainContent from '../panels/MainContent';
import SelectedFilters from '../panels/SelectedFilters';
import SideBar from '../panels/SideBar';
import StickyFilter from '../panels/StickyFilter';

import withURLSync from '../url-sync';
import Pagination from '../algolia/Pagination';
import HierarchicalMenu from '../algolia/widgets/HierarchicalMenu';
import { hasCategoryFilter } from '../../utils';

/**
 * Render search results elements facets, filters and sorting etc.
 */
const SearchResultsComponent = props => {
  const { query } = props;
  // Do not show out of stock products.
  const stockFilter = drupalSettings.algoliaSearch.filterOos === true ? 'stock > 0' : [];
  const indexName = drupalSettings.algoliaSearch.indexName;

  return (
    <InstantSearch
      searchClient={searchClient}
      indexName={indexName}
      searchState={props.searchState}
      createURL={props.createURL}
      onSearchStateChange={props.onSearchStateChange}
    >
      <Configure clickAnalytics hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} filters={stockFilter} query={query}/>
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
          {(callback) => (
            <React.Fragment>
              <Filters indexName={indexName} limit={4} callback={(callerProps) => callback(callerProps)}/>
              <div className="show-all-filters-algolia hide-for-desktop">
                <span className="desktop">{Drupal.t('all filters')}</span>
                <span className="upto-desktop">{Drupal.t('filter & sort')}</span>
              </div>
            </React.Fragment>
          )}
        </StickyFilter>
        <AllFilters>
          {(callback) => (
            <Filters indexName={indexName} callback={(callerProps) => callback(callerProps)}/>
          )}
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
          {(callback) => (
            <CurrentRefinements callback={(callerProps) => callback(callerProps)}/>
          )}
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

export default withURLSync(SearchResultsComponent);
