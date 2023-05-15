import React, { useEffect, useRef } from 'react';
import {
  Configure,
  InstantSearch,
  Stats,
} from 'react-instantsearch-dom';

import { algoliaSearchClient } from '../../../../js/utilities/algoliaHelper';

import { productListIndexStatus } from '../utils/indexUtils';
import { getSuperCategoryOptionalFilter } from '../utils';
import Filters from '../components/filters';
import PlpResultInfiniteHits from '../components/plp/PlpResultInfiniteHits';
import PlpPagination from '../components/plp/PlpPagination';
import PlpStickyFilter from '../components/plp/PlpStickFilter';
import GridAndCount from '../components/panels/GridAndCount';
import AllFilters from '../components/panels/AllFilters';
import SelectedFilters from '../components/panels/SelectedFilters';
import CurrentRefinements from '../components/algolia/current-refinements';
import withPlpUrlAliasSync from '../components/url-sync/withPlpUrlAliasSync';
import PLPHierarchicalMenu from '../components/algolia/widgets/PLPHierarchicalMenu';
import PLPNoResults from '../components/algolia/PLPNoResults';
import SubCategoryContent from '../components/subcategory';
import ConditionalView from '../../common/components/conditional-view';
import isHelloMemberEnabled from '../../../../js/utilities/helloMemberHelper';
import {
  isConfigurableFiltersEnabled,
  isUserAuthenticated,
} from '../../../../js/utilities/helper';
import BecomeHelloMember from '../../../../js/utilities/components/become-hello-member';
import { getExpressDeliveryStatus } from '../../../../js/utilities/expressDeliveryHelper';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { isMobile } from '../../../../js/utilities/display';
import DynamicWidgets from '../components/algolia/widgets/DynamicWidgets';
import { getMaxValuesFromFacets } from '../utils/FilterUtils';

if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

const getBackToPlpPage = (pageType) => {
  const plplocalStorage = Drupal.getItemFromLocalStorage(`${pageType}:${window.location.pathname}`);
  if (plplocalStorage && typeof plplocalStorage.page !== 'undefined') {
    return parseInt(plplocalStorage.page, 10);
  }
  return null;
};

/**
 * Render search results elements facets, filters and sorting etc.
 */
