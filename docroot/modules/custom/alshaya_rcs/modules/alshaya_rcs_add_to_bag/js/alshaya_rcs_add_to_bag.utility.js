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
      RcsPhStaticStorage.set('product_' + product.sku, product);
      // Get product labels.
      let labels = [];
      var productLabels = await window.commerceBackend.getProductLabelsData(mainSKU);
      productLabels.forEach(function (label) {
        labels.push({
          image: {
            url: label.image,
            alt: label.name,
            title: label.name
          },
          position: label.position
        });
      });
      var productInfo = processProductInfo(product, labels);
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
   * Creates product info object from product.
   *
   * @param {object} product
   *   Product object.
   * @param {array} labels
   *   Product labels.
   *
   * @returns {object}
   *   The product info object.
   */
  function processProductInfo(product, labels) {
    var productInfo = {};
    productInfo.title = product.name;
    productInfo.max_sale_qty_parent_enable = false;
    if (typeof product.stock_data !== 'undefined' && product.stock_data.max_sale_qty !== 0) {
      productInfo.max_sale_qty_parent_enable = true;
    }

    // Process product variants.
    productInfo.variants = [];
    product.variants.forEach(function (variant) {
      let variantData = variant.product;
      let prices = window.commerceBackend.getPrices(variantData);

      productInfo.variants.push({
        sku: variantData.sku,
        parent_sku: product.sku,
        cart_title: product.name,
        cart_image: variantData.media_cart,
        media: {images: variantData.media},
        product_labels: labels,
        original_price: prices.price.toString(),
        final_price: prices.finalPrice.toString(),
        discount_percentage: prices.percent_off,
        max_sale_qty: variantData.stock_data.max_sale_qty,
        stock: {
          qty: variantData.stock_data.qty,
          status: (variantData.stock_status === "IN_STOCK") ? true : false,
        }
      });
    });

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
