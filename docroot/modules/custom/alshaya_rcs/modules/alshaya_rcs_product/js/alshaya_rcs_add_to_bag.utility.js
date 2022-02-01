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
  window.commerceBackend.getProductDataAddToBagListing = async function (sku, parentSKU = null) {
    var mainSKU = Drupal.hasValue(parentSKU) ? parentSKU : sku;

    // The product will be fetched and saved in static storage.
    var productInfo = {};
    var response = globalThis.rcsPhCommerceBackend.getDataSynchronous('product', {sku: mainSKU});
    if (response) {
      RcsPhStaticStorage.set('product_' + mainSKU, response[0]);
      productInfo = processProductInfo(response[0]);
    }
    return productInfo;
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
  function processProductInfo(product) {
    var productInfo = {};
    productInfo.title = product.name;
    productInfo.max_sale_qty_parent_enable = false;
    if (typeof product.stock_data !== 'undefined' && product.stock_data.max_sale_qty !== 0) {
      productInfo.max_sale_qty_parent_enable = true;
    }

    // Get product labels.
    const productLabels = globalThis.rcsPhCommerceBackend.getDataSynchronous(
      'labels',
      { productIds: [product.id] },
      null,
      drupalSettings.path.currentLanguage,
      ''
    );
    let labels = [];
    productLabels[0]['items'].forEach(function (label) {
      labels.push({
        image: {
          url: label.image,
          alt: label.name,
          title: label.name
        },
        position: label.position
      });
    });

    // Process product variants.
    productInfo.variants = [];
    product.variants.forEach(function (variant) {
      let variant_data = variant.product;
      // Get Cart image.
      let cart = JSON.parse(variant_data.assets_cart);
      let maximum_price = variant_data.price_range.maximum_price;

      productInfo.variants.push({
        sku: variant_data.sku,
        parent_sku: product.sku,
        cart_title: product.name,
        cart_image: cart[0].url,
        media: {images: variant_data.media},
        product_labels: labels,
        original_price: maximum_price.regular_price.value,
        final_price: maximum_price.final_price.value,
        discount_percentage: maximum_price.discount.percent_off,
        max_sale_qty: variant_data.stock_data.max_sale_qty,
        stock: {
          qty: variant_data.stock_data.qty,
          status: (variant_data.stock_status == "IN_STOCK") ? true : false,
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

    // Set size guide.
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
      productInfo.configurable_attributes[option.attribute_code] = {
        id: parseInt(atob(option.attribute_uid), 10),
        label: option.label,
        position: option.position,
        is_swatch: false,
        values: option.values.map(function (option_value) {
          return {
            label: option_value.store_label,
            value: option_value.value_index
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
