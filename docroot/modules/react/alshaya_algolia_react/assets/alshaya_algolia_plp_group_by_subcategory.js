window.commerceBackend = window.commerceBackend || {};

/**
 * Fetch Grouped Subcategories data from drupalSettings.
 *
 * @returns {object}
 *   Data for rendering.
 */
window.commerceBackend.getSubcategoryData = function getSubcategoryData() {
  return window.drupalSettings.algoliaSearch.subCategories;
};
