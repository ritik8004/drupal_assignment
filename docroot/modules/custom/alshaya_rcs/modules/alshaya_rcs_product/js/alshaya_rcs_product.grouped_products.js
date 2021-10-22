(function (drupalSettings) {
  /**
   * Get the array of configurable attribute codes for the product.
   *
   * @param {object} product
   *   The product object.
   *
   * @returns {array}
   *   The array of configurable attributes.
   */
  function getProductConfigurableAttributes(product) {
    return product.variants[0].attributes.map(function (attribute) {
      return attribute.code;
    });
  }

  // Add the styled products.
  RcsEventManager.addListener('alshayaRcsUpdateResults', function getProductsInStyle(e) {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined'
      || typeof e.detail.result.style_code === 'undefined'
      || e.detail.result.style_code === null) {
      return;
    }

    // The original object will also be modified in this process.
    const mainProduct = e.detail.result;

    // Get the products with the same style.
    var styleProducts = globalThis.rcsPhCommerceBackend.getDataAsync('products-in-style', { styleCode: mainProduct.style_code });

    // If there are no products with the same style, then no further processing
    // required.
    if (!styleProducts.length) {
      return;
    }

    // This will hold the configugrable options for the main product keyed by
    // the attribute code and then the value index of the options.
    // Eg: {size: {11: {value_index: 10, store_label: "XS"}}}
    // Doing so, will reduce the amount of processing required.
    let mainProductConfigurableOptionsObject = {};
    mainProduct.configurable_options.forEach(function (option) {
      mainProductConfigurableOptionsObject[option.attribute_code] = {};
      // Copy the options of the main product to this object.
      option.values.forEach(function (value) {
        mainProductConfigurableOptionsObject[option.attribute_code][value.value_index] = value;
      });
    });

    const mainProductAttributes = getProductConfigurableAttributes(mainProduct);
    // Alter the configurable variants list of the main product.
    // We will re-populate the variants.
    mainProduct.variants = [];

    styleProducts.forEach(function (styleProduct) {
      // Check if product is in stock.

      // Check if attributes of the product is the same as the main product.
      const styleProductAttributes = getProductConfigurableAttributes(styleProduct);

      // Check if the attributes are the same of the main product and the style
      // products.
      let isAttributesSame = mainProductAttributes.length === styleProductAttributes.length;
      mainProductAttributes.forEach(function (mainProductAttribute) {
        if (!styleProductAttributes.includes(mainProductAttribute)) {
          isAttributesSame = false;
          // Break.
          return false;
        }
      });

      if (!isAttributesSame) {
        return;
      }

      styleProduct.variants.forEach(function (variant) {
        // These values will be used later on.
        variant.product.parent_sku = styleProduct.sku;
        variant.product.color_attribute = drupalSettings.alshayaRcs.colorAttribute;
        mainProduct.variants.push(variant);
      });

      // Get all the configurable options of the style products.
      styleProduct.configurable_options.forEach(function (styleProductOption) {
        // Add the values of the variant to the option slist.
        styleProductOption.values.forEach(function (value) {
          mainProductConfigurableOptionsObject[styleProductOption.attribute_code][value.value_index] = value;
        });
      });
    });

    // Now alter the configurable options for the main product.
    // Copy the resultant data for the attribute values from
    // mainProductConfigurableOptionsObject to mainProduct.configurable_options.
    Object.entries(mainProduct.configurable_options).forEach(function ([key, mainProductOption]) {
      mainProduct.configurable_options[key].values = [];
      Object.keys(mainProductConfigurableOptionsObject[mainProductOption.attribute_code]).forEach(function (value_index) {
        mainProduct.configurable_options[key].values.push(mainProductConfigurableOptionsObject[mainProductOption.attribute_code][value_index]);
      });
    });

    RcsPhStaticStorage.set('product_' + mainProduct.sku, mainProduct);
  }, 1);
})(drupalSettings);
