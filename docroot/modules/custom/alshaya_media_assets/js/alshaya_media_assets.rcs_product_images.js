window.commerceBackend = window.commerceBackend || {};

/**
 * Listens to the 'rcsUpdateResults' event and updates the result object
 * with media data.
 */
(function () {
  /**
   * Sets the configurable product media data in the main product object.
   *
   * @param {Object} product
   *   Product object.
   */
  function setProductMediaConfigurable(product) {
    // Temporary store for media data.
    let mediaData = {};
    product.variants.forEach(function eachVariant(variant) {
      variant.product.media = [];
      variant.product.media_teaser = '';
      variant.product.hasMedia = false;

      try {
        if (Drupal.hasValue(variant.product.assets_pdp)) {
          variant.product.hasMedia = true;
          mediaData = JSON.parse(variant.product.assets_pdp);
          mediaData.forEach(function setGalleryMedia(media) {
            variant.product.media.push({
              // @todo Add type of asset when dealing with video etc.
              url: media.url,
              medium: media.styles.product_zoom_medium_606x504,
              zoom: media.styles.product_zoom_large_800x800,
              thumbnails: media.styles.pdp_gallery_thumbnail,
              teaser: media.styles.product_teaser,
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

  /**
   * Sets media data into the configurable recommended product object.
   *
   * @param {Object} recommendedProduct
   *   Recommended product object.
   */
  function setProductRecommendationsMediaConfigurable(recommendedProduct) {
    if (Array.isArray(recommendedProduct.variants) && recommendedProduct.variants.length !== 0) {
      recommendedProduct.variants.forEach(function setRecommendedProductImage(variant) {
        variant.product.media_teaser = null;
        try {
          var mediaData = JSON.parse(variant.product.assets_teaser);
          if (Drupal.hasValue(mediaData)) {
            variant.product.hasMedia = true;
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
          Drupal.alshayaLogger('error', 'Exception occurred while parsing @type product assets for sku @sku: @message', {
            '@type': type,
            '@sku': variant.product.sku,
            '@message': e.message,
          });
        }
      });
    }
  }

  /**
   * Sets product recommendations media data into the main product object.
   *
   * @param {Object} product
   *   Product object.
   */
  function setProductRecommendationsMedia(product) {
    const productRecommendations = ['upsell_products', 'related_products', 'crosssell_products'];
    productRecommendations.forEach(function eachRecommendationType(type) {
      if (Drupal.hasValue(product[type])) {
        product[type].forEach(function (recommendedProduct) {
          if (recommendedProduct.type_id === 'configurable') {
            setProductRecommendationsMediaConfigurable(recommendedProduct);
          }
        });
      }
    });
  }

  /**
   * Sets media data to the passed product object.
   *
   * @param {object} product
   *   The raw product object.
   */
  function setMediaData(product) {
    product.media = [];
    if (product.type_id === 'configurable') {
      setProductMediaConfigurable(product);
    }

    setProductRecommendationsMedia(product);
  }

  /**
   * Sets media data values in provided product object.
   *
   * @param {object} products
   *   The products to set the media data for.
   */
  window.commerceBackend.setMediaData = function setProductMediaData(products) {
    // Check if it is an array of products, for eg. for magazine article
    // carousel we get an array of products here.
    if (Array.isArray(products)) {
      products.forEach(function (product) {setMediaData(product)});
    }
    else {
      setMediaData(products);
    }
  }

  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // We do not want further processing:
    // 1. If page is not a product page
    // 2. If the result object in the event data is undefined
    // 3. If product recommendation replacement or magazine carousel replacement
    // has not called this handler.
    if ((typeof e.detail.pageType !== 'undefined' && e.detail.pageType !== 'product')
      || typeof e.detail.result === 'undefined'
      || (typeof e.detail.placeholder !=='undefined'
           &&  !([
              'product_by_sku',
              'product-recommendation',
              'crosssel-products',
              'upsell-products',
              'related-products',
              'field_magazine_shop_the_story',
            ].includes(e.detail.placeholder)))
      ) {
      return;
    }

    window.commerceBackend.setMediaData(e.detail.result);
  }, 10);
})();
