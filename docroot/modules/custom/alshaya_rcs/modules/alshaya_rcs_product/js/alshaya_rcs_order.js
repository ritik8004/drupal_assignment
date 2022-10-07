window.commerceBackend = window.commerceBackend || {};

(function main(Drupal, RcsEventManager, drupalSettings) {

  /**
   * Local static data store.
   */
  var staticDataStore = {
    recentOrdersData: {},
    orderDetailsProductData: {},
  };

  /**
   * Fetches product data for order details from Backend.
   *
   * @param {String} sku
   *   SKU value.
   *
   * @returns {Promise}
   *   Product data.
   */
  function getOrdersDetailsProductDataFromBackend(sku) {
    if (staticDataStore.orderDetailsProductData[sku]) {
      return staticDataStore.orderDetailsProductData[sku];
    }

    staticDataStore.orderDetailsProductData[sku] =  globalThis.rcsPhCommerceBackend.getData('order_details_product_data', {sku});
    return staticDataStore.orderDetailsProductData[sku];
  }


  /**
   * Get individual product data for recent orders section.
   *
   * @param {string} child
   *   Child sku.
   * @param {string} parent
   *   Parent sku.
   *
   * @returns {Promise}
   *   Product data for recent orders.
   */
  function getProductDataRecentOrders(child, parent) {
    if (Drupal.hasValue(staticDataStore.recentOrdersData[child])){
      return staticDataStore.recentOrdersData[child];
    }

    staticDataStore.recentOrdersData[child] = globalThis.rcsPhCommerceBackend.getData('recent_orders_product_data', {sku: parent}).then(function onRecentOrdersFetched(response) {
      var data = {};
      try {
        if (response.data.products.total_count) {
          var product = response.data.products.items[0];
          // Clone the product so as to not modify the original object.
          product = JSON.parse(JSON.stringify(product));
          window.commerceBackend.setMediaData(product);
          if (product.type_id === 'configurable') {
            product.variants.some(function eachVariant(variant) {
              if (variant.product.sku === child) {
                data = variant.product;
                return true;
              }
              return false;
            });
          }
          else {
            data = product;
          }
        }
        else {
          data = {sku: child};
        }

        return data;
      } catch (e) {
        Drupal.alshayaLogger('warning', 'Could not parse recent orders product data for SKU @sku', {
          '@sku': sku
        });
        return {sku: child};
      }
    });

    return staticDataStore.recentOrdersData[child];
  }

  /**
   * Get individual product data for order details page.
   *
   * @param {string} child
   *   Child sku.
   * @param {string} parent
   *   Parent sku.
   *
   * @returns {Promise}
   *   Product data for order details.
   */
  function getProductDataOrderDetails(child, parent) {
    return getOrdersDetailsProductDataFromBackend(parent).then(function onOrderDetailsFetched(response) {
      var data = {};
      try {
        if (response.data.products.total_count) {
          var product = response.data.products.items[0];
          // Clone the product so as to not modify the original object.
          product = JSON.parse(JSON.stringify(product));
          window.commerceBackend.setMediaData(product);
          if (product.type_id === 'configurable') {
            product.variants.some(function eachVariant(variant) {
              if (variant.product.sku === child) {
                // Only take the product data except the attributes.
                data = variant.product;
                data.options = window.commerceBackend.getProductOptions(product, variant);
                return true;
              }
              return false;
            });
          }
          else {
            data = product;
          }
        }
        else {
          data = {sku: child};
        }

        return data;
      } catch (e) {
        Drupal.alshayaLogger('warning', 'Could not parse order details product data for SKU @child. Message: @message', {
          '@sku': child,
          '@message': e.message,
        });
        return {sku: child};
      }
    });
  }

  /**
   * Gets data for Order Details page
   *
   * @returns {Promise}
   *   Order details data.
   */
  window.commerceBackend.getOrderDetailsData = function getOrderDetailsData() {
    var skus = document.getElementById('order-teaser-container').getAttribute('data-param-skus');
    skus = JSON.parse(skus);
    var requests = [];
    Object.entries(skus).forEach(function eachSku([child, parent]) {
      requests.push(getProductDataOrderDetails(child, parent));
    });

    return Promise.all(requests).then(function processProducts(products) {
      // Convert array to object with SKU value as index.
      var indexedProducts = {};
      products.forEach(function eachProduct(product) {
        indexedProducts[product.sku] = product;
      });

      // Merge product data to drupalSettings.order.
      drupalSettings.order.products.forEach(function eachOrderProduct(product) {
        if (!Drupal.hasValue(indexedProducts[product.sku])) {
          return;
        }
        // Set the attributes.
        product.attributes = Drupal.hasValue(indexedProducts[product.sku].options)
          ? indexedProducts[product.sku].options
          : [];
        // Set the name.
        product.name = Drupal.hasValue(indexedProducts[product.sku].name)
          ? indexedProducts[product.sku].name
          : product.name;
        // Set the display image.
        product.image = handlebarsRenderer.render('image', {
          src: indexedProducts[product.sku].media_teaser,
          alt: product.name,
          title: product.name,
        });

        drupalSettings.onlineReturns.refunded_products.some(function eachRefundedProduct(refundedProduct) {
          if (refundedProduct.sku !== product.sku) {
            return false;
          }
          refundedProduct.attributes = product.attributes;
          refundedProduct.name = product.name;
          refundedProduct.image_data = {
            url: indexedProducts[product.sku].media_teaser,
            alt: product.name,
            title: product.name,
          }
          refundedProduct.is_returnable = indexedProducts[product.sku].is_returnable;
          return true;
        });
      });

      return drupalSettings.order;
    });
  }

  RcsEventManager.addListener('invokingApi', function invokingApi(e) {
    // For the order teaser section, add the promises of requests to get product
    // data to the promises array so that these are resolved before we render
    // the section.
    if (e.extraData.placeholder === 'order_teaser'
      && Drupal.hasValue(e.extraData.params)
      && Drupal.hasValue(e.extraData.params['skus'])
    ) {
      // Get the product data based on sku.
      var skus = JSON.parse(e.extraData.params['skus']);
      if (e.extraData.params['context'] === 'recent_order') {
        Object.entries(skus).forEach(function eachSku([child, parent]) {
          e.promises.push(getProductDataRecentOrders(child, parent));
        });
      }
    }
  });

})(Drupal, RcsEventManager, drupalSettings);
