(function orderDetailsUtilsRcs(Drupal, drupalSettings) {
  /**
   * Utility function to get order details for return pages.
   *
   * @returns {Promise}
   *   Promise which resolves to order details.
   */
  window.commerceBackend.getOrderDetails = function getOrderDetails() {
    let orderDetails = {};
    if (Drupal.hasValue(drupalSettings.onlineReturns)
      && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo)
      && Drupal.hasValue(drupalSettings.onlineReturns.returnInfo.orderInfo)) {
      orderDetails = drupalSettings.onlineReturns.returnInfo.orderInfo;
      var products = drupalSettings.onlineReturns.returnInfo.orderInfo['#products'];
      var productInfoPromises = [];
      Drupal.hasValue(products) && products.forEach(function eachProduct(product) {
        var skuForRequest = product.product_type === 'configurable'
          ? product.extension_attributes.parent_product_sku
          : product.sku;
        var orderData = globalThis.rcsPhCommerceBackend.getData('order_details_product_data', {sku: skuForRequest});
        productInfoPromises.push(orderData);
      });

      return Promise.all(productInfoPromises).then(function allProductsInfo(allProductInfo) {
        var productItems = allProductInfo[0].data.products.items;
        productItems.forEach(function eachProduct(product) {
          orderDetails['#products'].forEach(function eachOrderProduct(orderProduct) {
            window.commerceBackend.setMediaData(product);
            orderProduct.image_data = {
              url: window.commerceBackend.getTeaserImage(product),
              alt: product.name,
              title: product.name,
            };
            orderProduct.is_returnable = window.commerceBackend.isProductReturnable(product);
            // @todo Populate this value when working on big ticket items.
            orderProduct.is_big_ticket = null;
          });
        });
        return orderDetails;
      });
    } else if (Drupal.hasValue(drupalSettings.order)
      && Drupal.hasValue(drupalSettings.order.order_details)) {
      orderDetails['#order_details'] = drupalSettings.order.order_details;
      return new Promise((resolve) => resolve(orderDetails));
    }
  }
})(Drupal, drupalSettings);
