/**
 * @file
 * JS file for product pages.
 */

/**
 * Provides the product description for RCS Product token replacements.
 * @see alshaya_rcs_product-exports.es5.js
 *
 * @param data
 *    The data.
 *
 * @return {string}
 *    The description.
 */
window.rcsGetProductDescription = function (data) {
  return data.description.html;
}
