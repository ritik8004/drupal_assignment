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
 * @param {object} settings
 *   The drupal settings object.
 * @param {object} enrichmentData
 *   Enriched data object for the current item.
 * @param {boolean} isSuperCategoryEnabled
 *   Current item is the first item of the super category menu or not.
 *
 * @returns {object}
 *   Processed menu item.
 */
function processCategory(catItem, settings, enrichmentData, isSuperCategoryEnabled) {

  const level_url_path = catItem.url_path;
  // Append category prefix in L2 if super category is enabled.
  if (isSuperCategoryEnabled) {
    let urlItems = catItem.url_path.split('/');
    if (urlItems.length > 1) {
      urlItems[1] = `${settings.rcsPhSettings.categoryPathPrefix}${urlItems[1]}`;
    }
    catItem.url_path = urlItems.join('/').replace(/\/+$/, '/');
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
  let catItems = filterAvailableItems(inputs);
  catItems.sort(function (a, b) {
    return parseInt(a.position) - parseInt(b.position);
  });
  // Get the active super category.
  const isSuperCategoryEnabled = !!settings.superCategory;
   // Proceed only if superCategory is enabled.
   if (isSuperCategoryEnabled) {
    let activeSuperCategory = globalThis.rcsWindowLocation().pathname.split('/')[2];
    // Check if the active super category is valid or not.
    let validSuperCategory = false;
    catItems.forEach((item) => {
      if (activeSuperCategory == item.url_path) {
        validSuperCategory = true;
      }
    });
    if (!validSuperCategory && catItems[0].url_path.length) {
      // If there are no active super category then make first item as default.
      activeSuperCategory = catItems[0].url_path;
    }
    // Filter out the items that doesn't belong to the active super category.
    if (isSuperCategoryEnabled) {
      catItems = catItems.filter((item) => {
        return activeSuperCategory == item.url_path;
      });
    }
  }

  let menuItems = [];
  catItems.forEach(function eachCategory(catItem) {
    menuItems.push(processCategory(catItem, settings, enrichmentData, isSuperCategoryEnabled));
  });

  return {
    menuItems: menuItems,
  };
}
