
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
 * Prepares plp mobile menu.
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
  let childElm = getAllChildElements(inputs);
  // Filter out all the child elements marked to be used in PLP Mobile Menu.
  let menuItems = childElm.filter(item => {
    return item.category_quick_link_plp_mob;
  });

  menuItems.forEach(function eachValue(item) {
    item.url_path = Drupal.url(`${item.url_path}/`);
    item.langcode = settings.path.currentLanguage;
  });

  return menuItems;
 }
