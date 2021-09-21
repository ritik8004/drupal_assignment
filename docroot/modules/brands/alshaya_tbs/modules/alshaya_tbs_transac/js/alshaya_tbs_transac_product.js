/**
 * @file
 * JS file for product pages.
 */

/**
 * Provides the product title for RCS Product token replacements.
 * @see alshaya_rcs_product-exports.es5.js
 *
 * @param data
 *    The data.
 *
 * @return {string}
 *    The title.
 */
window.rcsGetProductTitle = function (data) {
  // @todo aggregate title when CORE-34014 is done.
  return data.name;
};

/**
 * Provides the product description for RCS Product token replacements.
 * @see alshaya_rcs_product-exports.es5.js
 *
 * @param data
 *    The data.
 *
 * @return {object}
 *    The object containing label and value.
 */
window.rcsGetProductDescription = function (data) {
  // @todo aggregate data, including ingredients, etc. when CORE-34014.
  return {
    label: Drupal.t('Features and benefits'),
    value: data.description.html
  };
};

/**
 * Provides the product short description for RCS Product token replacements.
 * @see alshaya_rcs_product-exports.es5.js
 *
 * @param data
 *    The data.
 *
 * @return {object}
 *    The object containing label and value.
 */
window.rcsGetProductShortDescription = function (data) {
  return rcsGetProductDescription(data);
};
