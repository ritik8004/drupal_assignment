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

/**
 * Algolia as of now is not providing `SortBy` for React
 * hierarchical menu component so we have custom method for sorting.
 */
function sortItemsByCount(items) {
  if (items === null || items.length === 0) {
    return items;
  }

  let sortedItems = [];
  sortedItems = items.slice(0);
  sortedItems.sort(function(a,b) {
    return b.count - a.count;
  });

  return sortedItems;
}

function getSortedItems(items) {
  let sortedItems = [];
  if (items !== null && items.length > 0) {
    // If super category is enabled then we sort the filters by result count.
    if (document.getElementById('block-supercategorymenu') !== null) {
      sortedItems = sortItemsByCount(items);
    }
    else {
      // Sort facet items in order of the megamenu.
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
  }
  else {
    sortedItems = items;
  }

  return sortedItems;
}

export {
  getFilters,
  hasCategoryFilter,
  getSortedItems
}
