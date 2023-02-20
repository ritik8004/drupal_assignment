(function (drupalSettings, Drupal) {
    // Initialize the global object.
  window.alshayaBazaarVoice = window.alshayaBazaarVoice || {};

  var staticStorage = {
    bvSettings: {},
  };

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
    const bazaarVoiceSettings = await window.alshayaBazaarVoice.getbazaarVoiceSettings(productIdentifier);
    if (!Drupal.hasValue(bazaarVoiceSettings)) {
      return;
    }
    const productId = productIdentifier || bazaarVoiceSettings.productid;
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
   * Process raw BV product object to required format.
   *
   * @param {Object} product
   *   BV product object.
   * @returns {Object}
   *   Process product.
   */
  function processProduct(product) {
    window.commerceBackend.setMediaData(product);

    return {
      url: Drupal.url(product.url_key + '.html'),
      title: product.name,
      image_url: window.commerceBackend.getTeaserImage(product),
    }
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
  window.alshayaBazaarVoice.getbazaarVoiceSettings = async function getbazaarVoiceSettings(productId) {
    var settings = {};
    var product_id = '';
    if (Drupal.hasValue(staticStorage.bvSettings[productId])) {
      return staticStorage.bvSettings[productId];
    }

    if (!Drupal.hasValue(productId)) {
      var productInfo = window.commerceBackend.getProductData(null, 'productInfo');
      Object.entries(productInfo).forEach(([key]) => {
        product_id = key;
      });
    }

    product_id = (typeof productId === 'undefined') ? product_id : productId;

    if (Drupal.hasValue(product_id)) {
      var response = globalThis.rcsPhCommerceBackend.getDataSynchronous('bv_product', {sku: product_id});
      if (response.data.products.total_count) {
        try {
          var product = response.data.products.items[0];
          // Clone the product object.
          product = JSON.parse(JSON.stringify(product));
          product = processProduct(product);

          settings = {
            product,
          };
        } catch(e) {
          Drupal.alshayaLogger('warning', 'Could not parse BV settings for SKU @sku', {
            '@sku': product_id,
          });
        }
      }
    }

    // Merge product and bazaar voice settings.
    settings = drupalSettings.alshaya_bazaar_voice ? Object.assign(settings, drupalSettings.alshaya_bazaar_voice) : Object.assign(settings, drupalSettings.userInfo);

    // Call commerceBackend for Bazaar voice settings.
    const bazaarVoiceConfig = await window.alshayaBazaarVoice.getBazaarVoiceSettingsFromCommerceBackend();

    if (bazaarVoiceConfig === null) {
      return bazaarVoiceConfig;
    }

    if (Drupal.hasValue(bazaarVoiceConfig.basic)) {
      // Add basic configurations from MDC response.
      Object.assign(
        settings.bazaar_voice,
        bazaarVoiceConfig.basic,
      );
    }

    settings.bazaar_voice.error_messages = {};
    settings.bazaar_voice.sorting_options = {};
    settings.bazaar_voice.filter_options = {};

    if (Drupal.hasValue(bazaarVoiceConfig.bv_error_messages)) {
      // Add error messages configurations from MDC response.
      Object.assign(
        settings.bazaar_voice.error_messages,
        bazaarVoiceConfig.bv_error_messages,
      );
    }

    if (Drupal.hasValue(bazaarVoiceConfig.sorting_options)) {
      // Add sorting options configurations from MDC response.
      Object.assign(
        settings.bazaar_voice.sorting_options,
        bazaarVoiceConfig.sorting_options,
      );
    }

    if (Drupal.hasValue(bazaarVoiceConfig.pdp_filter_options)) {
      // Add filter options configurations from MDC response.
      Object.assign(
        settings.bazaar_voice.filter_options,
        bazaarVoiceConfig.pdp_filter_options,
      );
    }

    staticStorage.bvSettings[product_id] = {
      productid: product_id,
      reviews: settings,
    };

    // Return Bazaar voice config from commerceBackend with product Id
    // using static variable.
    return staticStorage.bvSettings[product_id];
  };

  window.alshayaBazaarVoice.getUserBazaarVoiceSettings = async function getUserBazaarSettings() {
    const settings = [];
    if (drupalSettings.userInfo) {
      settings.reviews = drupalSettings.userInfo;
    }

    const bazaarVoiceCommonSettings = await window.alshayaBazaarVoice.getbazaarVoiceSettings();

    Object.assign(settings.reviews, bazaarVoiceCommonSettings.reviews);

    return settings;
  };
})(drupalSettings, Drupal);
