/**
 * Rcs Event listener for swatch.
 */
(function main() {
  RcsEventManager.addListener('alshayaRcsAlterSwatch', function (e) {
    const rawProductData = window.commerceBackend.getProductData(e.detail.sku, false, false);
    rawProductData.variants.forEach(function (variant) {
      if (variant.product.sku === e.detail.variantSku) {
        // Update swatch elements.
        if (variant.product.swatch_data.swatch_type === 'image') {
          try {
            const data = JSON.parse(variant.product.assets_swatch);
            if (Drupal.hasValue(data[0].url)) {
              e.detail.colorOptionsList = Object.assign(e.detail.colorOptionsList, {
                display_value: '<img src="' + data[0].url + '">',
                swatch_type: data[0].image_type,
              });
            }
            else {
              throw new Error('Empty url.');
            }
          }
          catch (e) {
            Drupal.alshayaLogger('warning', 'Invalid swatch asset data for sku @sku. @message', {
              '@sku': variant.product.sku,
              '@message': e.message,
            });
          }
        }
        // Override color label.
        e.detail.colorOptionsList.display_label = variant.product.color_label;
      }
    })
  });
})();

