/**
 * Global variable to store acq_product related data/methods.
 */
window.Product = window.Product || {};

/**
 * Gets the required data for acq_product.
 *
 * @param {string} sku
 *   The product sku value.
 * @param {string} productKey
 *   The product view mode.
 *
 * @returns {Object}
 *    The product data.
 */
window.Product.getProductData = function (sku, productKey) {
  return drupalSettings[productKey][sku];
}
