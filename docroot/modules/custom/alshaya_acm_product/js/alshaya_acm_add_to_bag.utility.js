(function (Drupal, $) {
  window.commerceBackend = window.commerceBackend || {};

   /**
   * Return product info from backend.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} parentSKU
   *   (optional) The parent sku value.
   *
   * @returns {object}
   *   The product info object.
   */
  window.commerceBackend.getProductDataAddToBagListing = async function (sku, parentSKU) {
    var sku = Drupal.hasValue(parentSKU) ? parentSKU : sku;
    var storageKey = getProductInfoStorageKey(sku);

    // Return null if the sku is undefined or null.
    if (typeof sku === 'undefined' || sku === null) {
      Drupal.removeItemFromLocalStorage(storageKey);
      return new Promise((resolve) => {
        resolve(null);
      });
    }

    // Check and return if product info available in storage and not expired.
    const productInfo = Drupal.getItemFromLocalStorage(storageKey);

    // If data is not available in storage, we flag it to check/fetch from api.
    if (productInfo && !productInfo.title) {
      productInfo = null;
    }
    // Return product info from storage.
    if (productInfo !== null) {
      return new Promise((resolve) => {
        resolve(productInfo);
      });
    }

    // Prepare the product info api url.
    const apiUrl = Drupal.url(`rest/v1/product-info/${btoa(sku)}`);

    // If product's info isn't available, fetch via api.
    return new Promise(function (resolve, reject) {
      $.ajax({
        url: apiUrl,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          resolve(response);
        },
        error: function (xhr, textStatus, error) {
          // Processing of error here.
          Drupal.removeItemFromLocalStorage(storageKey);
          Drupal.logJavascriptError('Failed to fetch product info data.', error, 'product_info_resource');
          reject;
        }
      });
    });
  }

  /**
   * Get the product info local storage key.
   * Encoded sku so the sku with slash(s) doesn't break the key.
   *
   * @returns {string}
   */
  var getProductInfoStorageKey = function getProductInfoStorageKey(sku) {
    return "productinfo:".concat(btoa(sku), ":").concat(drupalSettings.path.currentLanguage);
  };
})(Drupal, jQuery);
