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
import {
  isConfigurableFiltersEnabled,
  isUserAuthenticated,
} from '../../../../../js/utilities/helper';
import BecomeHelloMember from '../../../../../js/utilities/components/become-hello-member';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isMobile } from '../../../../../js/utilities/display';
import DynamicWidgets from '../algolia/widgets/DynamicWidgets';

/**
 * Render search results elements facets, filters and sorting etc.
 */
const SearchResultsComponent = ({
  query,
  searchState,
  createURL,
  onSearchStateChange,
  facets,
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

  const {
    defaultColgrid: defaultColGridDesktop,
    defaultColGridMobile,
  } = drupalSettings.algoliaSearch;

  let defaultcolgrid = isMobile() ? defaultColGridMobile : defaultColGridDesktop;
  // Set default value for col grid.
  if (!hasValue(defaultcolgrid)) {
    defaultcolgrid = 'small';
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

  const showBrandFilter = hasValue(drupalSettings.superCategory)
    ? drupalSettings.superCategory.show_brand_filter
    : false;

  const showSidebar = drupalSettings.show_srp_sidebar || false;
  // Add the drawer markup for add to bag feature.
  createConfigurableDrawer();

  // For enabling/disabling hitsPerPage key in algolia calls.
  const enableHitsPerPage = drupalSettings.algoliaSearch.hitsPerPage;

  const isConfigurableFilters = isConfigurableFiltersEnabled() || false;

  const superCategoryComponent = (hasSuperCategoryFilter() || isConfigurableFilters)
    ? (
      <Menu
        transformItems={(items) => getSortedItems(items, 'supercategory')}
        attribute="super_category"
      />
    )
    : null;

  const fieldCategoryComponent = (hasCategoryFilter() || isConfigurableFilters)
    ? (
      <HierarchicalMenu
        transformItems={(items) => getSortedItems(items, 'category')}
        attributes={attributes}
        facetLevel={1}
        showParentLevel
      />
    )
    : null;

  const SideBarWrapper = (showSidebar && !isConfigurableFilters)
    ? SideBar
    : DynamicWidgets;

  return (
    <InstantSearch
      searchClient={algoliaSearchClient}
      indexName={indexName}
      searchState={searchState}
      createURL={createURL}
      onSearchStateChange={onSearchStateChange}
    >
      <Configure
        userToken={Drupal.getAlgoliaUserToken()}
        clickAnalytics
        {...(enableHitsPerPage && { hitsPerPage: drupalSettings.algoliaSearch.itemsPerPage })}
        filters={stockFilter}
        query={query}
        facets={facets}
      />
      {optionalFilter ? (
        <Configure
          optionalFilters={optionalFilter}
          userToken={Drupal.getAlgoliaUserToken()}
        />
      ) : null}
      {isDesktop()
          && (
          <SideBarWrapper lhn>
            {superCategoryComponent}
            {fieldCategoryComponent}
          </SideBarWrapper>
          )}
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
              {!isDesktop() && !isConfigurableFilters && (
                <div className="block-facet-blockcategory-facet-search c-facet c-accordion c-collapse-item non-desktop" ref={parentRef}>
                  {(drupalSettings.algoliaSearch.search.filters.super_category !== undefined
                    && showBrandFilter) && (
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
                  {(drupalSettings.algoliaSearch.search.filters.super_category === undefined
                    || !showBrandFilter) && (
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
              {!isDesktop() && isConfigurableFilters
                  && (
                    <div className="block-facet-blockcategory-facet-search c-facet c-accordion c-collapse-item non-desktop" ref={parentRef}>
                      <div>
                        <h3 className="c-facet__title c-accordion__title c-collapse__title" onClick={showCategoryFacets}>
                          {Drupal.t('Brands/Category')}
                        </h3>
                        <DynamicWidgets lhn>
                          {superCategoryComponent}
                          {fieldCategoryComponent}
                        </DynamicWidgets>
                      </div>
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
        <div id="hits" className={`c-products-list product-${defaultcolgrid} view-search`}>
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
