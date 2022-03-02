(function () {
  'use strict';

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
  RcsEventManager.addListener('rcsUpdateResults', function getProductsInStyle(e) {
    // Return if result is empty.
    if (!Drupal.hasValue(e.detail.result)
      || !Drupal.hasValue(e.detail.result.style_code)) {
      return;
    }

    // The original object will also be modified in this process.
    const mainProduct = e.detail.result;

    // Get the products with the same style.
    var styleProducts = globalThis.rcsPhCommerceBackend.getDataSynchronous('products-in-style', { styleCode: mainProduct.style_code });

    // If there are no products with the same style, then no further processing
    // required.
    if (!styleProducts.length) {
      return;
    }

    // This will hold the configugrable options for the main product keyed by
    // the attribute code and then the value index of the options.
    // Eg: {size: {11: {value_index: 10, store_label: "XS"}}}
    // Doing so, will reduce the amount of processing required.
    var mainProductConfigurableOptionsObject = {};
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
    // This will store the color values of the styled product.
    const colorAttributeValues = [];

    styleProducts.forEach(function (styleProduct) {
      // Check if product is in stock.

      // Check if attributes of the product is the same as the main product.
      const styleProductAttributes = getProductConfigurableAttributes(styleProduct);

      // Check if the attributes are the same of the main product and the style
      // products.
      var isAttributesSame = mainProductAttributes.length === styleProductAttributes.length;
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

      // Stores values of processed colors, so that they are not re-processed.
      const processedColors = [];
      styleProduct.variants.forEach(function (variant) {
        // These values will be used later on.
        variant.product.parent_sku = styleProduct.sku;
        variant.product.color_attribute = drupalSettings.alshayaColorSplit.colorAttribute;
        variant.product.url_key = styleProduct.url_key;
        // Variants will inherit delivery options from their parent sku.
        variant.product.deliveryOptions = {};
        if (Drupal.hasValue(drupalSettings.expressDelivery)
          && Drupal.hasValue(drupalSettings.expressDelivery.enabled)
        ) {
          variant.product.deliveryOptions = {
            express_delivery: {
              status: (Drupal.hasValue(styleProduct.express_delivery) && Drupal.hasValue(drupalSettings.expressDelivery.express_delivery))
                ? 'active'
                : 'in-active'
            },
            same_day_delivery: {
              status: (Drupal.hasValue(styleProduct.same_day_delivery) && Drupal.hasValue(drupalSettings.expressDelivery.same_day_delivery))
                ? 'active'
                : 'in-active'
            },
          };
        }

        if (!processedColors.includes(variant.product.color)) {
          processedColors.push(variant.product.color);
          // Get the labels for the color attribute.
          if (Drupal.hasValue(variant.product.color)) {
            const label = window.commerceBackend.getAttributeValueLabel(variant.product.color_attribute, variant.product.color);
            // Update the array with the color values.
            colorAttributeValues.push({value_index: variant.product.color, store_label: label});
          }
        }

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

    // Push color to the configurable options of the main product.
    mainProduct.configurable_options.push({
      attribute_uid: btoa(drupalSettings.psudo_attribute),
      label: drupalSettings.alshayaColorSplit.colorLabel,
      position: -1,
      attribute_code: drupalSettings.alshayaColorSplit.colorAttribute,
      values: colorAttributeValues,
    });

    // Sort the configurable options according to position.
    mainProduct.configurable_options = mainProduct.configurable_options.sort(function (optionA, optionB) {
      return (optionA.position > optionB.position) - (optionA.position < optionB.position);
    });

    RcsPhStaticStorage.set('product_' + mainProduct.sku, mainProduct);
  }, 100);
})();
