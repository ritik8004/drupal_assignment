window.commerceBackend = window.commerceBackend || {};

(function (Drupal) {

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

    product.media = [];

    // We do this so that we are able to detect in getSkuForGallery
    // that the variant has media.
    product.hasMedia = false;

    var productHasMedia = Drupal.hasValue(product.media_gallery);

    try {
      product.media_teaser = null;
      if (productHasMedia) {
        product.hasMedia = true;
        mediaData = product.media_gallery;
        mediaData.forEach(function setGalleryMedia(media) {
          var productMedia = getProcessedProductMedia(media, product.sku);
          if (Drupal.hasValue(productMedia)) {
            product.media.push(productMedia);
          }
        });
      }
    }
    catch (e) {
      Drupal.alshayaLogger('error', 'Exception occurred while parsing variant product assets for sku @sku : @message', {
        '@sku': product.sku,
        '@message': e.message
      });
    }

    try {
      product.media_cart = null;
      if (productHasMedia) {
        var variantMediaStyles = JSON.parse(product.media_gallery[0].styles);
        product.media_cart = variantMediaStyles.cart_thumbnail;
      }
    }
    catch (e) {
      Drupal.alshayaLogger('error', 'Exception occurred while parsing cart product assets for sku @sku: @message', {
        '@sku': product.sku,
        '@message': e.message,
      });
    }

    try {
      product.media_teaser = null;
      if (productHasMedia) {
        mediaData = product.media_gallery;
        mediaData.every(function setTeaserMedia(media) {
          var variantMediaStyles = JSON.parse(media.styles);
          product.media_teaser = variantMediaStyles.product_teaser;
          // Break as there is only 1 teaser image expected.
          return false;
        });
      }
    }
    catch (e) {
      Drupal.alshayaLogger('error', 'Exception occurred while parsing teaser product assets for sku @sku: @message', {
        '@sku': product.sku,
        '@message': e.message,
      });
    }
  }

  /**
   * Sets the configurable product media data in the main product object.
   *
   * @param {Object} product
   *   Product object.
   */
  function setProductMediaConfigurable(product) {
    product.hasMedia = false;
    if (Drupal.hasValue(product.media_gallery)) {
      // We do this so that we are able to detect in getSkuForGallery
      // that the variant has media.
      product.hasMedia = true;
      product.media_gallery.forEach(function setGalleryMedia(media) {
        var productMedia = getProcessedProductMedia(media, product.sku);
        if (Drupal.hasValue(productMedia)) {
          product.media.push(productMedia);
        }
      });
    }

    product.variants.forEach(function eachVariant(variant) {
      setProductMediaSimple(variant.product);
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
    product.type_id === 'configurable'
      ? setProductMediaConfigurable(product)
      : setProductMediaSimple(product);
  }

   /**
    * Sets media data values in provided product object.
    *
    * @param {object|array} products
    *   The products to set the media data for.
    */
   window.commerceBackend.setMediaData = function setProductMediaData(products) {
     var productArray = Drupal.hasValue(products.type_id)
       ? [products]
       : products;

     Object.values(productArray).forEach(function (product) {setMediaData(product)});
   }

})(Drupal);
