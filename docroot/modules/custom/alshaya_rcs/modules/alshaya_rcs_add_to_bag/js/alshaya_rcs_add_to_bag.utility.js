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
    if (Drupal.hasValue(staticDataStore.processedProduct[sku])) {
      return staticDataStore.processedProduct[sku];
    }

    var product = null;
    if (Drupal.hasValue(styleCode)) {
      product = await window.commerceBackend.getProductsInStyle({ sku, style_code: styleCode });
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
    return productInfo;
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
      }
    }
  };

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

    // Set product promotion info.
    productInfo.promotions = [];
    product.promotions.forEach(function (promotion) {
      productInfo.promotions.push({
        label: promotion.text,
        url: promotion.promo_web_url
      });
    });

    // Display size guide information in product configurable drawer.
    // @see _alshaya_acm_product_get_size_guide_info()
    productInfo.size_guide = drupalSettings.alshayaRcs.sizeGuide;

    // Set configurable attributes.
    var configurableCombinations = window.commerceBackend.getConfigurableCombinations(product.sku);
    productInfo.configurable_attributes = [];
    product.configurable_options.forEach(function (option) {
      var attribute_id = parseInt(atob(option.attribute_uid), 10);
      var optionValues = [];
      // Filter and process the option values.
      option.values.forEach(function eachValue(option_value) {
        // Disable unavailable options.
        if (!Drupal.hasValue(configurableCombinations.attribute_sku)
          || typeof configurableCombinations.attribute_sku[option.attribute_code][option_value.value_index] === 'undefined'
        ) {
          return false;
        }

        optionValues.push({
          label: option_value.store_label,
          value: option_value.value_index.toString(),
        });
      });
      productInfo.configurable_attributes[option.attribute_code] = {
        id: attribute_id,
        label: option.label,
        position: option.position,
        is_swatch: false,
        is_pseudo_attribute: (attribute_id === drupalSettings.psudo_attribute),
        values: optionValues,
      };
    });

    // Set configurable combinations.
    productInfo.configurable_combinations = configurableCombinations;
    productInfo.configurable_combinations.attribute_hierarchy_with_values = productInfo.configurable_combinations.combinations;

    return productInfo;
  }
})(Drupal, drupalSettings);
