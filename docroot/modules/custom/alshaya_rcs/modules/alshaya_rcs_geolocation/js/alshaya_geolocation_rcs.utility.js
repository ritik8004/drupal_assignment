window.alshayaGeolocation = window.alshayaGeolocation || {};

(function alshayaGeolocationUtility($, drupalSettings) {
  /**
   * Returns the store labels.
   *
   * @returns {object}
   *   Store labels values.
   */
  window.alshayaGeolocation.getStoreLabelsPdp = function getStoreLabelsPdp() {
    var storeLabels = drupalSettings.storeLabels;
    var product = $('.entity--type-node').not('[data-sku *= "#"]').closest('[gtm-type="gtm-product-link"]');
    var sku = $(product).attr('data-sku');
    var productData = window.commerceBackend.getProductData(sku, null, false);
    var isCncAvailable = window.commerceBackend.isProductAvailableForClickAndCollect(productData);

    storeLabels = Object.assign(storeLabels, {
      sku,
      type: productData.type,
      state: isCncAvailable ? 'enabled' : 'disabled',
      subtitle: isCncAvailable ? storeLabels.subtitle_texts['enabled'] : storeLabels.subtitle_texts['disabled']
    });

    return storeLabels;
  }
})(jQuery, drupalSettings);
