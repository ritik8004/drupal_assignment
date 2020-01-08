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
 * As of now, filtering out field_category_name, as it is not
 * displayed anywhere and ideally it will be part of lhn sidebar.
 */
function getFilters() {
  let filters = getAllFilters();
  _.remove(filters, function (filter) {
    return filter.identifier === 'field_category_name';
  });
  return filters;
}

/**
 * Return true if filters contains field_category_name field,
 * which is used for hierarchical menu facet in lhn sidebar.
 */
function hasCategoryFilter() {
  // @todo: remove this line once finalized that we want to show
  // category hierarchical menu in lhn side.
  return false;
  let filters = getAllFilters();
  const isCategoryPresent = _.findIndex(filters, { 'identifier': 'field_category_name' });
  return (isCategoryPresent >= 0);
}

export {
  getFilters,
  hasCategoryFilter
}
