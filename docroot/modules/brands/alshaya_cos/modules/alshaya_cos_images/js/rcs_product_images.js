/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function () {
  RcsEventManager.addListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if ((typeof e.detail.pageType !== 'undefined' && e.detail.pageType !== 'product')
      || typeof e.detail.result === 'undefined'
      || e.detail.placeholder !== 'product-recommendation'
    ) {
      return;
    }

    let product = e.detail.result;
    product.media = {};
    let mediaData = {};

    product.variants.forEach(function eachVariant(variant) {
      variant.product.media = [];

      try {
        mediaData = JSON.parse(variant.product.assets_pdp);
        mediaData.forEach(function setGalleryMedia(media) {
          variant.product.media.push({
            gallery: media.styles.product_zoom_medium_606x504,
            zoom: media.styles.product_zoom_large_800x800,
            thumbnails: media.styles.product_teaser,
          });
        });
      }
      catch (e) {
        // Do nothing.
      }
    });
  }, 1);
})();
