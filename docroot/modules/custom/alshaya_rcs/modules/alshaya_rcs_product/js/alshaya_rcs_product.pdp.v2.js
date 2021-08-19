/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

/**
 * Gets the required data for rcs_product.
 *
 * @param {string} sku
 *   The product sku value.
 *
 * @returns {Object}
 *    The processed product data.
 */
window.commerceBackend.getProductData = function (sku) {
  var product = RcsPhStaticStorage.get('product_' + sku);
  if (product) {
    return processProduct(product);
  }

  return null;
}

/**
 * Process the product so that it has the same structure as drupalSettings
 * productInfo key.
 *
 * @param {object} product
 *   The product object from the API response.
 *
 * @returns {Object}
 *    The processed product data.
 */
function processProduct(product) {
  var productData = {
    sku: product.sku,
    type: product.type_id,
    gtm_attributes: product.gtm_attributes,
  };

  return productData;
}
