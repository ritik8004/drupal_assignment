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
      variant.product.media_teaser = '';

      try {
        if (Drupal.hasValue(variant.product.assets_pdp)) {
          mediaData = JSON.parse(variant.product.assets_pdp);
          mediaData.forEach(function setGalleryMedia(media) {
            variant.product.media.push({
              // @todo Add type of asset when dealing with video etc.
              url: media.url,
              medium: media.styles.product_zoom_medium_606x504,
              zoom: media.styles.product_zoom_large_800x800,
              // @todo Find out actual thumbnail key.
              thumbnails: media.styles.product_teaser,
              teaser: media.styles.product_teaser,
            });
          });
        }
      }
      catch (e) {
        console.log('Exception occurred while parsing variant product assets for sku ' + variant.product.sku + ': ' + e.message);
      }

      try {
        variant.product.media_cart = null;
        if (Drupal.hasValue(variant.product.assets_cart)) {
          mediaData = JSON.parse(variant.product.assets_cart);
          variant.product.media_cart = mediaData.find(function setCartMedia(media) {
            return media.styles.cart_thumbnail;
          });
        }
      }
      catch (e) {
        console.log('Exception occurred while parsing variant product assets for sku ' + variant.product.sku + ': ' + e.message);
      }
    });

    const productRecommendations = ['upsell_products', 'related_products', 'crosssell_products'];
    productRecommendations.forEach(function eachRecommendationType(type) {
      if (Drupal.hasValue(product[type])) {
        product[type][0].variants.forEach(function setRecommendedProductImage(variant) {
          variant.product.media_teaser = null;
          try {
            mediaData = JSON.parse(variant.product.assets_teaser);
            mediaData.every(function setTeaserMedia(media) {
              variant.product.media_teaser = media.styles.product_teaser;
              // We do this so that we are able to detect in getSkuForGallery
              // that the variant has media.
              variant.product.media = variant.product.media_teaser;
              // Break as there is only 1 teaser image expected.
              return false;
            });
          }
          catch (e) {
            console.log('Exception occurred while parsing ' + type + ' product assets for sku ' + variant.product.sku + ': ' + e.message);
          }
        });
      }
    });
  }, 1);
})();
