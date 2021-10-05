/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

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
window.commerceBackend.getProductData = function (sku, productKey) {
  return drupalSettings[productKey][sku];
}

/**
 * Gets the configurable combinations for the given sku.
 *
 * @param {string} sku
 *   The sku value.
 *
 * @returns {object}
 *   The object containing the configurable combinations for the given sku.
 */
window.commerceBackend.getConfigurableCombinations = function (sku) {
  return drupalSettings.configurableCombinations[sku];
}
