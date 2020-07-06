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
import { hasCategoryFilter, getAlgoliaStorageValues, getSuperCategoryOptionalFilter } from '../../utils';
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

  function getSortedItems(items) {
    // Sort facet items in order of the megamenu.
    let sortedItems = [];
    if (items !== null && items.length > 0) {
      let weight = [];
      // Getting the title attribute for L1 items from the menu.
      let l1MenuItems = document.getElementsByClassName('menu--one__link');
      for (let i in l1MenuItems) {
        try {
          if (l1MenuItems[i].getAttribute('title') !== null) {
            // Add 10 to allow adding All at top.
            weight[l1MenuItems[i].getAttribute('title').trim()] = parseInt(i) + 10;
          }
        }
        catch (e) {
        }
      }

      for (let i in items) {
        if (weight[items[i].label.trim()] !== undefined) {
          sortedItems[weight[items[i].label]] = items[i];
        }
        else if (items[i].label === window.Drupal.t('All')) {
          // Use 1 for All to ensure Object.values work properly.
          sortedItems[1] = items[i];
        }
      }

      sortedItems = Object.values(Object.keys(sortedItems).reduce((a, c) => (a[c] = sortedItems[c], a), {}));
    }
    else {
      sortedItems = items;
    }

    return sortedItems;
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
      {hasCategoryFilter() && isDesktop() && (
        <SideBar>
          <HierarchicalMenu
            transformItems={items => getSortedItems(items)}
            attributes={[
              'field_category.lvl0',
              'field_category.lvl1',
            ]}
          />
        </SideBar>
      )}
      <MainContent>
        <StickyFilter>
          {(callback) => (
            <React.Fragment>
              <Filters indexName={indexName} limit={4} callback={(callerProps) => callback(callerProps)}/>
              {hasCategoryFilter() && !isDesktop() && (
                <div className="block-facet-blockcategory-facet-search c-facet c-accordion c-collapse-item non-desktop">
                  <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                  <HierarchicalMenu
                    transformItems={items => getSortedItems(items)}
                    attributes={[
                      'field_category.lvl0',
                      'field_category.lvl1',
                    ]}
                    facetLevel={1}
                  />
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
