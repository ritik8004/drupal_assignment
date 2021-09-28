// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.
exports.render = function render(
  settings,
  inputs,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  // Extract L2 HTML.
  let l2Elm = innerHtmlObj.find('div.l2-terms li');

  // Export the URL Path from the current URL.
  const currentUrlPath = drupalSettings.rcsPage.fullPath;
  if (currentUrlPath) {
    let itemHtml = '';
    let l3Items = [];
    // Now extract the L2 items based on current URL path.
    let l2Items = getL2Items(currentUrlPath, inputs, settings);
    // Now extract all the L2 items marked to be used in App navigation.
    l2Items = getInAppNavigationItems(l2Items);
    if (l2Items) {
      l2Items.forEach(item => {
        itemHtml += replaceAppNavigationPlaceHolders(item, l2Elm[0].outerHTML, settings);
        // Prepare the L3 items array.
        if (item.hasOwnProperty('children') && item.children.length) {
          l3Items = l3Items.concat(item.children);
        }
      });
      // Update the L2 Elm object.
      l2Elm.html(itemHtml);

      // Extract L3 HTML.
      let l3Elm = innerHtmlObj.find('div.l3-terms li');
      // Reset itemHtml;
      itemHtml = '';
      l3Items = getInAppNavigationItems(l3Items);
      if (l3Items) {
        l3Items.forEach(item => {
          itemHtml += replaceAppNavigationPlaceHolders(item, l3Elm[0].outerHTML, settings);
        });
        // Update the L3 Elm object.
        l3Elm.html(itemHtml);
      }
    }
  }

  return innerHtmlObj.html();
}

/**
 * Get all L2 items based on current URL path.
 *
 * @param {string} currentUrlPath
 *   Current URL path.
 * @param {object} inputs
 *   Object containing all category items.
 * @returns
 *   An array of l2 elements of current URL path category.
 */
const getL2Items = function(currentUrlPath, inputs) {
  // Iterate through the inputs items and compare the current URL path.
  let l2Items = [];
  inputs.forEach(item => {
    if (item.hasOwnProperty('children') && item.url_path === currentUrlPath) {
      l2Items = item.children;
    }
  });

  return l2Items;
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
  item.url_path = `/${settings.path.pathPrefix}${item.url_path}/`
  rcsPhReplaceEntityPh(itemHtml, 'app_nav', item, settings.path.currentLanguage)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];
      // Apply the replacement on all the elements containing the
      // placeholder. We filter to keep only the child element
      // and not the parent ones.
      itemHtml = rcsReplaceAll(itemHtml, fieldPh, entityFieldValue);
    });

  return itemHtml;
}
