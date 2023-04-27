window.commerceBackend = window.commerceBackend || {};

(function (Drupal, drupalSettings) {
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
    if (Drupal.hasValue(product.variants)) {
      return product.variants[0].attributes.map(function (attribute) {
        return attribute.code;
      });
    }
  }

  /**
   * Get color attributes for product.
   *
   * @param {object} product
   *   Raw product object.
   * @param {array} styleProducts
   *   Raw style products object.
   *
   * @returns {array}
   *   The array of color attributes.
   */
  function getColorAttribute(product, styleProducts) {
    var colorAttribute = drupalSettings.alshayaColorSplit.colorAttribute;
    var colorAttributeValues = [];
    if (Drupal.hasValue(styleProducts)) {
      styleProducts.forEach(function (styleProduct) {
        if (Drupal.hasValue(styleProduct.variants)) {
          var processedColors = [];
          styleProduct.variants.forEach(function (variant) {
            // Check if color attribute is already added.
            if (!processedColors.includes(variant.product[colorAttribute])) {
              processedColors.push(variant.product[colorAttribute]);
              // Get the labels for the color attribute.
              if (Drupal.hasValue(variant.product[colorAttribute])) {
                const label = window.commerceBackend.getAttributeValueLabel(variant.product.color_attribute, variant.product[colorAttribute]);
                // Update the array with the color values.
                colorAttributeValues.push({value_index: variant.product[colorAttribute], store_label: label});
              }
            }
          });
        }
      });
    }
    else {
      const label = window.commerceBackend.getAttributeValueLabel(colorAttribute, product[colorAttribute]);
      // Update the array with the color values.
      colorAttributeValues.push({value_index: product[colorAttribute], store_label: label});
    }
    // Prepare configurable options object.
    var colorAttributeObj = {
      attribute_uid: btoa(drupalSettings.psudo_attribute),
      label: drupalSettings.alshayaColorSplit.colorLabel,
      position: -1,
      attribute_code: colorAttribute,
      values: colorAttributeValues,
    };
    return colorAttributeObj;
  }

  function setVariantData(variant, parent) {
    // This will store the color values of the styled product.
    const colorAttribute = drupalSettings.alshayaColorSplit.colorAttribute;
    // These values will be used later on.
    variant.product.parent_sku = parent.sku;
    variant.product.color_attribute = colorAttribute;
    variant.product.url_key = parent.url_key;
    // Variants will inherit delivery options from their parent sku.
    variant.product.deliveryOptions = {};
    if (Drupal.hasValue(drupalSettings.expressDelivery)
      && Drupal.hasValue(drupalSettings.expressDelivery.enabled)
    ) {
      variant.product.deliveryOptions = {
        express_delivery: {
          status: (Drupal.hasValue(parent.express_delivery) && Drupal.hasValue(drupalSettings.expressDelivery.express_delivery))
            ? 'active'
            : 'in-active'
        },
        same_day_delivery: {
          status: (Drupal.hasValue(parent.same_day_delivery) && Drupal.hasValue(drupalSettings.expressDelivery.same_day_delivery))
            ? 'active'
            : 'in-active'
        },
      };
    }
  }

  /**
   * Processes and stores style products data in static cache.
   *
   * @param {object} product
   *   Raw product object.
   * @param {Array} styleProducts
   *   Raw style products object.
   *
   * @returns {void}
   */
  function getProcessedStyleProducts(product, styleProducts) {
    // Return from here if the product is not configurable.
    if (Drupal.hasValue(product.type_id) && product.type_id !== 'configurable') {
      return product;
    }

    var mainProduct = null;
    // Use main product on PDP to display product attributes.
    if (globalThis.rcsPhGetPageType() === 'product'
      // Adding this check of of product id to confirm if product is containing
      // the proper product object.
      && Drupal.hasValue(product.id)) {
      mainProduct = product;
    }
    else {
      if (Drupal.hasValue(styleProducts)) {
        styleProducts.forEach(function eachStyleProduct(styleProduct) {
          if (styleProduct.sku === product.sku) {
            mainProduct = JSON.parse(JSON.stringify(styleProduct));
          }
        });
        if (!Drupal.hasValue(mainProduct)) {
          mainProduct = product;
        }
      }
      else {
        mainProduct = product;
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

    // Set the first child of the main product, to be used later.
    mainProduct.variants.some(function eachVariant(variant) {
      if (window.commerceBackend.isProductInStock(variant.product)) {
        mainProduct.firstChild = variant.product.sku;
        return true;
      }
      return false;
    });

    const mainProductAttributes = getProductConfigurableAttributes(mainProduct);

    if (Drupal.hasValue(styleProducts)) {
      // Alter the configurable variants list of the main product.
      // We will re-populate the variants.
      mainProduct.variants = [];
      styleProducts.forEach(function eachStyleProduct(styleProduct) {
        // Store each product data into static storage so that it can be used in
        // PLP add to bag to get the parent sku.
        if (mainProduct.sku !== styleProduct.sku) {
          window.commerceBackend.setRcsProductToStorage(styleProduct, mainProduct.context);
        }
        // Check if product is in stock.

        // Check if attributes of the product is the same as the main product.
        const styleProductAttributes = getProductConfigurableAttributes(styleProduct);

        // Check if the attributes are the same of the main product and the style
        // products.
        if (Drupal.hasValue(styleProductAttributes)) {
          var isAttributesSame = mainProductAttributes.length === styleProductAttributes.length;
          mainProductAttributes.forEach(function (mainProductAttribute) {
            if (!styleProductAttributes.includes(mainProductAttribute)) {
              isAttributesSame = false;
              // Break.
              return false;
            }
          });
        }

        if (!isAttributesSame) {
          return;
        }

        // Stores values of processed colors, so that they are not re-processed.
        styleProduct.variants.forEach(function eachVariant(variant) {
          setVariantData(variant, styleProduct);
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
    }
    else {
      mainProduct.variants.forEach(function eachVariant(variant) {
        setVariantData(variant, mainProduct);
      });
    }

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
    mainProduct.configurable_options.push(getColorAttribute(product, styleProducts));

    // Sort the configurable options according to position.
    mainProduct.configurable_options = mainProduct.configurable_options.sort(function (optionA, optionB) {
      return (optionA.position > optionB.position) - (optionA.position < optionB.position);
    });
    // Process and set the media data for the product.
    window.commerceBackend.setMediaData(mainProduct);
    // We will handle static storage caching for free gift
    // products from alshaya_rcs_free_gift module.
    if (product.context !== 'free_gift') {
      // Set the processed product to storage.
      window.commerceBackend.setRcsProductToStorage(mainProduct, mainProduct.context);
      // Reset static cache of product data as we have updated product data here
      // now.
      window.commerceBackend.resetStaticStoragePostProductUpdate();
    }
    return mainProduct;
  }

  /**
   * Asynchronously fetches and processes style products data.
   *
   * This is used only for PDP.
   *
   * @param {object} product
   *   Raw product object.
   * @param {string} loadStyles
   *   Load styled products.
   *
   * @returns {void}
   */
  window.commerceBackend.getProductsInStyle = async function getProductsInStyle(product, loadStyles = true) {
    // Return if result is empty.
    if (!Drupal.hasValue(product)
      || !Drupal.hasValue(product.style_code)) {
      return product;
    }

    var styleProducts = [];
    if (loadStyles) {
      // Get the products with the same style.
      styleProducts = await globalThis.rcsPhCommerceBackend.getData('products-in-style', { styleCode: product.style_code });
    }

    return getProcessedStyleProducts(product, styleProducts);
  };
})(Drupal, drupalSettings);
