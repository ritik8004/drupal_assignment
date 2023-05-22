// Ref: https://github.com/algolia/react-instantsearch/blob/v5.7.0/packages/react-instantsearch-core/src/core/indexUtils.js
import _objectSpread from '@babel/runtime/helpers/esm/objectSpread';
import omit from 'lodash/omit';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

/**
* As we have copied the file from algolia repo and modified it for our need
* we may not want to change anything here or change any eslint rule for this.
*/

// eslint-disable-next-line max-params
function refineMultiIndexWithNamespace(
  searchState,
  nextRefinement,
  indexId,
  resetPage,
  namespace,
) {
  const page = resetPage ? { page: 1 } : undefined;
  const state = searchState.indices && searchState.indices[indexId]
    ? {
      ...searchState.indices,
      [indexId]: {
        ...searchState.indices[indexId],
        [namespace]: {
          ...searchState.indices[indexId][namespace],
          ...nextRefinement,
        },
        page: 1,
      },
    }
    : {
      ...searchState.indices,
      [indexId]: {
        [namespace]: nextRefinement,
        ...page,
      },
    };

  return {
    ...searchState,
    indices: state,
  };
}

function refineMultiIndex(searchState, nextRefinement, indexId, resetPage) {
  const page = resetPage ? { page: 1 } : undefined;
  const state = searchState.indices && searchState.indices[indexId]
    ? {
      ...searchState.indices,
      [indexId]: {
        ...searchState.indices[indexId],
        ...nextRefinement,
        ...page,
      },
    }
    : {
      ...searchState.indices,
      [indexId]: {
        ...nextRefinement,
        ...page,
      },
    };

  return {
    ...searchState,
    indices: state,
  };
}

function refineSingleIndex(searchState, nextRefinement, resetPage) {
  const page = resetPage ? { page: 1 } : undefined;
  return { ...searchState, ...nextRefinement, ...page };
}

function refineSingleIndexWithNamespace(
  searchState,
  nextRefinement,
  resetPage,
  namespace,
) {
  const page = resetPage ? { page: 1 } : undefined;
  return {
    ...searchState,
    [namespace]: { ...searchState[namespace], ...nextRefinement },
    ...page,
  };
}

function cleanUpValueWithSingleIndex({
  searchState,
  id,
  namespace,
  attribute,
}) {
  if (namespace) {
    return {
      ...searchState,
      [namespace]: omit(searchState[namespace], attribute),
    };
  }

  return omit(searchState, id);
}

function cleanUpValueWithMutliIndex({
  searchState,
  indexId,
  id,
  namespace,
  attribute,
}) {
  const indexSearchState = searchState.indices[indexId];

  if (namespace && indexSearchState) {
    return {
      ...searchState,
      indices: {
        ...searchState.indices,
        [indexId]: {
          ...indexSearchState,
          [namespace]: omit(indexSearchState[namespace], attribute),
        },
      },
    };
  }

  return omit(searchState, `indices.${indexId}.${id}`);
}

export function getIndexId(context) {
  return context && context.multiIndexContext
    ? context.multiIndexContext.targetedIndex
    : context.ais.mainTargetedIndex;
}

export function getResults(searchResults, context) {
  if (searchResults.results && !searchResults.results.hits) {
    return searchResults.results[getIndexId(context)]
      ? searchResults.results[getIndexId(context)]
      : null;
  }
  return searchResults.results ? searchResults.results : null;
}

export function hasMultipleIndices(context) {
  return context && context.multiIndexContext;
}

// eslint-disable-next-line max-params
export function refineValue(
  searchStateParam,
  nextRefinement,
  context,
  resetPage,
  namespace,
) {
  let searchState = searchStateParam;

  if (hasMultipleIndices(context)) {
    const indexId = getIndexId(context);
    return namespace
      ? refineMultiIndexWithNamespace(
        searchState,
        nextRefinement,
        indexId,
        resetPage,
        namespace,
      )
      : refineMultiIndex(searchState, nextRefinement, indexId, resetPage);
  }
  // When we have a multi index page with shared widgets we should also
  // reset their page to 1 if the resetPage is provided. Otherwise the
  // indices will always be reset
  // see: https://github.com/algolia/react-instantsearch/issues/310
  // see: https://github.com/algolia/react-instantsearch/issues/637
  if (searchState.indices && resetPage) {
    Object.keys(searchState.indices).forEach((targetedIndex) => {
      searchState = refineValue(
        searchState,
        { page: 1 },
        { multiIndexContext: { targetedIndex } },
        true,
        namespace,
      );
    });
  }
  return namespace
    ? refineSingleIndexWithNamespace(
      searchState,
      nextRefinement,
      resetPage,
      namespace,
    )
    : refineSingleIndex(searchState, nextRefinement, resetPage);
}

function getNamespaceAndAttributeName(id) {
  const parts = id.match(/^([^.]*)\.(.*)/);
  const namespace = parts && parts[1];
  const attributeName = parts && parts[2];

  return { namespace, attributeName };
}

