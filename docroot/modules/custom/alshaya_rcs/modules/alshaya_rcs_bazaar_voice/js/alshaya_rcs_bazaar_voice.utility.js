(function (drupalSettings, Drupal) {
    // Initialize the global object.
  window.alshayaBazaarVoice = window.alshayaBazaarVoice || {};

  var staticStorage = {};

  /**
   * Returns a review for the user for the current/mentioned product.
   *
   * (optional) @param {string} productIdentifier
   *   The sku value for the product.
   *
   * @returns {Object}
   *   The product review data.
   */
  window.alshayaBazaarVoice.getProductReviewForCurrrentUser = async function getProductReviewForCurrrentUser(productIdentifier) {
    const bazaarVoiceSettings = window.alshayaBazaarVoice.getbazaarVoiceSettings(productIdentifier);
    const productId = typeof productIdentifier !== 'undefined' ? productIdentifier : bazaarVoiceSettings.productid;
    const userId = bazaarVoiceSettings.reviews.customer_id;
    const staticStorageKey = `${userId}_${productId}`;
    let productReviewData = Drupal.hasValue(staticStorage[staticStorageKey])
      ? staticStorage[staticStorageKey]
      : null;

    if (productReviewData instanceof Promise) {
      return productReviewData;
    }
    if (productReviewData) {
      return JSON.parse(productReviewData);
    }
    if (productReviewData === 0) {
      return null;
    }

    // Get review data from BazaarVoice based on available parameters.
    const apiUri = '/data/reviews.json';
    const params = `&include=Authors,Products&filter=authorid:${userId}&filter=productid:${productId}&stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}`;
    const response = window.alshayaBazaarVoice.fetchAPIData(apiUri, params).then((result) => {
      if (!Drupal.hasValue(result.error) && Drupal.hasValue(result.data)) {
        if (result.data.Results.length > 0) {
          const products = result.data.Results;
          Object.keys(products).forEach((i) => {
            if (products[i].ProductId === productId) {
              productReviewData = {
                review_data: products[i],
                user_rating: products[i].Rating,
              };
            }
          });
        }
      }
      // In case there are no reviews, store 0 instead of NULL in order to
      // differentiate between empty storage and 0 reviews.
      // When we fetch from static storage later, we convert 0 back to NULL and
      // return.
      const staticData = !productReviewData ? 0 : JSON.stringify(productReviewData);
      // Store the value statically so that it can be reused.
      staticStorage[staticStorageKey] = staticData;
      // Return the product review data.
      return productReviewData;
    });

    // As ratings and review-summary components are calling this function at
    // around the same time, we store the promise "result" into the static key the
    // first time this function is called. The 2nd time this function is called,
    // if the promise is not resolved, we return the same promise above so that
    // when it gets resolved both the calling functions are able to use the same
    // result instead of making multiple network requests for the same data.
    staticStorage[staticStorageKey] = response;
    return response;
  }

  /**
   * Gets bazaar voice settings.
   *
   * (optional) @param {string} productId
   * Product SKU value.
   *
   * @returns {Object}
   *   Bazaar voice settings.
   */
  window.alshayaBazaarVoice.getbazaarVoiceSettings = function getbazaarVoiceSettings(productId) {
    var settings = {};
    if (Drupal.hasValue(productId)) {
      var product = Drupal.alshayaSpc.getProductDataV2Synchronous(productId);
      settings = {
        product: {
          url: product.url,
          title: product.title,
          // @todo Image will be same as alshaya_acm_get_product_display_image().
          image_url: product.image,
        }
      };
    }

    settings = Object.assign(settings, drupalSettings.alshaya_bazaar_voice);

    return {
      productId: productId,
      reviews: settings,
    };
  }
})(drupalSettings, Drupal);
