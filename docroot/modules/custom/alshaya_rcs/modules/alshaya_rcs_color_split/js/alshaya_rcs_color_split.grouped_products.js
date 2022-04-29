(function () {
  'use strict';

  /**
   * Global variable which will contain acq_product related data/methods among
   * other things.
   */
  window.commerceBackend = window.commerceBackend || {};

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

  /**
   *  Updated the main product object with the styled products.
   *
   * @param {object} mainProduct
   *   The raw product object.
   *
   * @param {function} getAddToCartHtml
   *   The add to cart render function.
   */
  window.commerceBackend.getProductsInStyle = function getProductsInStyle(mainProduct, getAddToCartHtml) {
    // Return if result is empty.
    if (!Drupal.hasValue(mainProduct)
      || !Drupal.hasValue(mainProduct.style_code)) {
      return;
    }

    // Get the products with the same style.
    globalThis.rcsPhCommerceBackend.getDataAsynchronous('products-in-style', { styleCode: mainProduct.style_code }, function updateProductInStyle(product) {
      const styleProducts = product.data.products.items;
      // Get the main product entity from the style products list.
      for (var index = 0; index < styleProducts.length; index++) {
        if (styleProducts[index].sku === mainProduct.sku) {
          try {
            // Deep clone the object received in the response.
            mainProduct = JSON.parse(JSON.stringify(styleProducts[index]));
            break;
          } catch (error) {
            Drupal.alshayaLogger('error', 'Could not parse product data for SKU @sku', {
              '@sku': mainProduct.sku,
            });
            return;
          }
        }
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
      // Get the context from product data.
      const context = window.commerceBackend.getProductContext(mainProduct);
      // Alter the configurable variants list of the main product.
      // We will re-populate the variants.
      mainProduct.variants = [];
      // This will store the color values of the styled product.
      const colorAttributeValues = [];
      const colorAttribute = drupalSettings.alshayaColorSplit.colorAttribute;

      styleProducts.forEach(function (styleProduct) {
        // Store each product data into static storage so that it can be used in
        // PLP add to bag to get the parent sku.
        if (mainProduct.sku !== styleProduct.sku) {
          window.commerceBackend.setRcsProductToStorage(styleProduct, context);
        }
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
          variant.product.color_attribute = colorAttribute;
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

          if (!processedColors.includes(variant.product[colorAttribute])) {
            processedColors.push(variant.product[colorAttribute]);
            // Get the labels for the color attribute.
            if (Drupal.hasValue(variant.product[colorAttribute])) {
              const label = window.commerceBackend.getAttributeValueLabel(variant.product.color_attribute, variant.product[colorAttribute]);
              // Update the array with the color values.
              colorAttributeValues.push({ value_index: variant.product[colorAttribute], store_label: label });
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
        attribute_code: colorAttribute,
        values: colorAttributeValues,
      });

      // Sort the configurable options according to position.
      mainProduct.configurable_options = mainProduct.configurable_options.sort(function (optionA, optionB) {
        return (optionA.position > optionB.position) - (optionA.position < optionB.position);
      });

      // Fire 'rcsUpdateResult' with the updated product object, So that we have
      // the updated product object.
      mainProduct = RcsEventManager.fire('rcsUpdateResults', {
        detail: {
          result: mainProduct,
        }
      });

      mainProduct = mainProduct.detail.result;

      window.commerceBackend.setRcsProductToStorage(mainProduct, context);
      // Invoke the add to cart render function.
      window.commerceBackend.loadAddToCartForm(mainProduct, getAddToCartHtml);
    });
  }
})();