function hasRefinements({
  multiIndex,
  indexId,
  namespace,
  attributeName,
  id,
  searchState,
}) {
  if (multiIndex && namespace) {
    return (
      searchState.indices
      && searchState.indices[indexId]
      && searchState.indices[indexId][namespace]
      && Object.prototype.hasOwnProperty.call(
        searchState.indices[indexId][namespace], attributeName,
      )
    );
  }

  if (multiIndex) {
    return (
      searchState.indices
      && searchState.indices[indexId]
      && Object.prototype.hasOwnProperty.call(
        searchState.indices[indexId],
        id,
      )
    );
  }

  if (namespace) {
    return (
      searchState[namespace]
      && Object.prototype.hasOwnProperty.call(
        searchState[namespace],
        attributeName,
      )
    );
  }

  return Object.prototype.hasOwnProperty.call(searchState, id);
}

function getRefinements({
  multiIndex,
  indexId,
  namespace,
  attributeName,
  id,
  searchState,
}) {
  if (multiIndex && namespace) {
    return searchState.indices[indexId][namespace][attributeName];
  }
  if (multiIndex) {
    return searchState.indices[indexId][id];
  }
  if (namespace) {
    return searchState[namespace][attributeName];
  }

  return searchState[id];
}

export function getCurrentRefinementValue(
  props,
  searchState,
  context,
  id,
  defaultValue,
) {
  const indexId = getIndexId(context);
  const { namespace, attributeName } = getNamespaceAndAttributeName(id);
  const multiIndex = hasMultipleIndices(context);
  const args = {
    multiIndex,
    indexId,
    namespace,
    attributeName,
    id,
    searchState,
  };
  const hasRefinementsValue = hasRefinements(args);

  if (hasRefinementsValue) {
    return getRefinements(args);
  }

  if (props.defaultRefinement) {
    return props.defaultRefinement;
  }

  return defaultValue;
}

export function cleanUpValue(searchState, context, id) {
  const indexId = getIndexId(context);
  const { namespace, attributeName } = getNamespaceAndAttributeName(id);

  if (hasMultipleIndices(context) && Boolean(searchState.indices)) {
    return cleanUpValueWithMutliIndex({
      attribute: attributeName,
      searchState,
      indexId,
      id,
      namespace,
    });
  }

  return cleanUpValueWithSingleIndex({
    attribute: attributeName,
    searchState,
    id,
    namespace,
  });
}

export function addAbsolutePositions(hits, hitsPerPage, page) {
  // Used Drupal settings to know the pages based on set number of items.
  const itemsPerPage = parseInt(drupalSettings.algoliaSearch.itemsPerPage, 10);
  const pageIndex = (hitsPerPage > itemsPerPage) ? 0 : page;
  return hits.map((hit, index) => _objectSpread({}, hit, {
    __position: hitsPerPage * pageIndex + index + 1,
  }));
}
export function addQueryID(hits, queryID) {
  if (!queryID) {
    return hits;
  }

  return hits.map((hit) => _objectSpread({}, hit, {
    __queryID: queryID,
  }));
}

// Helper function to return the status of productListIndexStatus
// if alshaya_algolia_product_list_index is enabled in Drupal
// this will return true else false
export function productListIndexStatus() {
  if (drupalSettings.algoliaSearch.productListIndexStatus === true) {
    return true;
  }
  return false;
}

/**
 * Get product frame enabled / disabled.
 *
 * @returns {boolean}
 */
export function isProductFrameEnabled() {
  if (drupalSettings.algoliaSearch.productFrameEnabled === true) {
    return true;
  }
  return false;
}

/**
 * Get promotion frame enabled / disabled.
 *
 * @returns {boolean}
 */
export function isPromotionFrameEnabled() {
  if (drupalSettings.algoliaSearch.promotionFrameEnabled === true) {
    return true;
  }
  return false;
}

/**
 * Get product title trim status.
 *
 * @returns {boolean}
 */
export function isProductTitleTrimEnabled() {
  if (drupalSettings.algoliaSearch.productTitleTrimEnabled === true) {
    return true;
  }
  return false;
}

/**
 * Get product elements alignment status.
 *
 * @returns {boolean}
 */
export function isProductElementAlignmentEnabled() {
  if (drupalSettings.algoliaSearch.productElementAlignmentEnabled === true) {
    return true;
  }
  return false;
}

/**
 * Get sort index from local storage.
 */
export const getBackToPlpPageIndex = () => {
  const plplocalStorage = Drupal.getItemFromLocalStorage(`plp:${window.location.pathname}`);
  if (plplocalStorage && typeof plplocalStorage.sort !== 'undefined') {
    window.algoliaPlpSortIndex = plplocalStorage.sort;
  }
  return window.algoliaPlpSortIndex || null;
};

/**
 * Check if we need to display price range.
 */
export function hasPriceRange(alshayaPriceRange) {
  return drupalSettings.reactTeaserView.isPriceModeFromTo
    && hasValue(alshayaPriceRange)
    && (alshayaPriceRange.to.min !== alshayaPriceRange.to.max)
    && (alshayaPriceRange.to.min !== 0 || alshayaPriceRange.to.max !== 0);
}

/**
 * Converts escaped hyphen to hyphen.
 *
 * @param {string} value
 *   The value to unescape.
 *
 * @returns
 *   The unescaped value.
 */
export function unescapeFacetValue(value) {
  return value.replace(/^\\-/, '-');
}
