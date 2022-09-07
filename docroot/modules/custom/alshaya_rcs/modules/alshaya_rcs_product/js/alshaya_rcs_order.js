(function main(Drupal, RcsEventManager, drupalSettings) {

  /**
   * Local static data store.
   */
  var staticDataStore = {
    recentOrdersData: {},
    orderDetailsProductData: {},
  };

  /**
   * Gets the label for provided attribute.
   *
   * @param {Object} mainProduct
   *   Main product object, i.e. Configurable product in case of configurable or
   *   Simple in case of simple product.
   * @param {string} attrCode
   *   Product attribute code.
   *
   * @returns {string|Boolean}
   *   Label if available or false if not available.
   */
   function getLabel(mainProduct, attrCode) {
    var label = '';

    // For color attribute, return the configured color label.
    if (drupalSettings.alshayaColorSplit.colorAttribute === attrCode) {
      label = drupalSettings.alshayaColorSplit.colorLabel;
    }
    else {
      Drupal.hasValue(mainProduct.configurable_options)
        && mainProduct.configurable_options.some(function eachOption(option) {
        if (option.attribute_code === attrCode) {
          label = option.label;
          return true;
        }
        return false;
      });
    }

    return label;
  }

  /**
   * Gets the option ID for given attribute code.
   *
   * @param {Object} product
   *   Product object.
   * @param {String} attrCode
   *   Attribute code.
   *
   * @returns {String}
   *   Option Id.
   */
  function getOptionId(product, attrCode) {
    var optionId = null;
    product.configurable_options.some(function eachOption(option) {
      if (attrCode === option.attribute_code) {
        optionId = atob(option.attribute_uid);
        return true;
      }
      return false;
    });

    return optionId;
  }

  /**
   * Fetch the product options.
   *
   * @param {Object} mainProduct
   *   Main product object.
   * @param {Object} product
   *   Current variant object.
   *
   * @returns {Array}
   *   Array of product options in format [{value: value, label:label}].
   */
  function getProductOptions(mainProduct, product) {
    var options = [];
    if (!Drupal.hasValue(product)) {
      return options;
    }

    Drupal.hasValue(product.attributes) && product.attributes.forEach(function eachAttribute(attr) {
      options.push({
        attribute_id: 'attr_' + attr.code,
        value: attr.label,
        label: getLabel(mainProduct, attr.code),
        option_id: getOptionId(mainProduct, attr.code).toString(),
        option_value: attr.value_index.toString(),
      });
    });

    // Check if color split is enabled.
    if (window.commerceBackend.getProductsInStyle) {
      var colorAttribute = drupalSettings.alshayaColorSplit.colorAttribute;
      if (Drupal.hasValue(product.product[colorAttribute])) {
        var label = window.commerceBackend.getAttributeValueLabel(colorAttribute, product.product[colorAttribute]);
        options.push({
          attribute_id: 'attr_' + colorAttribute,
          value: label,
          label: getLabel(mainProduct, colorAttribute),
          option_id: drupalSettings.psudo_attribute,
          option_value: product.product[colorAttribute],
        });
      }
    }

    return options;
  }

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
                data.options = getProductOptions(product, variant);
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
        product.attributes = indexedProducts[product.sku].options;
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
