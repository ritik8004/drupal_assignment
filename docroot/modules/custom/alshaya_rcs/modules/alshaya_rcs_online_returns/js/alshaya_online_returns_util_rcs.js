window.commerceBackend.getOrderDetails = window.commerceBackend.getOrderDetails || {};

(function orderDetailsUtilsRcs(Drupal, drupalSettings) {

  var staticStorage = {
    orderDetailsStorage: {},
  };

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
        var productItems = [];
        // Promise.all() returns the products in different arrays.
        // So we merge the data into a single array of products.
        allProductInfo.forEach(function eachProductItem(item) {
          if (Drupal.hasValue(item.data.products.items[0])) {
            productItems.push(item.data.products.items[0]);
          }
        });

        // Loop over the order items.
        // Find the corresponding product data for the order item which is
        // fetched from the API call above.
        // Then merge the product data with the order data.
        orderDetails['#products'].forEach(function eachOrderProduct(orderProduct) {
          productItems.forEach(function eachProduct(product) {
            var currentVariant = null;
            product.variants.some(function name(variant) {
              if (variant.product.sku === orderProduct.sku) {
                currentVariant = variant;
                return true;
              }
              return false;
            });
            if (!Drupal.hasValue(currentVariant)) {
              return;
            }
            // Store in static storage so that it can be used later.
            staticStorage.orderDetailsStorage[product.sku] = product;
            window.commerceBackend.setMediaData(product);
            orderProduct.image_data = {
              url: window.commerceBackend.getTeaserImage(product),
              alt: product.name,
              title: product.name,
            };
            orderProduct.attributes = window.commerceBackend.getProductOptions(product, currentVariant);
            orderProduct.is_returnable = window.commerceBackend.isProductReturnable(product, orderProduct.sku);
            // @todo Populate this value when working on big ticket items.
            // Now, we do receive big ticket item info in orders API itself
            // so we might consider removing this code in future when
            // big ticket items are enabled for v3 brands.
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
      drupalSettings.onlineReturns.returnInfo.orderInfo['#products'].forEach((product) => {
        var parentSku = Drupal.hasValue(product.type)
          ? product.extension_attributes.parent_product_sku
          : sku;
        var sku = product.sku;
        if (Drupal.hasValue(drupalSettings.onlineReturns.returnInfo.orderInfo['#gtm_info'].products[sku])) {
          drupalSettings.onlineReturns.returnInfo.orderInfo['#gtm_info'].products[sku] = staticStorage.orderDetailsStorage[parentSku].gtm_attributes;
        }
      });

      return drupalSettings.onlineReturns.returnInfo.orderInfo['#gtm_info'];
    }

    // For order detail page, get the data from online returns drupal settings.
    if (Drupal.hasValue(drupalSettings.onlineReturns)
      && Drupal.hasValue(drupalSettings.onlineReturns.gtm_info)) {
      return drupalSettings.onlineReturns.gtm_info;
    }

    return {};
  }

  /**
   * Set returns product data in order data.
   *
   * @param {Object} orderProducts
   *   Array of products in the order.
   */
  window.commerceBackend.setReturnsProductData = async function setReturnsProductData(orderProducts) {
    var productInfoPromises = [];
    orderProducts.forEach(function eachProduct(product) {
      var skuForRequest = product.product_type === 'configurable'
        ? product.extension_attributes.parent_product_sku
        : product.sku;
      var orderData = globalThis.rcsPhCommerceBackend.getData('order_details_product_data', {sku: skuForRequest});
      productInfoPromises.push(orderData);
    });

    return await Promise.all(productInfoPromises).then(function allProductsInfo(allProductInfo) {
      var productItems = {};
      // Promise.all() returns the products in different arrays.
      // So we merge the data into a single array of products.
      allProductInfo.forEach(function eachProductItem(item) {
        if (Drupal.hasValue(item.data.products.items[0])) {
          productItems[item.data.products.items[0].sku] = item.data.products.items[0];
        }
      });

      orderProducts.forEach(function eachProduct(orderProduct) {
        // Fetch the ordered product data.
        var product = productItems[orderProduct.sku];
        orderProduct.attributes = [];
        if (orderProduct.type === 'configurable') {
          product = productItems[orderProduct.extension_attributes.parent_product_sku];
          product.variants.some(function eachVariant(variant) {
            if (variant.product.sku === orderProduct.sku) {
              orderProduct.attributes = window.commerceBackend.getProductOptions(product, variant);
              return true;
            }
            return false;
          });
        }

        // Update the product data with proper name, image and options.
        window.commerceBackend.setMediaData(product);
        orderProduct.image_data = {
          url: window.commerceBackend.getTeaserImage(product),
          alt: product.name,
          title: product.name,
        };
        orderProduct.is_returnable = window.commerceBackend.isProductReturnable(product, orderProduct.sku);
      });
    });
  }

  document.addEventListener('alterOrderProductData', function onAlterProductData(e) {
    var product = e.detail.data.product;
    drupalSettings.onlineReturns.refunded_products.some(function eachRefundedProduct(refundedProduct) {
      if (refundedProduct.sku !== product.sku) {
        return false;
      }
      refundedProduct.attributes = product.attributes;
      refundedProduct.name = product.name;
      refundedProduct.image_data = {
        url: product.imageData.src,
        alt: product.imageData.alt,
        title: product.imageData.title,
      }
      refundedProduct.is_returnable = product.is_returnable;
      return true;
    });
  });
})(Drupal, drupalSettings);
