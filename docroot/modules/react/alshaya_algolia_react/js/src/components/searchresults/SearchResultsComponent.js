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
import {
  hasCategoryFilter,
  getAlgoliaStorageValues,
  getSortedItems,
  hasSuperCategoryFilter,
  getSuperCategoryOptionalFilter,
} from '../../utils';
import { isDesktop } from '../../utils/QueryStringUtils';
import { createConfigurableDrawer } from '../../../../../js/utilities/addToBagHelper';
import isHelloMemberEnabled from '../../../../../js/utilities/helloMemberHelper';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import BecomeHelloMember from '../../../../../js/utilities/components/become-hello-member';

/**
 * Render search results elements facets, filters and sorting etc.
 */
const SearchResultsComponent = ({
  query,
  searchState,
  createURL,
  onSearchStateChange,
}) => {
  const parentRef = React.createRef();
  // Do not show out of stock products.
  const stockFilter = drupalSettings.algoliaSearch.filterOos === true ? 'stock > 0' : '';
  const { indexName } = drupalSettings.algoliaSearch.search;

  // Get default page to display for back to search,
  // and delete the stored info from local storage.
  const storedvalues = getAlgoliaStorageValues();
  let defaultpageRender = false;
  if (storedvalues !== null && storedvalues.page !== null) {
    defaultpageRender = storedvalues.page;
  }

  const optionalFilter = getSuperCategoryOptionalFilter();
  const { maximumDepthLhn } = drupalSettings.algoliaSearch;
  const attributes = [];
  for (let i = 0; i <= maximumDepthLhn; i++) {
    attributes.push(`field_category.lvl${i}`);
  }

  const showCategoryFacets = () => {
    parentRef.current.classList.toggle('category-facet-open');
  };

  // Add the drawer markup for add to bag feature.
  createConfigurableDrawer();

  return (
    <InstantSearch
      searchClient={algoliaSearchClient}
      indexName={indexName}
      searchState={searchState}
      createURL={createURL}
      onSearchStateChange={onSearchStateChange}
    >
      <Configure
        clickAnalytics
        hitsPerPage={drupalSettings.algoliaSearch.itemsPerPage}
        filters={stockFilter}
        query={query}
      />
      {optionalFilter ? <Configure optionalFilters={optionalFilter} /> : null}
      <SideBar>
        {hasSuperCategoryFilter() && isDesktop() && (
          <Menu
            transformItems={(items) => getSortedItems(items, 'supercategory')}
            attribute="super_category"
          />
        )}
        {hasCategoryFilter() && isDesktop() && (
          <HierarchicalMenu
            transformItems={(items) => getSortedItems(items, 'category')}
            attributes={attributes}
            facetLevel={1}
            showParentLevel
          />
        )}
      </SideBar>
      <MainContent>
        <StickyFilter>
          {(callback) => (
            <>
              <Filters
                indexName={indexName}
                limit={drupalSettings.algoliaSearch.topFacetsLimit}
                callback={(callerProps) => callback(callerProps)}
                pageType="search"
              />
              {!isDesktop() && (
                <div className="block-facet-blockcategory-facet-search c-facet c-accordion c-collapse-item non-desktop" ref={parentRef}>
                  {(drupalSettings.algoliaSearch.search.filters.super_category !== undefined) && (
                    <div>
                      <h3 className="c-facet__title c-accordion__title c-collapse__title" onClick={showCategoryFacets}>
                        {Drupal.t('Brands/Category')}
                      </h3>
                      <div className="category-facet-wrapper">
                        {hasSuperCategoryFilter() && (
                          <div className="supercategory-facet c-accordion">
                            <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.search.filters.super_category.label}</h3>
                            <Menu
                              transformItems={(items) => getSortedItems(items, 'supercategory')}
                              attribute="super_category"
                            />
                          </div>
                        )}
                        {hasCategoryFilter() && (
                          <div className="c-accordion">
                            <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                            <HierarchicalMenu
                              transformItems={(items) => getSortedItems(items, 'category')}
                              attributes={[
                                'field_category.lvl0',
                                'field_category.lvl1',
                              ]}
                              facetLevel={1}
                            />
                          </div>
                        )}
                      </div>
                    </div>
                  )}
                  {(drupalSettings.algoliaSearch.search.filters.super_category === undefined) && (
                    <>
                      <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                      <HierarchicalMenu
                        transformItems={(items) => getSortedItems(items, 'category')}
                        attributes={attributes}
                        facetLevel={1}
                        showParentLevel
                      />
                    </>
                  )}
                </div>
              )}
              <div className={`show-all-filters-algolia hide-for-desktop ${!hasCategoryFilter() ? 'empty-category' : ''}`}>
                <span className="desktop">{Drupal.t('all filters')}</span>
                <span className="upto-desktop">{Drupal.t('filter & sort')}</span>
              </div>
            </>
          )}
        </StickyFilter>
        <AllFilters wrapperClassName="block-alshaya-search-facets-block-all" AllFilterClass="all-filters-algolia">
          {(callback) => (
            <Filters
              indexName={indexName}
              pageType="search"
              callback={(callerProps) => callback(callerProps)}
            />
          )}
        </AllFilters>
        <GridAndCount>
          <Stats
            translations={{
              stats(nbHits) {
                return Drupal.t('@total items', { '@total': nbHits });
              },
            }}
          />
        </GridAndCount>
        <SelectedFilters>
          {(callback) => (
            <CurrentRefinements callback={(callerProps) => callback(callerProps)} />
          )}
        </SelectedFilters>
        {/* Show Become member content if helloMember is enabled and is guest user. */}
        { isHelloMemberEnabled()
        && !isUserAuthenticated()
        && (
          <BecomeHelloMember />
        )}
        <div id="hits" className="c-products-list product-small view-search">
          <SearchResultInfiniteHits
            defaultpageRender={defaultpageRender}
            pageType="search"
            pageNumber={searchState.page || 1}
          >
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
