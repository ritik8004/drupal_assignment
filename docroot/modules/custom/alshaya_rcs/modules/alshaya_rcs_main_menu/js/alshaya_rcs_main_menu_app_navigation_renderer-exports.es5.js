// Render function to prepare the markup for DP App navigation Block and replace
// placeholders with API Response.
exports.render = function render(
  settings,
  inputs,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  // Get child category tree based on current category.
  inputs = getCurrentCategoryNextLevelItems(settings.rcsPage.fullPath, inputs, settings);
  // Get the proper mobile menu markup.
  let mobileMenuHtml = getAppNavigationMarkup(
    inputs,
    2,
    innerHtmlObj,
    settings,
    3,
  );

  return mobileMenuHtml;
}

/**
 * Returns the app navigation markup.
 *
 * @param {object} inputs
 *   Object containing categories based on level.
 * @param {integer} level
 *   The level for which markup needs to be prepared.
 * @param {object} innerHtmlObj
 *   The object containing the full markup of the app navigation block.
 * @param {object} settings
 *   Drupal settings object.
 * @param {integer} maxLevel
 *   The max level that needs to be traversed.
 * @returns
 *   {string} Full markup of the app navigation block.
 */
const getAppNavigationMarkup = function(inputs, level, innerHtmlObj, settings, maxLevel) {
  // Proceed only if maxLevel is not reached or the inputs is not empty.
  if (!inputs || level > maxLevel) {
    return '';
  }
  // Get the placeholder.
  let placeHolderObj = innerHtmlObj.find('div.menu-terms-link ul');
  let nextLevelItems = [];
  let itemHtml = '';
  // Now extract all the items marked to be used in App navigation.
  let items = getInAppNavigationItems(inputs);
  if (items) {
    items.forEach(item => {
      itemHtml += replaceAppNavigationPlaceHolders(item, placeHolderObj.html(), settings);
      // Prepare the nextLevel items array.
      if (item.hasOwnProperty('children') && item.children.length) {
        nextLevelItems = nextLevelItems.concat(item.children);
      }
    });
  }
  // Get the div wrapper as the level finished here.
  let wrapperHtml = innerHtmlObj.clone();
  wrapperHtml.find('div.menu-terms-link').html(placeHolderObj.clone().html(itemHtml));
  // Replace the class placeholder with level based class.
  wrapperHtml = replaceAppNavigationPlaceHolders({
    'classes': `l${level}-terms`,
  }, wrapperHtml.html(), settings);
  // Prepare and call the function with next level items.
  wrapperHtml += getAppNavigationMarkup(nextLevelItems, level+1, innerHtmlObj, settings, maxLevel);

  return wrapperHtml;
}

/**
 * Get the next level items based on current category.
 *
 * @param {string} currentUrlPath
 *   Current URL path.
 * @param {object} inputs
 *   Object containing all category items.
 * @returns
 *   An array of next level elements.
 */
const getCurrentCategoryNextLevelItems = function(currentUrlPath, inputs) {
  // Iterate through the inputs items and compare the current URL path.
  let nextLevelItems = [];
  inputs.forEach(item => {
    if (item.hasOwnProperty('children') && item.url_path === currentUrlPath) {
      nextLevelItems = item.children;
    }
  });

  return nextLevelItems;
}

/**
 * Return category items that are marked to be used as In App.
 *
 * @param {object} items
 *   Object containing category items.
 * @returns
 *   Array of objects containing items marked for App navigation.
 */
const getInAppNavigationItems = function(items) {
  // Now extract all the items marked to be used in App navigation.
  items = items.filter((item) => {
    return item.show_in_app_navigation;
  });

  return items;
}


/**
 * Replace the placeholders with the App navigation block items.
 *
 * @param {object} item
 *   The individual item object containing category info.
 * @param {string} itemHtml
 *   The App navigation HTML with Placeholders.
 * @param {object} settings
 *   The drupalSettings object.
 * @returns
 *   {string} App navigation HTML with proper data.
 */
const replaceAppNavigationPlaceHolders = function (item, itemHtml, settings) {
  // Add lang code in URL path.
  item.url_path = Drupal.url(`${item.url_path}/`);
  rcsPhReplaceEntityPh(itemHtml, 'appNav', item, settings.path.currentLanguage)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];
      // Apply the replacement on all the elements containing the
      // placeholder. We filter to keep only the child element
      // and not the parent ones.
      itemHtml = globalThis.rcsReplaceAll(itemHtml, fieldPh, entityFieldValue);
    });

  return itemHtml;
}
