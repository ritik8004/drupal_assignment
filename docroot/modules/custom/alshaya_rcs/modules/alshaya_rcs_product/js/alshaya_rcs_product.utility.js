(function (Drupal) {
  window.commerceBackend = window.commerceBackend || {};

  /**
   * Fetch the product data from backend.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} parentSKU
   *   (optional) The parent sku value.
   */
  window.commerceBackend.getProductDataFromBackend = async function (sku, parentSKU = null) {
    var mainSKU = Drupal.hasValue(parentSKU) ? parentSKU : sku;
    // Get the product data.
    // The product will be fetched and saved in static storage.
    await globalThis.rcsPhCommerceBackend.getDataAsync('product', {sku: mainSKU});

    // Fetch the processed product data from the static storage.
    var product = window.commerceBackend.getProductData(mainSKU);
    var productSku = sku;
    var options = [];
    var is_configurable = typeof product.variants !== 'undefined';
    var url = product.url;

    if (is_configurable) {
      product = product.variants[sku];
      options = product.configurableOptions;
      url = product.url[drupalSettings.path.currentLanguage];
    }

    var stock = Drupal.hasValue(product.stock) ? product.stock : stock;

    Drupal.alshayaSpc.storeProductData({
      sku: productSku,
      parentSKU: mainSKU,
      title: product.cart_title,
      url,
      image: product.cart_image,
      price: product.priceRaw,
      options: options,
      promotions: product.promotionsRaw,
      freeGiftPromotion: product.freeGiftPromotion || [],
      maxSaleQty: product.max_sale_qty,
      maxSaleQtyParent: product.max_sale_qty_parent,
      isNonRefundable: product.is_non_refundable,
      gtmAttributes: product.gtm_attributes,
      stock: stock,
    });
  }

  /**
   * Get the stock status of the given sku.
   *
   * @param {string} sku
   *   The sku value.
   */
  window.commerceBackend.getProductStatus = async function (sku) {
    let stock = null;
    // Product data, containing stock information, is already present in local
    // storage before this function is invoked. So no need to call a separate
    // API to fetch stock status for V2.
    Drupal.alshayaSpc.getLocalStorageProductData(sku, function (product) {
      stock = {
        stock: product.stock.qty,
        in_stock: product.stock.in_stock,
        cnc_enabled: product.cncEnabled,
        max_sale_qty: product.maxSaleQty,
      };
    });

    return stock;
  }
})(Drupal);
