import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { isConfigurableFiltersEnabled } from '../../../../js/utilities/helper';

const _ = require('lodash');

/**
 * Get all the filters as array.
 */
function getAllFilters(pageType) {
  return (typeof drupalSettings.algoliaSearch[pageType].filters === 'object')
    ? Object.values(drupalSettings.algoliaSearch[pageType].filters)
    : drupalSettings.algoliaSearch[pageType].filters;
}

/**
 * Get all the filters that we want to show for sticky filter
 * panel on top and "All filters".
 *
 * As of now, filtering out field_category and super_category, as it is not
 * displayed anywhere and ideally it will be part of lhn sidebar.
 */
function getFilters(pageType) {
  const filters = getAllFilters(pageType);
  _.remove(filters, (filter) => (filter.identifier === 'field_category' || filter.identifier === 'super_category'));
  return filters;
}

/**
 * Return true if filters contains field_category_name field,
 * which is used for hierarchical menu facet in lhn sidebar.
 */
function hasCategoryFilter() {
  if (drupalSettings.algoliaSearch.enable_lhn_tree_search) {
    // category hierarchical menu in lhn side.
    const filters = getAllFilters('search');
    const isCategoryPresent = _.findIndex(filters, { identifier: 'field_category' });
    if (isCategoryPresent) {
      return (isCategoryPresent >= 0);
    }
  }

  return false;
}

/**
 * Algolia as of now is not providing `SortBy` for React
 * hierarchical menu component so we have custom method for sorting.
 */
function sortItemsByCount(items) {
  let sortedItems = [];

  sortedItems = items.slice(0);
  sortedItems.sort((a, b) => b.count - a.count);

  return sortedItems;
}

function sortItemsByMegaMenu(items, selector, label) {
  const sortedItems = [];

  // Sort facet items in order of the megamenu.
  const weight = [];
  // Getting the attribute for L1 items from the menu.
  const l1MenuItems = document.querySelectorAll(selector);
  Object.keys(l1MenuItems).forEach((i) => {
    try {
      if (l1MenuItems[i].getAttribute(label) !== null) {
        // Add 10 to allow adding All at top.
        weight[l1MenuItems[i].getAttribute(label).toLowerCase().trim()] = parseInt(i, 10) + 10;
      }
    } catch (e) {
      // Do nothing.
    }
  });

  Object.keys(items).forEach((i) => {
    if (weight[items[i].label.toLowerCase().trim()] !== undefined) {
      sortedItems[weight[items[i].label.toLowerCase().trim()]] = items[i];
    } else if (items[i].label === window.Drupal.t('All')) {
      // Use 1 for All to ensure Object.values work properly.
      sortedItems[1] = items[i];
    }
  });

  return Object.values(sortedItems);
}

function getSortedItems(items, element) {
  if (items === null || items.length === 0 || isConfigurableFiltersEnabled()) {
    return items;
  }

  let sortedItems = [];

  switch (element) {
    case 'category': {
      // If super category is enabled then we sort the category filters by result count.
      if (document.getElementsByClassName('block-alshaya-super-category-menu').length > 0) {
        sortedItems = sortItemsByCount(items);
      } else {
        sortedItems = sortItemsByMegaMenu(items, '.menu--one__link', 'title');
      }
      break;
    }
    case 'supercategory': {
      if (document.getElementsByClassName('block-alshaya-super-category-menu').length > 0) {
        sortedItems = sortItemsByMegaMenu(items, '[data-super-category-label]', 'data-super-category-label');
      }
      break;
    }
    default:
      break;
  }

  return sortedItems;
}

/**
 * Return true if filters contains super_category field,
 * which is used for menu facet in lhn sidebar.
 */
function hasSuperCategoryFilter() {
  if (!drupalSettings.algoliaSearch.enable_lhn_tree_search) {
    return false;
  }
  const filters = getAllFilters('search');
  const isSuperCategoryPresent = _.findIndex(filters, { identifier: 'super_category' });
  if (isSuperCategoryPresent !== -1) {
    return (isSuperCategoryPresent >= 0);
  }
  return false;
}

/**
 * Return the alias for the given facet key OR return key for the given
 * facet alias.
 *
 * @param {*} key
 *   The key for which we need result.
 * @param {*} returnType
 *   Return type value to return. ("alias" or "key")
 */
function facetFieldAlias(key, returnType, pageType = null) {
  // Proceed only if key is defined.
  if (!hasValue(key)) {
    return key;
  }

  const { filters_alias: filtersAlias } = drupalSettings.algoliaSearch;
  let allFilters = '';
  if (pageType === 'plp') {
    const { filters } = drupalSettings.algoliaSearch.listing;
    allFilters = filters;
  } else {
    const { filters } = drupalSettings.algoliaSearch.search;
    allFilters = filters;
  }
  const facetField = key.split('.')[0];
  if (returnType === 'alias') {
    return allFilters[facetField].alias;
  }
  return filtersAlias[key];
}

/**
 * Redirect to url if keyword matches as per
 * rules configured in algolia dashboard.
 */
const customQueryRedirect = (items) => {
  const match = items.find((data) => Boolean(data.redirect));
  if (match && match.redirect) {
    window.location.href = match.redirect;
  }
  return [];
};

/**
 * Get Max values per facets from settings.
 *
 * @returns {number|*}
 *   Max values per facets.
 */
const getMaxValuesFromFacets = () => {
  const { maxValuesPerFacets } = drupalSettings.algoliaSearch;
  if (hasValue(maxValuesPerFacets)) {
    return maxValuesPerFacets;
  }

  return 1000;
};

export {
  getFilters,
  hasCategoryFilter,
  getSortedItems,
  hasSuperCategoryFilter,
  facetFieldAlias,
  customQueryRedirect,
  getMaxValuesFromFacets,
};
