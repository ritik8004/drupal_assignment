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
 * @param {boolean} isSuperCategoryEnabled
 *   Current item is the first item of the super category menu or not.
 *
 * @returns {object}
 *   Processed menu item.
 */
function processCategory(catItem, enrichmentData, isSuperCategoryEnabled) {

  const level_url_path = catItem.url_path;
  // Append category prefix in L2 if super category is enabled.
  if (isSuperCategoryEnabled) {
    // Remove the supercategory from the path since the page URL already
    // contains the supercategory and this path will be getting appened to the
    // page url.
    catItem.url_path = level_url_path.replace(/[a-zA-Z0-9]+\//, '');
  } else {
    catItem.url_path = level_url_path.replace(/\/+$/, '/');
  }

  // Apply enrichments.
  if (enrichmentData && enrichmentData[level_url_path]) {
    enrichedDataObj = enrichmentData[level_url_path];
    // Override label from Drupal.
    catItem.name = enrichedDataObj.name;
    catItem.url_path = enrichedDataObj.url_path;
  }

  return catItem;
}

/**
 * Processes mdc categories for rendering.
 *
 * @param {object} settings
 *   The drupal settings object.
 * @param {object} inputs
 *   Mdc Categories.
 *
 * @returns {object}
 *   Returns prepared categories to be displayed.
 */
exports.prepareData = function prepareData(settings, inputs) {
  let enrichmentData = globalThis.rcsGetEnrichedCategories();
  // Clone the original input data so as to not modify it.
  let catItems = JSON.parse(JSON.stringify(inputs.children));
  catItems = filterAvailableItems(catItems);
  catItems.sort(function (a, b) {
    return parseInt(a.position) - parseInt(b.position);
  });
  // Get the active super category.
  let menuItems = [];
  catItems.forEach(function eachCategory(catItem) {
    menuItems.push(processCategory(catItem, enrichmentData, !!settings.superCategory));
  });

  return {
    menuItems: menuItems,
  };
}
