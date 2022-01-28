/**
 * Listens to the 'rcsUpdateResults' event and updates the result object
 * with media data.
 */
 (function () {
  /**
   * Sets media data to the passed product object.
   *
   * @param {object} product
   *   The raw product object.
   */
  function setMediaData(product) {
    let mediaData = {};
    product.media = [];

    if (product.type_id === configurable) {
      product.variants.forEach(function eachVariant(variant) {
        variant.product.media = [];
        variant.product.media_teaser = '';

        try {
          if (Drupal.hasValue(variant.product.media_gallery)) {
            variant.product.media_gallery.forEach(function setGalleryMedia(media) {
              variant.product.media.push({
                url: media.url,
              });
            });
          }
        }
        catch (e) {
          Drupal.alshayaLogger('error', 'Exception occurred while parsing variant product assets for sku @sku : @message', {
            '@sku': variant.product.sku,
            '@message': e.message
          });
        }

        try {
          variant.product.media_cart = null;
          if (Drupal.hasValue(variant.product.assets_cart)) {
            mediaData = JSON.parse(variant.product.assets_cart);
            variant.product.media_cart = mediaData[0].styles.cart_thumbnail;
          }
        }
        catch (e) {
          Drupal.alshayaLogger('error', 'Exception occurred while parsing cart product assets for sku @sku: @message', {
            '@sku': variant.product.sku,
            '@message': e.message,
          });
        }

        try {
          variant.product.media_teaser = null;
          if (Drupal.hasValue(variant.product.assets_teaser)) {
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
        }
        catch (e) {
          Drupal.alshayaLogger('error', 'Exception occurred while parsing teaser product assets for sku @sku: @message', {
            '@sku': variant.product.sku,
            '@message': e.message,
          });
        }
      });
    }

    const productRecommendations = ['upsell_products', 'related_products', 'crosssell_products'];
    productRecommendations.forEach(function eachRecommendationType(type) {
      if (Drupal.hasValue(product[type])) {
        product[type].forEach(function (recommendedProduct) {
          recommendedProduct.variants.forEach(function setRecommendedProductImage(variant) {
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
              Drupal.alshayaLogger('error', 'Exception occurred while parsing @type product assets for sku @sku: @message', {
                '@type': type,
                '@sku': variant.product.sku,
                '@message': e.message,
              });
            }
          });
        });
      }
    });
  }

  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    console.log('THIS FILE IS CALLED');
    return;
    // We do not want further processing:
    // 1. If page is not a product page
    // 2. If the result object in the event data is undefined
    // 3. If product recommendation replacement or magazine carousel replacement
    // has not called this handler.
    if ((typeof e.detail.pageType !== 'undefined' && e.detail.pageType !== 'product')
      || typeof e.detail.result === 'undefined'
      || (typeof e.detail.placeholder !=='undefined'
           &&  !(['product-recommendation', 'field_magazine_shop_the_story'].includes(e.detail.placeholder))
         )
      ) {
      return;
    }

    let products = e.detail.result;

    // Check if it is an array of products, for eg. for magazine article
    // carousel we get an array of products here.
    if (Array.isArray(products)) {
      products.forEach(function (product) {setMediaData(product)});
    }
    else {
      setMediaData(products);
    }
  }, 10);
})();
