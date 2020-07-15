import React from 'react';
import {
  Configure,
  InstantSearch,
  Stats,
} from 'react-instantsearch-dom';

import { algoliaSearchClient } from '../../config/SearchClient';

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
import Menu from '../algolia/widgets/Menu';
import { hasCategoryFilter, getAlgoliaStorageValues, getSortedItems, hasSuperCategoryFilter, getSuperCategoryOptionalFilter } from '../../utils';
import { isDesktop } from '../../utils/QueryStringUtils';

/**
 * Render search results elements facets, filters and sorting etc.
 */
const SearchResultsComponent = props => {
  const { query } = props;
  // Do not show out of stock products.
  const stockFilter = drupalSettings.algoliaSearch.filterOos === true ? 'stock > 0' : '';
  const indexName = drupalSettings.algoliaSearch.indexName;

  // Get default page to display for back to search,
  // and delete the stored info from local storage.
  const storedvalues = getAlgoliaStorageValues();
  var defaultpageRender = false;
  if (storedvalues !== null && typeof storedvalues.page !== null) {
    defaultpageRender = storedvalues.page;
  }

  const optionalFilter = getSuperCategoryOptionalFilter();

  return (
    <InstantSearch
      searchClient={algoliaSearchClient}
      indexName={indexName}
      searchState={props.searchState}
      createURL={props.createURL}
      onSearchStateChange={props.onSearchStateChange}
    >
      <Configure clickAnalytics hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage} filters={stockFilter} query={query}/>
      {optionalFilter ? <Configure optionalFilters={optionalFilter} /> : null}
      <SideBar>
        {hasSuperCategoryFilter() && isDesktop() && (
          <Menu
            transformItems={items => getSortedItems(items, 'supercategory')}
            attribute='super_category'
          />
        )}
        {hasCategoryFilter() && isDesktop() && (
          <HierarchicalMenu
            transformItems={items => getSortedItems(items, 'category')}
            attributes={[
              'field_category.lvl0',
              'field_category.lvl1',
            ]}
          />
        )}
      </SideBar>
      <MainContent>
        <StickyFilter>
          {(callback) => (
            <React.Fragment>
              <Filters indexName={indexName} limit={4} callback={(callerProps) => callback(callerProps)}/>
              {!isDesktop() && (
                <div className="block-facet-blockcategory-facet-search c-facet c-accordion c-collapse-item non-desktop">
                  <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                  {hasSuperCategoryFilter() && (
                    <div className="supercategory-facet c-accordion">
                      <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.filters.super_category.label}</h3>
                      <Menu
                        transformItems={items => getSortedItems(items, 'supercategory')}
                        attribute='super_category'
                      />
                    </div>
                  )}
                  {hasCategoryFilter() && (
                    <div className="c-accordion">
                      <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                      <HierarchicalMenu
                        transformItems={items => getSortedItems(items, 'category')}
                        attributes={[
                          'field_category.lvl0',
                          'field_category.lvl1',
                        ]}
                        facetLevel={1}
                      />
                    </div>
                  )}
                </div>
              )}
              <div className={"show-all-filters-algolia hide-for-desktop " + (!hasCategoryFilter() ? 'empty-category' : '')}>
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
          <SearchResultInfiniteHits defaultpageRender={defaultpageRender}>
            {(paginationArgs) => (
              <Pagination {...paginationArgs}>{Drupal.t('Load more products')}</Pagination>
            )}
          </SearchResultInfiniteHits>
        </div>
        <NoResults />
      </MainContent>
    </InstantSearch>
  );
};

export default withURLSync(SearchResultsComponent);
