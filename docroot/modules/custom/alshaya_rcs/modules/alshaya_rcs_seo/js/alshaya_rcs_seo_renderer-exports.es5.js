exports.render = function render(
  settings,
  inputs,
  innerHtml
) {
  if (!Drupal.hasValue(inputs)) {
    return;
  }
  // Clone the object.
  let menuData = JSON.parse(JSON.stringify(inputs));
  menuData.forEach((menuItem, key) => {
    // If item is not in the menu, it should not appear in the sitemap.
    if (typeof menuItem.include_in_menu !== 'undefined'
      && !menuItem.include_in_menu
    ) {
      delete (menuData[key]);
      return;
    }
    processEnrichment(menuItem);
  });
  return handlebarsRenderer.render('sitemap', {term_tree: menuData});
}

/**
 * Process and add the enrichments to the menu item.
 *
 * @param {Object} menuItem
 *   Individual menu item object.
 */
function processEnrichment(menuItem) {
  var enrichmentData = globalThis.rcsGetEnrichedCategories();
  var enrichedMenuItem = enrichmentData[menuItem.url_path];

  if (!Drupal.hasValue(enrichedMenuItem)) {
    return;
  }

  if (Drupal.hasValue(enrichedMenuItem.url_path)
    && menuItem.url_path !== enrichedMenuItem.url_path
  ) {
    menuItem.overridden_path = true;
  }

  menuItem = Object.assign(menuItem, enrichedMenuItem);
}
