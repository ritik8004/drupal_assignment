import React, { useRef } from 'react';
import {
  Configure,
  InstantSearch,
  Stats,
} from 'react-instantsearch-dom';

import { searchClient } from '../config/SearchClient';

import { getSuperCategoryOptionalFilter, isMobile } from '../utils';
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

if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

const getBackToPlpPage = (pageType) => {
  const plplocalStorage = localStorage.getItem(`${pageType}:${window.location.pathname}`);
  if (plplocalStorage) {
    const plpPathData = JSON.parse(plplocalStorage);
    if (plpPathData.page) {
      return parseInt(plpPathData.page, 10);
    }
  }
  return null;
};

/**
 * Render search results elements facets, filters and sorting etc.
 */
const PlpApp = ({
  searchState, createURL, onSearchStateChange, pageType,
}) => {
  const plpCategoryRef = useRef();
  const allFiltersRef = useRef();

  const {
    indexName,
    itemsPerPage,
    filterOos,
    pageSubType,
    hierarchy: defaultCategoryFilter,
    category_field: categoryField,
    level: nestedLevel,
    max_category_tree_depth: categoryDepth,
    promotionNodeId,
    ruleContext,
    subCategories,
  } = drupalSettings.algoliaSearch;

  const filters = [];
  let finalFilter = '';
  let filterOperator = ' AND ';
  let groupEnabled = false;

  // Do not show out of stock products.
  if (filterOos === true) {
    finalFilter = '(stock > 0) AND ';
  }

  if (pageSubType === 'plp') {
    if (subCategories.length !== 0) {
      filterOperator = ' OR ';
      groupEnabled = true;
      // Set all the filters selected in sub category.
      Object.keys(subCategories).forEach((key) => {
        const subCategoryField = subCategories[key].category.category_field;
        const defaultSubCategoryFilter = subCategories[key].category.hierarchy;
        filters.push(`${subCategoryField}: "${defaultSubCategoryFilter}"`);
      });
    } else {
      // Filter for Category product listing page.
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
  if ((isMobile() && pageSubType === 'plp' && nestedLevel < parseInt(categoryDepth, 10) + 1)) {
    for (let i = 0; i <= nestedLevel; i++) {
      categoryFieldAttributes.push(`lhn_category.lvl${i}`);
    }
  }

  const defaultpageRender = getBackToPlpPage(pageType);

  finalFilter = `${finalFilter}(${filters.join(filterOperator)})`;

  return (
    <InstantSearch
      searchClient={searchClient}
      indexName={indexName}
      searchState={searchState}
      createURL={createURL}
      onSearchStateChange={onSearchStateChange}
    >
      <Configure
        clickAnalytics
        hitsPerPage={groupEnabled ? 1000 : itemsPerPage}
        filters={finalFilter}
        ruleContexts={ruleContext}
        optionalFilters={optionalFilter}
      />
      <PlpStickyFilter>
        {(callback) => (
          <>
            {(pageSubType === 'plp') ? (
              <ConditionalView condition={subCategories !== undefined}>
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
            ) : null}
            <Filters
              indexName={indexName}
              limit={4}
              pageType="listing"
              callback={(callerProps) => callback(callerProps)}
            />

            {(isMobile() && pageSubType === 'plp' && nestedLevel < parseInt(categoryDepth, 10) + 1) ? (
              <div className="c-facet c-accordion block-facet-blockcategory-facet-plp algolia-plp-category-facet">
                <h3 className="c-facet__title c-accordion__title c-collapse__title plp-category-facet-title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                <PLPHierarchicalMenu
                  attributes={categoryFieldAttributes}
                  rootPath={defaultCategoryFilter}
                  facetLevel={1}
                  ref={plpCategoryRef}
                  showParentLevel={false}
                />
              </div>
            ) : null}
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
      <div id="plp-hits" className="c-products-list product-small view-algolia-plp">
        <PlpResultInfiniteHits
          defaultpageRender={defaultpageRender || false}
          gtmContainer="product listing page"
          pageType={pageType}
          pageNumber={searchState.page || 1}
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
