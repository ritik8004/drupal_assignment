(function (Drupal) {
  window.commerceBackend = window.commerceBackend || {};

   /**
   * Return product info from backend.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} parentSKU
   *   (optional) The parent sku value.
   *
   * @returns {object}
   *   The product info object.
   */
  window.commerceBackend.getProductDataAddToBagListing = async function (sku, parentSKU) {
    var mainSKU = Drupal.hasValue(parentSKU) ? parentSKU : sku;

    // The product will be fetched and saved in static storage.
    var productInfo = {};
    var product = await globalThis.rcsPhCommerceBackend.getData('product_by_sku', {sku: mainSKU});
    if (Drupal.hasValue(product.sku)) {
      window.commerceBackend.setRcsProductToStorage(product, 'plp');
      var productInfo = await processProductInfo(product);
      return productInfo;
    }
    else {
      response = {
        error: true,
        error_message: 'Product could not be loaded!',
      };
      return response;
    }
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
    var prices = window.commerceBackend.getPrices(variantData);
    var productLabels = await getProductLabels(variantData.parent_sku, variantData.sku);

    return {
      sku: variantData.sku,
      parent_sku: variantData.parent_sku,
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
    const sizeGuide = document.querySelector('.rcs-templates--size-guide');
    var link = sizeGuide.innerHTML;
    var sizeAttr = sizeGuide.getAttribute("data-attributes");
    sizeAttr = sizeAttr ? sizeAttr.split(',') : sizeAttr;
    productInfo.size_guide = {
      link: link,
      attributes: sizeAttr,
    };

    // Set configurable attributes.
    productInfo.configurable_attributes = [];
    product.configurable_options.forEach(function (option) {
      let attribute_id = parseInt(atob(option.attribute_uid), 10);
      productInfo.configurable_attributes[option.attribute_code] = {
        id: attribute_id,
        label: option.label,
        position: option.position,
        is_swatch: false,
        is_pseudo_attribute: (attribute_id === drupalSettings.psudo_attribute),
        values: option.values.map(function (option_value) {
          return {
            label: option_value.store_label,
            value: option_value.value_index.toString(),
          };
        })
      };
    });

    // Set configurable combinations.
    productInfo.configurable_combinations = window.commerceBackend.getConfigurableCombinations(product.sku);
    productInfo.configurable_combinations.attribute_hierarchy_with_values = productInfo.configurable_combinations.combinations;

    return productInfo;
  }
})(Drupal);
