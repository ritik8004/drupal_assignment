/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function (Drupal, $) {

   /**
   * Return product info from backend.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} styleCode
   *   (optional) Style code value.
   *
   * @returns {object}
   *   The product info object.
   */
  window.commerceBackend.getProductDataAddToBagListing = async function (productSKU, styleCode) {
    var storageKey = getProductInfoStorageKey(productSKU);

    // Return null if the sku is undefined or null.
    if (typeof productSKU === 'undefined' || productSKU === null) {
      Drupal.removeItemFromLocalStorage(storageKey);
      return null;
    }

    // Check and return if product info available in storage and not expired.
    let productInfo = Drupal.getItemFromLocalStorage(storageKey);

    // If data is not available in storage, we flag it to check/fetch from api.
    if (productInfo && !productInfo.title) {
      productInfo = null;
    }
    // Return product info from storage.
    if (productInfo !== null) {
      return productInfo;
    }

    // Prepare the product info api url.
    const apiUrl = Drupal.url(`rest/v1/product-info/${btoa(productSKU)}`);

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
          Drupal.alshayaLogger('error', 'Failed to fetch product info data: @error', {
            '@error': error
          });
          reject(error);
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
    return "productinfo:" + btoa(sku) + ":" + drupalSettings.path.currentLanguage;
  };
})(Drupal, jQuery);
