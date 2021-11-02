/**
 * Listens to the 'alshayaRcsUpdateResults' event and update the result object.
 */
(function () {
  RcsEventManager.addListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if ((typeof e.detail.pageType !== 'undefined' && e.detail.pageType !== 'product')
      || typeof e.detail.result === 'undefined'
      || (typeof e.detail.placeholder !=='undefined' &&  e.detail.placeholder !== 'product-recommendation')
      ) {
      return;
    }

    let mediaData = {};
    let product = e.detail.result;
    product.media = [];

    product.variants.forEach(function eachVariant(variant) {
      variant.product.media = [];

      try {
        mediaData = JSON.parse(variant.product.assets_pdp);
        mediaData.forEach(function setGalleryMedia(media) {
          variant.product.media.push({
            // @todo Add type of asset.
            url: media.url,
            medium: media.styles.product_zoom_medium_606x504,
            zoom: media.styles.product_zoom_large_800x800,
            // @todo Find out actual thumbnail key.
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
