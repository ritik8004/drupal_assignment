(function orderDetailsUtilsRcs() {
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

    var products = drupalSettings.onlineReturns.returnInfo.orderInfo['#products'];
    var productInfoPromises = [];
    Drupal.hasValue(products) && products.forEach(function eachProduct(product) {
      var skuForRequest = product.product_type === 'configurable'
        ? product.extension_attributes.parent_product_sku
        : product.sku;

        var orderData = globalThis.rcsPhCommerceBackend.getData('order_details_product_data', {sku: skuForRequest});
        productInfoPromises.push(orderData);
    });

    return Promise.all(productInfoPromises).then(function eachProductInfo(productInfo) {
      return orderDetails;
    });
  }
})();
