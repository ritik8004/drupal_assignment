(function (jQuery, Drupal, drupalSettings) {
  // Initialize the global object.
  window.alshayaBazaarVoice = window.alshayaBazaarVoice || {};

  /**
   * Returns a review for the user for the current/mentioned product.
   *
   * (optional) @param {string} productIdentifier
   *   The sku value for the product.
   *
   * @returns {Object}
   *   The product review data.
   */
  window.alshayaBazaarVoice.getProductReviewForCurrrentUser = async function getProductReviewForCurrrentUser(productId) {
    if (productId !== '' && typeof drupalSettings.bazaarvoiceUserDetails !== 'undefined') {
      if (typeof productId !== 'undefined' && Object.keys(drupalSettings.productInfo[productId]).length > 0) {
        return drupalSettings.productInfo[productId].productReview;
      }
      if (typeof drupalSettings.bazaarvoiceUserDetails.productReview !== undefined) {
        return drupalSettings.bazaarvoiceUserDetails.productReview;
      }
    }
    return null;
  };

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
    var productInfo = window.commerceBackend.getProductData(productId);
    var settings = {};

    if (typeof productId !== 'undefined' && productInfo !== null) {
      settings.productid = productId;
      settings.reviews = productInfo.alshaya_bazaar_voice;
    } else {
      productInfo = window.commerceBackend.getProductData(null, 'productInfo');
      Object.entries(productInfo).forEach(function eachInfo([key]) {
        settings.productid = key;
        settings.reviews = productInfo[key].alshaya_bazaar_voice;
      });
    }

    return settings;
  };

  /**
   * Get user bazaar voice settings.
   *
   * @returns {Object}
   *   Bazaar voice settings for user.
   */
  window.alshayaBazaarVoice.getUserBazaarVoiceSettings = function getUserBazaarSettings() {
    var settings = {};
    if (drupalSettings.userInfo) {
      settings.reviews = drupalSettings.userInfo;
    }
    return settings;
  };

  /**
   * Gets the write review form configurations.
   *
   * @returns {Promise}
   *   Promise of the response object of the API call to fetch form configs.
   */
  window.commerceBackend.getWriteReviewFieldsConfigs = function getWriteReviewFieldsConfigs() {
    return jQuery.ajax({
      url: Drupal.url('get-write-review-fields-configs'),
      type: 'GET',
      dataType: 'json',
    }).then(function getData(response, status, xhr) {
      var data = {
        data: response,
        status: xhr.status,
      };
      var event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data
        }
      });
      document.dispatchEvent(event);
      return data;
    }).catch(function err(error) {
        var event = new CustomEvent('showMessage', {
          bubbles: true,
          detail: { data: error },
        });
        document.dispatchEvent(event);
      });
  }

  /**
   * Get the names of fields to hide in write review form.
   *
   * @param {string} productId
   *   Product sku value.
   *
   * @returns {Array}
   *   Array of hidden fields.
   */
  window.commerceBackend.getHiddenWriteReviewFields = function getHiddenWriteReviewFields(productId) {
    var bazaarVoiceSettings = window.alshayaBazaarVoice.getbazaarVoiceSettings(productId);
    return bazaarVoiceSettings.reviews.hide_fields_write_review;
  }
})(jQuery, Drupal, drupalSettings);
