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
 * @param {string} productKey
 *   The product view mode.
 *
 * @returns {Object}
 *    The processed product data.
 */
window.commerceBackend.getProductData = function (sku, productKey) {
  if (typeof sku === 'undefined' || !sku) {
    var allStorageData = RcsPhStaticStorage.getAll();
    var productData = {};
    Object.keys(allStorageData).forEach(function (key) {
      if (key.startsWith('product_')) {
        productData[allStorageData[key].sku] = processProduct(allStorageData[key]);
      }
    });

    return productData;
  }

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

  // Add general bazaar voice data to product data if present.
  if (typeof drupalSettings.alshaya_bazaar_voice !== 'undefined') {
    var bazaarVoiceData = drupalSettings.alshaya_bazaar_voice;
    bazaarVoiceData.product = {
      url: '/' + drupalSettings.path.currentLanguage + '/buy-' + product.url_key + '.html',
      title: product.name,
      image_url: '',
    }
    productData.alshaya_bazaar_voice = drupalSettings.alshaya_bazaar_voice;
  }

  return productData;
}
