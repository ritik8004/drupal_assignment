window.commerceBackend = window.commerceBackend || {};

(function onlineReturnsUtilsV2(Drupal, drupalSettings) {
  /**
   * Utility function to get order details for return pages.
   *
   * @returns {Promise}
   *   Promise which resolves to order details.
   */
  window.commerceBackend.getOrderDetails = function getOrderDetails() {
    return new Promise((resolve) => {
      let orderDetails = {};
      if (Drupal.hasValue(drupalSettings.onlineReturns)
        && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo)
        && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo.orderInfo)) {
        orderDetails = drupalSettings.onlineReturns.returnInfo.orderInfo;
      } else if (Drupal.hasValue(drupalSettings.order)
        && Drupal.hasValue(drupalSettings.order.order_details)) {
        orderDetails['#order_details'] = drupalSettings.order.order_details;
      }

      return resolve(orderDetails);
    });
  };

  /**
   * Get the order gtm info.
   *
   * @returns {Object}
   *   Order GTM info.
   */
  window.commerceBackend.getOrderGtmInfo = function getOrderGtmInfo() {
    if (Drupal.hasValue(drupalSettings.onlineReturns)
      && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo)
      && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo.orderInfo)
      && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo.orderInfo['#gtm_info'])) {
      return drupalSettings.onlineReturns.returnInfo.orderInfo['#gtm_info'];
    }

    // For order detail page, get the data from online returns drupal settings.
    if (Drupal.hasValue(drupalSettings.onlineReturns)
      && Drupal.hasValue(drupalSettings.onlineReturns.gtm_info)) {
      return drupalSettings.onlineReturns.gtm_info;
    }

    return {};
  };

  /**
   * Set returns product data in order data.
   *
   * Do nothing for V2, this is used in V3.
   *
   * @param {Object} orderProducts
   *   Array of products in the order.
   */
  window.commerceBackend.setReturnsProductData = function setReturnsProductData() {
  };
}(Drupal, drupalSettings));
