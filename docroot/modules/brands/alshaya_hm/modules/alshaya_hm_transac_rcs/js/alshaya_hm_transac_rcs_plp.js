/**
 * Rcs Event listner for swatch.
 */
 (function main() {
    RcsEventManager.addListener('alshayaRcsAlterSwatch', function (e) {
      const rawProductData = window.commerceBackend.getProductData(e.detail.sku, false, false);
      rawProductData.variants.forEach(function (variant) {
        if (variant.product.sku === e.detail.variantSku) {
          try {
            const data = JSON.parse(variant.product.assets_swatch);
            // @todo Uncomment this when proper type is available.
            // if (data.type === 'StillMedia/Fabricswatch') {
              e.detail.colorOptionsList = Object.assign(e.detail.colorOptionsList, {
                // @todo Use the proper image style.
                display_value: data[0].styles.pdp_gallery_thumbnail,
                swatch_type: data[0].image_type,
              });
            // }
          } catch (e) {
            // Do nothing.
          }
        }
      })
    });
    
  })();