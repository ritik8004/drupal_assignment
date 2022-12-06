/**
 * Filter available menu items.
 *
 * @param {array} catArray
 *   Mdc categories.
 *
 * @returns {array}
 *   Filtered array with items for which the value of
 *  include_in_menu property is true.
 */
 const filterAvailableItems = function (catArray) {
  return catArray.filter(
    innerCatArray => (innerCatArray.include_in_menu === 1)
  );
};

/**
 * Process rcs category menu items.
 *
 * @param {object} catItem
 *   Rcs Category menu item.
 * @param {object} enrichmentData
 *   Enriched data object for the current item.
 *
 * @returns {object}
 *   Processed menu item.
 */
function processCategory(catItem, enrichmentData) {

  const level_url_path = catItem.url_path;
  catItem.url_path = Drupal.url(level_url_path.replace(/\/+$/, '/'));

  // Apply enrichments.
  if (enrichmentData && enrichmentData[level_url_path]) {
    enrichedDataObj = enrichmentData[level_url_path];
    // Override label from Drupal.
    catItem.name = enrichedDataObj.name;
    if (typeof enrichedDataObj.url_path !== 'undefined') {
      catItem.url_path = Drupal.url(enrichedDataObj.url_path);
    }
  }

  return catItem;
}

/**
 * Processes mdc categories for rendering.
 *
 * @param {object} inputs
 *   Mdc Categories.
 *
 * @returns {object}
 *   Returns prepared categories to be displayed.
 */
exports.prepareData = function prepareData(inputs) {
  let enrichmentData = globalThis.rcsGetEnrichedCategories();
  // Clone the original input data so as to not modify it.
  let catItems = JSON.parse(JSON.stringify(inputs));
  catItems = filterAvailableItems(catItems);
  catItems.sort(function (a, b) {
    return parseInt(a.position) - parseInt(b.position);
  });
  // Get the active super category.
  let menuItems = [];
  catItems.forEach(function eachCategory(catItem) {
    menuItems.push(processCategory(catItem, enrichmentData));
  });

  return {
    menuItems: menuItems,
  };
}
