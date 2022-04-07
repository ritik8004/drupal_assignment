// Render function to prepare the markup for PLP mobile menu Block and replace
// placeholders with API Response.
exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  // Get row item HTML.
  let rowElm = innerHtmlObj.find('.view-content');
  // Get all the child elements.
  let childElm = getAllChildElements(entity);
  // Filter out all the child elements marked to be used in PLP Mobile Menu.
  childElm = childElm.filter(item => {
    return item.category_quick_link_plp_mob;
  });
  // Replace the placeholders with proper value.
  let itemHtml = '';
  if (childElm) {
    childElm.forEach(item => {
      itemHtml += replacePlpMobileMenuPlaceHolders(item, rowElm.find('.views-row')[0].outerHTML, settings);
    });
  }
  rowElm.html(itemHtml);

  return innerHtmlObj.html();
}

/**
 * Get all the child element of the current viewing category.
 *
 * @param {object} entity
 *   Object containing all category items.
 * @returns
 *   An array of all the child elements of the current viewing category.
 */
const getAllChildElements = function (entity) {
  // Iterate through the inputs items.
  if (entity && entity.hasOwnProperty('children')) {
    return entity.children;
  }

  return [];
}


/**
 * Replace the placeholders with the Mobile menu block items.
 *
 * @param {object} item
 *   The individual item object containing category info.
 * @param {string} itemHtml
 *   The App navigation HTML with Placeholders.
 * @param {object} settings
 *   The drupalSettings object.
 * @returns
 *   {string} Mobile menu HTML with proper data.
 */
const replacePlpMobileMenuPlaceHolders = function (item, itemHtml, settings) {
  // Add lang code in URL path.
  item.url_path = Drupal.url(`${item.url_path}/`);
  rcsPhReplaceEntityPh(itemHtml, 'plpMobileMenu', item, settings.path.currentLanguage)
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