const PlpApp = ({
  searchState,
  createURL,
  onSearchStateChange,
  pageType,
  hierarchy: defaultCategoryFilter,
  level: nestedLevel,
  ruleContext,
  categoryField,
  promotionNodeId,
}) => {
  let subCategories = {};
  useEffect(() => {
    getExpressDeliveryStatus().then((status) => {
      window.sddEdStatus = status;
    });
  }, []);

  const plpCategoryRef = useRef();
  const allFiltersRef = useRef();

  const {
    itemsPerPage,
    filterOos,
    pageSubType,
    hierarchy_lhn: lhnCategoryFilter,
    max_category_tree_depth: categoryDepth,
    categoryFacetEnabled,
    defaultColgrid: defaultColGridDesktop,
    defaultColGridMobile,
  } = drupalSettings.algoliaSearch;

  // Split ruleContext into array of strings.
  let context = [];
  if (ruleContext !== undefined && ruleContext.length > 0) {
    context = ruleContext.split(',');
  }

  let defaultcolgrid = isMobile() ? defaultColGridMobile : defaultColGridDesktop;
  // Set default value for col grid.
  if (!hasValue(defaultcolgrid)) {
    defaultcolgrid = 'small';
  }

  const { indexName } = drupalSettings.algoliaSearch.listing;

  const filters = [];
  let finalFilter = '';
  let filterOperator = ' AND ';
  let groupEnabled = false;

  // Do not show out of stock products.
  if (filterOos === true) {
    finalFilter = '(stock > 0) AND ';
  }

  if (pageSubType === 'plp') {
    let { currentLanguage } = drupalSettings.path;
    // Set default EN category filter in product list index for VM.
    if (productListIndexStatus()) {
      currentLanguage = 'en';
    }
    // Get subcategories data.
    subCategories = window.commerceBackend.getSubcategoryData();
    if (hasValue(subCategories)) {
      filterOperator = ' OR ';
      groupEnabled = true;
      // Set all the filters selected in sub category.
      Object.keys(subCategories).forEach((key) => {
        let subCategoryField = subCategories[key].category.category_field;
        const defaultSubCategoryFilter = subCategories[key].category.hierarchy;
        // Add language suffix to the filter attribute eg: field_category_name.en.lvl2.
        if (productListIndexStatus()) {
          if (subCategoryField !== undefined && subCategoryField.indexOf('.') > -1) {
            subCategoryField = subCategoryField.replace('.', `.${currentLanguage}.`);
          } else {
            subCategoryField = `${subCategoryField}.${currentLanguage}`;
          }
        }
        filters.push(`${subCategoryField}: "${defaultSubCategoryFilter}"`);
      });
    } else if (productListIndexStatus()) {
      // Add language suffix to the filter attribute eg: field_category_name_en.lvl2.
      let categoryAttr = '';
      if (categoryField !== undefined && categoryField.indexOf('.') > -1) {
        categoryAttr = categoryField.replace('.', `.${currentLanguage}.`);
      } else {
        categoryAttr = `${categoryField}.${currentLanguage}`;
      }
      filters.push(`${categoryAttr}: "${defaultCategoryFilter}"`);
    } else {
      filters.push(`${categoryField}: "${defaultCategoryFilter}"`);
    }
  } else if (pageSubType === 'product_option_list') {
    // Filter for product option list page.
    const {
      option_page: {
        option_key: optionKey,
        option_val: optionVal,
      },
    } = drupalSettings.algoliaSearch;
    if (optionKey) filters.push(`${optionKey}: "${optionVal}"`);
  } else if (pageSubType === 'promotion') {
    // Filter for promotion page.
    filters.push(`promotion_nid: ${promotionNodeId}`);
  }

  const optionalFilter = getSuperCategoryOptionalFilter();

  const categoryFieldAttributes = [];
  if ((isMobile()
    && pageSubType === 'plp'
    && categoryFacetEnabled
    && nestedLevel < parseInt(categoryDepth, 10) + 1)) {
    const { currentLanguage } = drupalSettings.path;
    for (let i = 0; i <= nestedLevel; i++) {
      if (productListIndexStatus()) {
        // Set default EN category filter in product list index for VM.
        categoryFieldAttributes.push(`lhn_category.${currentLanguage}.lvl${i}`);
      } else {
        categoryFieldAttributes.push(`lhn_category.lvl${i}`);
      }
    }
  }

  const defaultpageRender = getBackToPlpPage(pageType);

  // For enabling/disabling hitsPerPage key in algolia calls.
  const enableHitsPerPage = drupalSettings.algoliaSearch.hitsPerPage;

  // hitsPerPage key value.
  const hits = groupEnabled ? 1000 : itemsPerPage;

  finalFilter = `${finalFilter}(${filters.join(filterOperator)})`;

  const MobileFilterWrapper = isConfigurableFiltersEnabled()
    ? DynamicWidgets : React.Fragment;

  const maxValuesPerFacets = getMaxValuesFromFacets();

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
        {...(enableHitsPerPage && { hitsPerPage: hits })}
        filters={finalFilter}
        ruleContexts={context}
        optionalFilters={optionalFilter}
      />
      <PlpStickyFilter
        pageType={pageType}
      >
        {(callback) => (
          <>
            <ConditionalView condition={(groupEnabled)}>
              <div id="block-subcategoryblock" className="block-alshaya-sub-category-block">
                <div className="plp-subcategory-block">
                  {Object.keys(subCategories || {}).map((id) => (
                    <SubCategoryContent
                      category={subCategories[id]}
                    />
                  ))}
                </div>
              </div>
            </ConditionalView>
            <Filters
              indexName={indexName}
              limit={drupalSettings.algoliaSearch.topFacetsLimit}
              pageType="listing"
              callback={(callerProps) => callback(callerProps)}
              ruleContexts={context}
            />

            <ConditionalView condition={categoryFieldAttributes.length > 0}>
              <div className="c-facet c-accordion block-facet-blockcategory-facet-plp algolia-plp-category-facet">
                <h3 className="c-facet__title c-accordion__title c-collapse__title plp-category-facet-title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                <MobileFilterWrapper
                  maxValuesPerFacet={isConfigurableFiltersEnabled()
                    ? maxValuesPerFacets
                    : undefined}
                >
                  <PLPHierarchicalMenu
                    attributes={categoryFieldAttributes}
                    rootPath={lhnCategoryFilter}
                    facetLevel={1}
                    ref={plpCategoryRef}
                    showParentLevel={false}
                  />
                </MobileFilterWrapper>
              </div>
            </ConditionalView>

            <div className="show-all-filters-algolia show-all-filters hide-for-desktop" ref={allFiltersRef}>
              <span className="desktop">{Drupal.t('all filters')}</span>
              <span className="upto-desktop">{Drupal.t('filter & sort')}</span>
            </div>
          </>
        )}
      </PlpStickyFilter>
      <AllFilters
        wrapperClassName="block-alshaya-algolia-plp-facets-block-all"
        AllFilterClass="all-filters all-filters-plp-algolia"
        pageType={pageType}
      >
        {(callback) => (
          <Filters
            indexName={indexName}
            pageType="listing"
            callback={(callerProps) => callback(callerProps)}
            ruleContexts={context}
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
          <CurrentRefinements
            callback={(callerProps) => callback(callerProps)}
            pageType={pageType}
          />
        )}
      </SelectedFilters>
      {/* Show Become member content if helloMember is enabled and is guest user. */}
      { isHelloMemberEnabled()
      && !isUserAuthenticated()
      && (
        <BecomeHelloMember />
      )}
      <div id="plp-hits" className={`c-products-list product-${defaultcolgrid} view-algolia-plp`}>
        <PlpResultInfiniteHits
          defaultpageRender={defaultpageRender || false}
          gtmContainer="product listing page"
          pageType={pageType}
          pageNumber={searchState.page || 1}
          subCategories={subCategories}
        >
          {(paginationArgs) => (
            <PlpPagination {...paginationArgs}>
              {Drupal.t('Load more products')}
            </PlpPagination>
          )}
        </PlpResultInfiniteHits>
      </div>
      <PLPNoResults />
    </InstantSearch>
  );
};

export default withPlpUrlAliasSync(PlpApp, 'plp', drupalSettings.algoliaSearch.pageSubType);
