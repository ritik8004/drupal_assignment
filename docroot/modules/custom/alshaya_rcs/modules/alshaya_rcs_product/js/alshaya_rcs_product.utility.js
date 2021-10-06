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

    window.commerceBackend.processAndStoreProductData(mainSKU, sku, 'productInfo');
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

  /**
   * Triggers stock refresh of the provided skus.
   *
   * @param {object} data
   *   The object of sku values and their requested quantity, like {sku1: qty1}.
   * @returns {Promise}
   *   The stock status for all skus.
   */
  window.commerceBackend.triggerStockRefresh = async function (data) {
    const cartData = Drupal.alshayaSpc.getCartData();
    const skus = {};

    Object.values(cartData.items).forEach(function (item) {
      const sku = item.sku;
      if (!Drupal.hasValue(data[sku])) {
        return;
      }

      Drupal.alshayaSpc.getLocalStorageProductData(sku, function (productData) {
        // Check if error is triggered when stock data in local storage is
        // greater than the requested quantity.
        if (productData.stock.qty > data[sku]) {
          skus[item.parentSKU] = sku;
          Drupal.alshayaSpc.removeLocalStorageProductData(sku);
        }
      });
    });

    const skuValues = Object.keys(skus);
    if (!skuValues.length) {
      return;
    }

    // Fetch the product data for the given skus which also saves them to the
    // static storage.
    globalThis.rcsPhCommerceBackend.getDataAsync('product', {sku: skuValues, op: 'in'});

    // Now store the product data to local storage.
    Object.entries(skus).forEach(function ([ parentSku, sku ]) {
      window.commerceBackend.processAndStoreProductData(parentSku, sku, 'productInfo');
    });
  }
})(Drupal);
