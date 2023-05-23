/**
 * Recursively processes Lhn menu children.
 *
 * @param {object} item
 *   Mdc category items.
 * @param {object} settings
 *   Drupal settings.
 * @param {object} enrichmentData
 *   Category enrichments.
 *
 * @returns {object}
 *   Processed Lhn menu item.
 */
function processLhnMenu(item, settings, enrichmentData) {
  let childItems = [];
  if (Drupal.hasValue(item.children) && item.children.length > 0) {
    item.children.forEach(function eachItem(childItem) {
      if (childItem.show_in_lhn === 1) {
        childItems.push(processLhnMenu(childItem, settings, enrichmentData));
      }
    });
  }

  // Check based on enrichment, if clickable is set or not.
  let clickable = true;
  let enrichmentDataObj = {};
  if (enrichmentData && enrichmentData[item.url_path]) {
    enrichmentDataObj = enrichmentData[item.url_path];
    if (!enrichmentDataObj.item_clickable) {
      clickable = false;
    }
  }

  // Add active class based on current path.
  let activeClass = globalThis.rcsWindowLocation().pathname === Drupal.url(`${item.url_path}/`)
    ? 'active'
    : '';

  // Check enrichments for path.
  let urlPath = typeof enrichmentDataObj.path !== 'undefined'
    ? enrichmentDataObj.path
    : Drupal.url(`${item.url_path}/`);

  // Prepare lhn menu obj.
  return {
    name: item.name,
    url: urlPath,
    level: (item.level - 1),
    active: activeClass,
    child: childItems,
    clickable,
  };
}

/**
 * Trim slashes from the beginning and ending of the string.
 *
 * @param {String} str
 *   String to trim.
 */
function trimSlashes(str) {
  return str.replace(/\/$/, '').replace(/^\//, '');
}

/**
 * Prepares Lhn menu.
 *
 * @param {object} settings
 *   Drupal settings.
 * @param {object} inputs
 *   Mdc categories.
 *
 * @returns {object}
 *   Returns menu obj to be rendered.
 */
exports.prepareData = function prepareData(settings, inputs) {
  // @todo Handle special base where we separate URL by - instead of /.
  const firstLevelTermUrl = Drupal.getTopLevelCategoryUrl();
  let menuItems = [];
  if (firstLevelTermUrl) {
    let catItems = inputs.filter(function filterCat(input) {
      return trimSlashes(input.url_key) === trimSlashes(firstLevelTermUrl);
    });
    if (catItems.length > 0 && !!catItems[0].children) {
      // Get the enrichment data.
      let enrichmentData = globalThis.rcsGetEnrichedCategories();
      catItems[0].children.forEach(function eachValue(item) {
        if (item.show_in_lhn === 1) {
          menuItems.push(processLhnMenu(item, settings, enrichmentData));
        }
      });
    }
  }

  return menuItems;
}
