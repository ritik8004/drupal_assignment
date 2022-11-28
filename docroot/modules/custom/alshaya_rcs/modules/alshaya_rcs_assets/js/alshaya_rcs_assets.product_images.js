window.commerceBackend = window.commerceBackend || {};

(function (Drupal) {
  /**
   * Sets the configurable product media data in the main product object.
   *
   * @param {Object} product
   *   Product object.
   */
  function setProductMediaConfigurable(product) {
    product.variants.forEach(function eachVariant(variant) {
      setProductMediaSimple(variant);
    });
  }

  /**
   * Sets product media data for simple product.
   *
   * @param {Object} product
   *   Product object.
   */
  function setProductMediaSimple(variant) {
    if (Drupal.hasValue(variant.product)) {
      variant.product.media = [];
      variant.product.media_teaser = '';
      variant.product.hasMedia = false;

      try {
        if (Drupal.hasValue(variant.product.assets_pdp)) {
          variant.product.hasMedia = true;
          mediaData = JSON.parse(variant.product.assets_pdp);
          mediaData.forEach(function setGalleryMedia(media) {
            if (media.image_type !== "MovingMedia") {
              variant.product.media.push({
                type: 'image',
                url: media.url,
                medium: media.styles.product_zoom_medium_606x504,
                zoom: media.styles.product_zoom_large_800x800,
                thumbnails: media.styles.pdp_gallery_thumbnail,
                teaser: media.styles.product_teaser,
              });
            }
            else {
              variant.product.media.push({
                type: 'video',
                url: media.url,
              });

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
      }
      catch (e) {
        Drupal.alshayaLogger('error', 'Exception occurred while parsing teaser product assets for sku @sku: @message', {
          '@sku': variant.product.sku,
          '@message': e.message,
        });
      }
    }
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
