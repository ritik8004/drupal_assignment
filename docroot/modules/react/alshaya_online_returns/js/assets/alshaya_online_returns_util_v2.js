(function onlineReturnsUtilsV2(Drupal, drupalSettings) {
  /**
   * Utility function to get order details for return pages.
   */
  window.commerceBackend.getOrderDetails = function getOrderDetails() {
    let orderDetails = {};
    if (Drupal.hasValue(drupalSettings.onlineReturns)
      && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo)
      && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo.orderInfo)) {
      orderDetails = drupalSettings.onlineReturns.returnInfo.orderInfo;
    } else if (Drupal.hasValue(drupalSettings.order)
      && Drupal.hasValue(drupalSettings.order.order_details)) {
      orderDetails['#order_details'] = drupalSettings.order.order_details;
    }
    return orderDetails;
  };
}(Drupal, drupalSettings));
