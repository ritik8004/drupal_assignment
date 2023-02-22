(function (Drupal, drupalSettings) {
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
      Object.entries(productInfo).forEach(([key]) => {
        settings.productid = key;
        settings.reviews = productInfo[key].alshaya_bazaar_voice;
      });
    }

    return settings;
  };

  /**
   * Get user bazaar voice settings.
   *
   * @returns {*[]}
   *   Bazaar voice settings for user.
   */
  window.alshayaBazaarVoice.getUserBazaarVoiceSettings = function getUserBazaarSettings() {
    var settings = [];
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
  window.commerceBackend.getWriteReviewFieldsConfigs = function () {
    return jQuery.ajax({
      url: Drupal.url('get-write-review-fields-configs'),
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        const event = new CustomEvent('showMessage', {
          bubbles: true,
          detail: { data: response },
        });
        document.dispatchEvent(event);
      },
      error: function (error) {
        const event = new CustomEvent('showMessage', {
          bubbles: true,
          detail: { data: error },
        });
        document.dispatchEvent(event);
      }
    });
  }

})(Drupal, drupalSettings);
