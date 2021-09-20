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
 
  if (inputs.length !== 0) {
    // Get the enrichment data. It's a sync call.
    // Check if static storage is having value, If 'YES' then use that else call
    // the API.
    let enrichmentData = rcsGetEnrichedCategories();

    // Get the L1 menu list element.
    const menuListLevel1Ele = innerHtmlObj.find('.menu__list.menu--one__list');

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
      let activeSuperCategory = rcsWindowLocation().pathname.split('/')[2];
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
      menuHtml += getMenuMarkup(
        level1,
        1,
        innerHtmlObj,
        settings,
        enrichmentData,
        isSuperCategoryEnabled,
      );
    });

    // Remove the placeholders markup.
    menuListLevel1Ele.find('li').remove();

    // Update with the resultant markups.
    menuListLevel1Ele.append(menuHtml);
  }
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
 *  {string} Generated menu markup for given level.
 */
const getMenuMarkup = function (levelObj, level, phHtmlObj, settings, enrichmentData, isSuperCategoryEnabled) {
  // We support max depth by L4.
  if (level > parseInt(drupalSettings.alshayaRcs.navigationMenu.menuMaxDepth)) {
    return;
  }
  const levelIdentifier = `level-${level}`;

  // Clone the default clickable placeholder element from the given html.
  var clonePhEle = phHtmlObj.find(`li.${levelIdentifier}.clickable`).clone();

  return navRcsReplacePh(clonePhEle, levelObj);
};

/**
 * It will take the element, replace the navigation placeholders
 * in that and return the output Html.
 */
const navRcsReplacePh = function (phElement, entity) {
  const langcode = drupalSettings.path.currentLanguage;
  const attributes = drupalSettings.rcsPhSettings.placeholderAttributes;

  // Identify all the field placeholders and get the replacement
  // value. Parse the html to find all occurrences at apply the
  // replacement.
  let menuItemHtml = phElement[0].outerHTML;
  rcsPhReplaceEntityPh(menuItemHtml, 'shopbymenuItem', entity, langcode)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];
      // Apply the replacement on all the elements containing the
      // placeholder.
      menuItemHtml = rcsReplaceAll(menuItemHtml, fieldPh, entityFieldValue);
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
