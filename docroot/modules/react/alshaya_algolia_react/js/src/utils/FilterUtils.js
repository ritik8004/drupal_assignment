const _ = require("lodash");

/**
 * Get all the filters as array.
 */
function getAllFilters() {
  return (typeof drupalSettings.algoliaSearch.filters === 'object')
    ? Object.values(drupalSettings.algoliaSearch.filters)
    : drupalSettings.algoliaSearch.filters;
}

/**
 * Get all the filters that we want to show for sticky filter
 * panel on top and "All filters".
 *
 * As of now, filtering out field_category, as it is not
 * displayed anywhere and ideally it will be part of lhn sidebar.
 */
function getFilters() {
  let filters = getAllFilters();
  _.remove(filters, function (filter) {
    return filter.identifier === 'field_category';
  });
  return filters;
}

/**
 * Return true if filters contains field_category_name field,
 * which is used for hierarchical menu facet in lhn sidebar.
 */
function hasCategoryFilter() {
  if (drupalSettings.algoliaSearch.enable_lhn_tree_search) {
    // category hierarchical menu in lhn side.
    let filters = getAllFilters();
    const isCategoryPresent = _.findIndex(filters, { 'identifier': 'field_category' });
    if (isCategoryPresent) {
      return (isCategoryPresent >= 0);
    }
  } else {
    return false;
  }
}

export {
  getFilters,
  hasCategoryFilter
}
