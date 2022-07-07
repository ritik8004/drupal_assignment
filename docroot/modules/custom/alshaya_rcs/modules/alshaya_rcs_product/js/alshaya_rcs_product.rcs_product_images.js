/**
 * Listens to the 'rcsUpdateResults' event and updates the result object
 * with assets data.
 */
 (function () {

  /**
   * Processes and returns an object containing media for given sku.
   *
   * @param {object} media
   *   Product media field object.
   * @param {string} sku
   *   SKU value.
   *
   * @returns {object}
   *   Processed product media object.
   */
  function getProcessedProductMedia(media, sku) {
    var processedMedia = {};
    try {
      var productMediaStyles = JSON.parse(media.styles);
      processedMedia = {
        // @todo Add type of asset when dealing with video etc.
        url: media.url,
        medium: productMediaStyles.product_zoom_medium_606x504,
        zoom: productMediaStyles.product_zoom_large_800x800,
        thumbnails: productMediaStyles.pdp_gallery_thumbnail,
        teaser: productMediaStyles.product_teaser,
      }
    } catch (e) {
      Drupal.alshayaLogger('error', 'Error occurred while parsing media for sku @sku : @message', {
        '@sku': sku,
        '@message': e.message,
      });
    }
    return processedMedia;
  }

  /**
   * Sets the simple product media data in the main product object.
   *
   * @param {Object} product
   *   Product object.
   */
  function setProductMediaSimple(product) {
    // Temporary store for media data.
    var mediaData = {};
    product.hasMedia = false;
    if (Drupal.hasValue(product.media_gallery)) {
      // We do this so that we are able to detect in getSkuForGallery
      // that the entity has media.
      product.hasMedia = true;
      mediaData = product.media_gallery;
      mediaData.forEach(function setGalleryMedia(media) {
        var productMedia = getProcessedProductMedia(media, product.sku);
        if (Drupal.hasValue(productMedia)) {
          product.media.push(productMedia);
        }
      });
      if (Drupal.hasValue(product.media[0])) {
        product.media_cart = product.media[0].thumbnails;
        product.media_teaser = product.media[0].teaser;
      }
    }
  }

  /**
   * Sets the configurable product media data in the main product object.
   *
   * @param {Object} product
   *   Product object.
   */
  function setProductMediaConfigurable(product) {
    // Temporary store for media data.
    var mediaData = {};
    product.hasMedia = false;
    if (Drupal.hasValue(product.media_gallery)) {
      // We do this so that we are able to detect in getSkuForGallery
      // that the variant has media.
      product.hasMedia = true;
      mediaData = product.media_gallery;
      mediaData.forEach(function setGalleryMedia(media) {
        var productMedia = getProcessedProductMedia(media, product.sku);
        if (Drupal.hasValue(productMedia)) {
          product.media.push(productMedia);
        }
      });
    }

    product.variants.forEach(function eachVariant(variant) {
      variant.product.media = [];
      variant.product.media_teaser = '';
      // We do this so that we are able to detect in getSkuForGallery
      // that the variant has media.
      variant.product.hasMedia = false;
      var variantHasMedia = Drupal.hasValue(variant.product.media_gallery);

      try {
        if (variantHasMedia) {
        variant.product.hasMedia = true;
        mediaData = variant.product.media_gallery;
          mediaData.forEach(function setGalleryMedia(media) {
            var productMedia = getProcessedProductMedia(media, variant.product.sku);
            if (Drupal.hasValue(productMedia)) {
              variant.product.media.push(productMedia);
            }
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
        if (variantHasMedia) {
          var variantMediaStyles = JSON.parse(variant.product.media_gallery[0].styles);
          variant.product.media_cart = variantMediaStyles.cart_thumbnail;
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
        if (variantHasMedia) {
          mediaData = variant.product.media_gallery;
          mediaData.every(function setTeaserMedia(media) {
            var variantMediaStyles = JSON.parse(media.styles);
            variant.product.media_teaser = variantMediaStyles.product_teaser;
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
    recommendedProduct.variants.forEach(function setRecommendedProductMedia(variant) {
      variant.product.media_teaser = null;
      try {
        var mediaData = JSON.parse(variant.product.assets_teaser);
        mediaData.every(function setTeaserMedia(media) {
          variant.product.media_teaser = media.styles.product_teaser;
          // We do this so that we are able to detect in getSkuForGallery
          // that the variant has media.
          variant.product.hasMedia = true;
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
    else {
      setProductMediaSimple(product);
    }

    setProductRecommendationsMedia(product);
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
           && !([
              'product-recommendation',
              'field_magazine_shop_the_story',
              'product_by_sku',
            ].includes(e.detail.placeholder))
         )
      ) {
      return;
    }

    var products = e.detail.result;

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
