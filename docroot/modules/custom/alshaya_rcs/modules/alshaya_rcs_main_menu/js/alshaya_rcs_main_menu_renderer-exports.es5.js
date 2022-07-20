exports.render = function render(
  settings,
  inputs,
  innerHtml,
  navigationType
) {
  // @todo Replace this implementation with Handlebars templates.
  if (navigationType !== 'shop_by_block') {
    return;
  }
  if (inputs.length == 0) {
    return '';
  }

  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  // Get the enrichment data. It's a sync call.
  // Check if static storage is having value, If 'YES' then use that else call
  // the API.
  let enrichmentData = globalThis.rcsGetEnrichedCategories();
  let menuListLevel1Ele = innerHtmlObj.find('div.c-footer-menu');

  // Filter category menu items if include_in_menu flag true.
  inputs = filterAvailableItems(inputs);

  // Sort the remaining category menu items by position in asc order.
  inputs.sort(function (a, b) {
    return parseInt(a.position) - parseInt(b.position);
  });

  let menuHtml = '';
  // Get the active super category.
  const isSuperCategoryEnabled = (typeof settings.superCategory) != "undefined";
  // Proceed only if superCategory is enabled.
  if (isSuperCategoryEnabled) {
    let activeSuperCategory = globalThis.rcsWindowLocation().pathname.split('/')[2];
    // Check if the active super category is valid or not.
    let validSuperCategory = false;
    inputs.forEach((item) => {
      if (activeSuperCategory == item.url_path) {
        validSuperCategory = true;
      }
    });
    if (!validSuperCategory && inputs[0].url_path.length) {
      // If there are no active super category then make first item as default.
      activeSuperCategory = inputs[0].url_path;
    }
    // Filter out the items that doesn't belong to the active super category.
    if (isSuperCategoryEnabled) {
      inputs = inputs.filter((item) => {
        return activeSuperCategory == item.url_path;
      });
    }
  }
  // Iterate over each L1 item and get the inner markup
  // prepared recursively.
  inputs.forEach(function eachCategory(level1) {
    // Change item level only if super category is enabled.
    if (isSuperCategoryEnabled) {
      // Return from here if L2 element is empty and if the child element is
      // not associated with the current active super category.
      if (!level1.children) {
        return;
      }
      level1 = level1.children[0];
    }

    menuHtml += getShopByMarkup(
      level1,
      1,
      innerHtmlObj,
      settings,
      enrichmentData,
      isSuperCategoryEnabled,
    );
  });

  // Remove the placeholders markup.
  menuListLevel1Ele.find('div.c-footer-menu__tab').remove();

  // Update with the resultant markups.
  menuListLevel1Ele.append(menuHtml);

  return innerHtmlObj.html();
};

/**
 *
 * @param {object} levelObj
 * @param {integer} level
 * @param {string} phHtmlObj
 * @param {object} settings
 * @param {object} enrichmentData
 * @param {boolean} isSuperCategoryEnabled
 *
 * @returns
 *  {string} Generated shop by markup for given level.
 */
const getShopByMarkup = function (levelObj, level, phHtmlObj, settings, enrichmentData, isSuperCategoryEnabled) {
  // We support max depth by L4.
  if (level > parseInt(drupalSettings.alshayaRcs.navigationMenu.menuMaxDepth)) {
    return;
  }

  // @todo remove this when API return the correct path.
  const levelObjOrgUrlPath = levelObj.url_path;
  // Append category prefix in L2 if super category is enabled.
  if (isSuperCategoryEnabled) {
    let urlItems = levelObj.url_path.split('/');
    if (urlItems.length > 1) {
      urlItems[1] = `${settings.rcsPhSettings.categoryPathPrefix}${urlItems[1]}`;
    }
    levelObj.url_path = urlItems.join('/').replace(/\/+$/, '/');
  } else {
    levelObj.url_path = levelObjOrgUrlPath.replace(/\/+$/, '/');
  }

  // Clone the default clickable placeholder element from the given html.
  const levelIdentifier = `c-footer-menu__tab`;
  let clonePhEle = phHtmlObj.find(`div.${levelIdentifier}`).clone();

  let enrichedDataObj = {};
  // Get the enrichment data from the settings.
  if (enrichmentData && enrichmentData[levelObjOrgUrlPath]) {
    enrichedDataObj = enrichmentData[levelObjOrgUrlPath];
    // Override label from Drupal.
    levelObj.name = enrichedDataObj.name;

    // Exclude terms with overridden target link.
    if (typeof enrichedDataObj.path !== 'undefined') {
      return '';
    }
  }

  return navRcsReplacePh(clonePhEle, levelObj, 'shopbymenuItem');
};

/**
 * It will take the element, replace the navigation placeholders
 * in that and return the output Html.
 */
const navRcsReplacePh = function (phElement, entity, markupId) {
  const langcode = drupalSettings.path.currentLanguage;
  // Identify all the field placeholders and get the replacement
  // value. Parse the html to find all occurrences at apply the
  // replacement.
  let menuItemHtml = phElement[0].outerHTML;
  rcsPhReplaceEntityPh(menuItemHtml, markupId, entity, langcode)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];
      // Apply the replacement on all the elements containing the
      // placeholder.
      menuItemHtml = globalThis.rcsReplaceAll(menuItemHtml, fieldPh, entityFieldValue);
    });

  return menuItemHtml;
};

/**
 *
 * @param {array} catArray
 *
 * @returns Filtered array with items for which the value of
 *  include_in_menu property is true.
 */
const filterAvailableItems = function (catArray) {
  return catArray.filter(
    innerCatArray => (innerCatArray.include_in_menu === 1)
  );
};
