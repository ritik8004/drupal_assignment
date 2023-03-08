(function (jQuery, drupalSettings, Drupal) {
    // Initialize the global object.
  window.alshayaBazaarVoice = window.alshayaBazaarVoice || {};

  var staticStorage = {
    bvSettings: {},
    writeReviewFormHiddenFields: [],
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
    var hide_fields_write_review = [];

    product.categories.forEach(function eachCategory(category) {
      if (Drupal.hasValue(category.rating_review)) {
        var hidden_fields_for_category = category.rating_review.split(',');
        if (Drupal.hasValue(hidden_fields_for_category)) {
          hidden_fields_for_category.forEach(function eachCat(hiddenCat) {
            if (!hide_fields_write_review.includes(hiddenCat)) {
              hide_fields_write_review.push(hiddenCat);
            }
          });
        }
      }
    });

    return {
      url: Drupal.url(product.url_key + '.html'),
      title: product.name,
      image_url: window.commerceBackend.getTeaserImage(product),
      hide_fields_write_review: hide_fields_write_review,
    }
  }

  /**
   * Gets bazaar voice settings.
   *
   * @param {string} productIdParam
   *   Product SKU value.
   *
   * @returns {Object|null}
   *   Bazaar voice settings object or null.
   */
  window.alshayaBazaarVoice.getbazaarVoiceSettings = async function getbazaarVoiceSettings(productIdParam) {
    var settings = {};
    var productId = productIdParam || '';
    if (Drupal.hasValue(staticStorage.bvSettings[productId])) {
      return staticStorage.bvSettings[productId];
    }

    // Get the first product id from the product info.
    if (!Drupal.hasValue(productId)) {
      var productInfo = window.commerceBackend.getProductData(null, 'productInfo');
      var ids = Object.keys(productInfo);
      productId = Drupal.hasValue(ids) ? ids[0] : productId;
    }

    if (Drupal.hasValue(productId)) {
      // Get bv product details with field assets_teaser.
      var response = globalThis.rcsPhCommerceBackend.getDataSynchronous('bv_product', {sku: productId});
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
            '@sku': productId,
          });
        }
      }
    }

    // For anonymous user merge common settings in alshaya_bazaar_voice key
    // and for authenticated user merge common settings in userInfo key
    // when user is viewing reviews page in my account.
    settings = drupalSettings.alshaya_bazaar_voice
      ? Object.assign(settings, drupalSettings.alshaya_bazaar_voice)
      : Object.assign(settings, drupalSettings.userInfo);

    // Call commerceBackend for Bazaar voice configurations.
    var bazaarVoiceConfig = await window.alshayaBazaarVoice.getBazaarVoiceSettingsFromCommerceBackend();
    // If ratings_reviews is set to 1, we do not show the write review form as
    // the same logic is implemented in the back-end.
    if (Drupal.hasValue(bazaarVoiceConfig.basic.pdp_rating_reviews)) {
      return null;
    }

    if (Drupal.hasValue(product)) {
      bazaarVoiceConfig.basic.hide_fields_write_review = settings.product.hide_fields_write_review;
    }

    if (bazaarVoiceConfig === null) {
      return null;
    }

    Object.entries(bazaarVoiceConfig).forEach((item) => {
      if (item[0] === 'basic') {
        // Merge drupalSettings and basic configurations from commerceBackend.
        Object.assign(settings.bazaar_voice, item[1]);
      } else {
        // Add other common configurations from commerceBackend.
        settings.bazaar_voice[item[0]] = item[1];
      }
    });

    staticStorage.bvSettings[productId] = {
      productid: productId,
      reviews: settings,
    };

    // Return Bazaar voice config from commerceBackend with product Id
    // using static variable.
    return staticStorage.bvSettings[productId];
  };

  /**
   * Get user bazaar voice settings from drupalSettings and commerceBackend.
   *
   * @returns {Promise<*[]>}
   *   Bazaar voice user settings.
   */
  window.alshayaBazaarVoice.getUserBazaarVoiceSettings = async function getUserBazaarSettings() {
    var settings = {};
    if (drupalSettings.userInfo) {
      settings.reviews = drupalSettings.userInfo;
    }

    var bazaarVoiceCommonSettings = await window.alshayaBazaarVoice.getbazaarVoiceSettings();

    Object.assign(settings.reviews, bazaarVoiceCommonSettings.reviews);

    return settings;
  };

  /**
   * Gets the write review form configurations.
   *
   * @returns {Promise}
   *   Promise of the response object of the API call to fetch form configs.
   */
  window.commerceBackend.getWriteReviewFieldsConfigs = function getWriteReviewFieldsConfigs(productId) {
    return jQuery.ajax({
      url: drupalSettings.cart.url + '/V1/bv/config/write-review/' + productId,
      type: 'GET',
      dataType: 'json',
    }).then(function getData(response, status, xhr) {
      staticStorage.writeReviewFormHiddenFields = response[0].hide_fields_write_review;
      response = response[0].write_review_form;
      var data = {
        data: Object.values(response),
        status: xhr.status,
      };
      var event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: { data },
      });
      document.dispatchEvent(event);
      return data;
    }).catch(function (error) {
      var event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: { data: error },
      });
      document.dispatchEvent(event);
    });
  }

  /**
   * Get the names of field
      document.dispatchEvent(event);s to hide in write review form.
   *
   * @param {string} productId
   *   Product sku value.
   *
   * @returns {Array}
   *   Array of hidden fields.
   */
  window.commerceBackend.getHiddenWriteReviewFields = function getHiddenWriteReviewFields(productId) {
    return staticStorage.writeReviewFormHiddenFields;
  }
})(jQuery, drupalSettings, Drupal);
