/**
 * Rcs Event listener for swatch.
 */
(function main(Drupal, RcsEventManager) {
  RcsEventManager.addListener('alshayaRcsAlterSwatch', function alshayaRcsAlterSwatch (e) {
    // Update swatch elements.
    if (e.detail.variant.product.swatch_data.swatch_type === 'image') {
      try {
        const data = JSON.parse(e.detail.variant.product.assets_swatch);
        if (!Drupal.hasValue(data[0].url)) {
          throw new Error('Empty url.');
        }

        e.detail.colorOptionsList = Object.assign(e.detail.colorOptionsList, {
          display_value: '<img src="' + data[0].url + '">',
          swatch_type: data[0].image_type,
        });
      }
      catch (e) {
        Drupal.alshayaLogger('warning', 'Invalid swatch asset data for sku @sku. @message', {
          '@sku': e.detail.variant.product.sku,
          '@message': e.message,
        });
      }
    }
  });
})(Drupal, RcsEventManager);

