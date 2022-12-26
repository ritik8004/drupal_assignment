/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function addToBagListingUtility(Drupal, drupalSettings) {

  var staticDataStore = {
    processedProduct: {},
  };

  /**
   * Get the cloned object of the statically stored product object.
   *
   * @param {string} sku
   *   SKU value.
   *
   * @returns {Object|null}
   *   Cloned product object.
   */
  function getClonedStaticProductData(sku) {
    if (Drupal.hasValue(staticDataStore.processedProduct[sku])) {
      try {
        // We clone the product object in order to prevent modification to the
        // object stored in the static cache.
        return JSON.parse(JSON.stringify(staticDataStore.processedProduct[sku]));
      } catch (error) {
        Drupal.alshayaLogger('error', 'Failed to parse stored products data for main sku @sku', {
          '@sku': sku,
        });
      }
    }

    return null;
  }

  /**
   * Return product info from backend.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} styleCode
   *   (optional) Style code value.
   *
   * @returns {object}
   *   The product info object.
   */
  window.commerceBackend.getProductDataAddToBagListing = async function (sku, styleCode) {
    var product = getClonedStaticProductData(sku);
    if (product) {
      return product;
    }

    if (Drupal.hasValue(styleCode)
      && Drupal.hasValue(window.commerceBackend.getProductsInStyle)
    ) {
      var styleCodeValue = typeof styleCode === 'object'
        ? styleCode[drupalSettings.path.currentLanguage]
        : styleCode;
      product = await window.commerceBackend.getProductsInStyle({ sku, style_code: styleCodeValue });
    }
    else {
      product = await globalThis.rcsPhCommerceBackend.getData('product_by_sku', {sku: sku});
    }

    // The product will be fetched and saved in static storage.
    var productInfo = {};
    if (Drupal.hasValue(product.sku)) {
      window.commerceBackend.setRcsProductToStorage(product, 'plp');
      productInfo = await processProductInfo(product);
    }
    else {
      productInfo = {
        error: true,
        error_message: 'Product could not be loaded!',
      };
    }

    staticDataStore.processedProduct[sku] = productInfo;
    return getClonedStaticProductData(sku);
  };

  /**
   * Gets the product labels for the given sku.
   *
   * @param {string} mainSku
   *   The main sku for the product.
   * @param {string} skuForLabel
   *   Selected variant sku for which lables are required.
   *
   * @returns {object}
   *   Labels data.
   */
  async function getProductLabels(mainSku, skuForLabel) {
    var labels = await window.commerceBackend.getProductLabelsData(mainSku, skuForLabel);
    var processedLabels = [];
    labels.forEach(function (label) {
      processedLabels.push({
        image: {
          url: label.image,
          alt: label.name,
          title: label.name
        },
        position: label.position
      });
    });

    return processedLabels;
  };

  /**
   * Processes and returns variant objects from raw data.
   *
   * @param {object} product
   *   Main product object.
   * @param {object} variantProduct
   *   Variant product object.
   *
   * @returns {object}
   *   Processed variant object.
   */
  async function processVariants(product, variantProduct) {
    var variantData = variantProduct.product;
    var parentSku = variantData.parent_sku;
    var prices = window.commerceBackend.getPrices(variantData);
    var productLabels = await getProductLabels(variantData.parent_sku, variantData.sku);
    // Get the promotion from variant if available else get it from parent.
    var variantPromotions = Drupal.hasValue(variantData.promotions)
      ? variantData.promotions : product.promotions;

    return {
      sku: variantData.sku,
      parent_sku: parentSku,
      cart_title: product.name,
      cart_image: variantData.media_cart,
      media: {images: variantData.media},
      product_labels: productLabels,
      original_price: prices.price.toString(),
      final_price: prices.finalPrice.toString(),
      discount_percentage: prices.percent_off,
      max_sale_qty: variantData.stock_data.max_sale_qty,
      stock: {
        qty: variantData.stock_data.qty,
        status: (variantData.stock_status === "IN_STOCK") ? true : false,
      },
      promotions: variantPromotions,
    }
  };

  /**
   * Parse swatch image tag and return swatch image url.
   *
   * @param {string} markup
   *   Swatch image tag.
   *
   * @returns {string}
   *   Swatch image url.
   */
  function parseImageUrl(markup) {
    var url = '';
    var ele = document.createElement('div');
    ele.innerHTML = markup;
    var img = ele.getElementsByTagName('img');
    url = img[0].getAttribute("src"); ;
    return url;
  }

  /**
   * Creates product info object from product.
   *
   * @param {object} product
   *   Product object.
   *
   * @returns {object}
   *   The product info object.
   */
  async function processProductInfo(product) {
    var productInfo = {};
    productInfo.title = product.name;
    productInfo.max_sale_qty_parent_enable = false;
    if (typeof product.stock_data !== 'undefined' && product.stock_data.max_sale_qty !== 0) {
      productInfo.max_sale_qty_parent_enable = true;
    }

    // This makes the API call to fetch the product labels and store them in
    // static storage.
    // We will again call this function for each variant, but now it will
    // fetch from static storage only.
    await getProductLabels(product.sku, product.sku);

    // Set product promotion info.
    productInfo.promotions = [];
    product.promotions.forEach(function (promotion) {
      productInfo.promotions.push({
        label: promotion.text,
        url: promotion.promo_web_url
      });
    });

    // Process product variants.
    productInfo.variants = [];
    var variantInfoPromises = [];
    product.variants.forEach(function eachVariant(variant) {
      variantInfoPromises.push(processVariants(product, variant));
    });

    try {
      productInfo.variants = await Promise.all(variantInfoPromises);
    } catch (e) {
      Drupal.alshayaLogger('error', 'Failed to process variants data for main sku @sku', {
        '@sku': product.sku,
      });
    }

    // Display size guide information in product configurable drawer.
    // @see _alshaya_acm_product_get_size_guide_info()
    productInfo.size_guide = drupalSettings.alshayaRcs.sizeGuide;

    // Set catalog restructuring enabled or not.
    productInfo.catalogRestructured = drupalSettings.alshayaRcs.catalogRestructured;

    // Set configurable attributes.
    var configurableCombinations = window.commerceBackend.getConfigurableCombinations(product.sku);
    productInfo.configurable_attributes = {};

    // Get color attribute config.
    if (Drupal.hasValue(drupalSettings.alshayaRcs.colorAttributeConfig)) {
      var configColorAttribute = drupalSettings.alshayaRcs.colorAttributeConfig.configurable_color_attribute;
      var configurableColorDetails = window.commerceBackend.getConfigurableColorDetails(product.sku);
    }

    product.configurable_options.forEach(function (option) {
      var isOptionSwatch = drupalSettings.alshayaRcs.pdpSwatchAttributes.includes(option.attribute_code);
      var attribute_id = parseInt(atob(option.attribute_uid), 10);
      var optionValues = [];
      // Filter and process the option values.
      var sortedValues = window.commerceBackend.getSortedAttributeValues(option.values, option.attribute_code);
      sortedValues.forEach(function eachValue(option_value) {
        // Disable unavailable options.
        if (!Drupal.hasValue(configurableCombinations.attribute_sku)
          || typeof configurableCombinations.attribute_sku[option.attribute_code][option_value.value_index] === 'undefined'
        ) {
          return false;
        }

        // Populate images for color swatch.
        if (isOptionSwatch && Drupal.hasValue(configColorAttribute) && option.attribute_code === configColorAttribute) {
          const childSku = window.commerceBackend.getChildSkuFromAttribute(product.sku, option.attribute_code, option_value.value_index.toString());
          let colorOption = configurableColorDetails.sku_configurable_options_color[option_value.value_index.toString()];
          let swatchType = '';
          switch (colorOption.swatch_type) {
            case 'RGB':
              swatchType = 'color';
              break;

            case 'Fabricswatch':
              swatchType = 'image';
              break;

            default:
              swatchType = 'text';
              break;
          }
          var swatch_data = (swatchType === 'image')
            ? parseImageUrl(colorOption.display_value)
            : colorOption.display_value;

          optionValues.push({
            label: option_value.store_label,
            value: option_value.value_index.toString(),
            data: swatch_data,
            type: swatchType,
          });
        }
        else {
          optionValues.push({
            label: option_value.store_label,
            value: option_value.value_index.toString(),
          });
        }
      });
      productInfo.configurable_attributes[option.attribute_code] = {
        id: attribute_id.toString(),
        label: option.label,
        position: option.position,
        is_swatch: isOptionSwatch,
        is_pseudo_attribute: (attribute_id === drupalSettings.psudo_attribute),
        values: optionValues,
      };
      if (isOptionSwatch) {
        productInfo.configurable_attributes[option.attribute_code].swatches = optionValues;
      }
    });

    // Set configurable combinations.
    productInfo.configurable_combinations = configurableCombinations;
    productInfo.configurable_combinations.attribute_hierarchy_with_values = productInfo.configurable_combinations.combinations;

    return productInfo;
  }
})(Drupal, drupalSettings);
