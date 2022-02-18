(function (Drupal) {
  window.commerceBackend = window.commerceBackend || {};

  const staticStorage = {
    attrLabels: {},
  };

  /**
   * Fetch the product data from backend.
   *
   * This is just a helper method for Drupal.alshayaSpc.getProductData() and
   * Drupal.alshayaSpc.getProductDataV2().
   * Do not invoke directly.
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
    globalThis.rcsPhCommerceBackend.getDataSynchronous('product', {sku: mainSKU});

    window.commerceBackend.processAndStoreProductData(mainSKU, sku, 'productInfo');
  };

  /**
   * Get the stock status of the given sku.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} parentSKU
   *   The parent sku value.
   *
   * @returns {object}
   *   The product stock data.
   */
  window.commerceBackend.getProductStatus = async function (sku, parentSKU) {
    // Product data, containing stock information, is already present in local
    // storage before this function is invoked. So no need to call a separate
    // API to fetch stock status for V2.
    const product = await Drupal.alshayaSpc.getProductDataV2(sku, parentSKU);

    return {
      stock: product.stock.qty,
      in_stock: product.stock.in_stock,
      cnc_enabled: product.cncEnabled,
      max_sale_qty: product.maxSaleQty,
    };
  };

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
    globalThis.rcsPhCommerceBackend.getDataSynchronous('product', {sku: skuValues, op: 'in'});

    // Now store the product data to local storage.
    Object.entries(skus).forEach(function ([ parentSku, sku ]) {
      window.commerceBackend.processAndStoreProductData(parentSku, sku, 'productInfo');
    });
  };

  /**
   * Gets the attribute label.
   *
   * @param {string} attrName
   *   The attribute name.
   * @param {string} attrValue
   *   The attribute value.
   *
   * @returns {string}
   *   The attribute label.
   */
  window.commerceBackend.getAttributeValueLabel = function (attrName, attrValue) {
    if (Drupal.hasValue(staticStorage['attrLabels'][attrName])) {
      return staticStorage['attrLabels'][attrName][attrValue];
    }

    const response = globalThis.rcsPhCommerceBackend.getDataSynchronous('product-option', { attributeCode: attrName });
    allOptionsForAttribute = {};

    // Process the data to extract what we require and format it into an object.
    response.data.customAttributeMetadata.items[0].attribute_options.forEach(function (option) {
      allOptionsForAttribute[option.value] = option.label;
    });

    // Set to static storage.
    staticStorage['attrLabels'][attrName] = allOptionsForAttribute;

    return allOptionsForAttribute[attrValue];
  };

  /**
   * Get the first child with media.
   *
   * @param {object}
   *   The raw product object.
   *
   * @return {object|null}
   *   The first child raw product object or null if no child with media found.
   *
   * @see \Drupal\alshaya_acm_product\SkuImagesManager::getFirstChildWithMedia()
   */
  const getFirstChildWithMedia = function (product) {
    const firstChild = product.variants.find(function (variant) {
      return Drupal.hasValue(variant.product.media) ? variant.product : false;
    });

    if (Drupal.hasValue(firstChild)) {
      return firstChild.product;
    }

    return null;
  };

  /**
   * Get SKU to use for gallery when no specific child is selected.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @return {object}
   *   The gallery sku object.
   *
   * @see \Drupal\alshaya_acm_product\SkuImagesManager::getSkuForGallery()
   */
  const getSkuForGallery = function (product) {
    let skuForGallery = product;
    let child = null;

    switch (drupalSettings.alshayaRcs.useParentImages) {
      case 'never':
        if (product.type_id === 'configurable') {
          child = getFirstChildWithMedia(product);
        }
        break;
    }

    skuForGallery = Drupal.hasValue(child) ? child : skuForGallery;
    return skuForGallery;
  };

  /**
   * Get first image from media to display as list.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @return {string}
   *   The media item url.
   *
   * @see \Drupal\alshaya_acm_product\SkuImagesManager::getFirstImage()
   */
  window.commerceBackend.getFirstImage = function (product) {
    const galleryProduct = getSkuForGallery(product);
    return Drupal.hasValue(galleryProduct.media[0]) ? galleryProduct.media[0] : null;
  };

  /**
   * Get the image from media to as the cart image.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @return {string}
   *   The media item url.
   */
  window.commerceBackend.getCartImage = function (product) {
    const galleryProduct = getSkuForGallery(product);
    return galleryProduct.media_cart;
  };

  /**
   * Get the image from media to display as teaser image.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @return {string}
   *   The media item url.
   */
   window.commerceBackend.getTeaserImage = function (product) {
    const galleryProduct = getSkuForGallery(product);
    return galleryProduct.media_teaser;
  };


  /**
   * Get the prices from product entity.
   *
   * @param {object} product
   *   The raw product object.
   * @param {boolean} formatted
   *   if we need to return formatted price.
   *
   * @return {array}
   *   The price array.
   */
  window.commerceBackend.getPrices = function (product, formatted) {
    var prices = {
      price : formatted ? globalThis.renderRcsProduct.getFormattedAmount(product.price_range.maximum_price.regular_price.value) : product.price_range.maximum_price.regular_price.value,
      finalPrice: formatted ? globalThis.renderRcsProduct.getFormattedAmount(product.price_range.maximum_price.final_price.value) : product.price_range.maximum_price.final_price.value,
      percent_off: product.price_range.maximum_price.discount.percent_off,
    };
    return prices;
  };

  /**
   * Gets the siblings and parent of the given sku.
   *
   * @param {string} sku
   *   The given sku.
   *
   * @returns
   *   An object containing the product skus in the keys and the product entities
   * in the values.
   * If sku is simple and is the main product, then sku is returned.
   * If sku is simple a child product, then all the siblings and parent are
   * returned.
   * If sku is configurable, then the sku and its children are returned.
   */
  function getSkuSiblingsAndParent(sku) {
    const allProducts = window.commerceBackend.getProductData(null, null, false);
    const data = {};

    Object.keys(allProducts).forEach(function eachMainProduct(mainProductSku) {
      const mainProduct = allProducts[mainProductSku];

      if (mainProduct.sku === sku) {
        data[sku] = mainProduct;
        if (mainProduct.type_id === 'configurable') {
          mainProduct.variants.forEach(function eachVariantProduct(variant) {
            data[variant.product.sku] = variant.product;
          });
        }
      }
      else {
        if (mainProduct.type_id === 'configurable') {
          mainProduct.variants.forEach(function eachVariantProduct(variant) {
            if (variant.product.sku === sku) {
              data[mainProduct.sku] = mainProduct;
              mainProduct.variants.forEach(function eachVariantProduct(variant) {
                data[variant.product.sku] = variant.product;
              });
            }
          });
        }
      }
    });

    return data;
  }

  /**
   * Gets the labels data for the given product ID.
   *
   * @param sku
   *   The sku value.
   *
   * @returns object
   *   The labels data for the given product ID.
   */
  window.commerceBackend.getProductLabelsData = async function  (sku) {
    if (typeof staticDataStore.labels === 'undefined') {
      staticDataStore.labels = {};
      staticDataStore.labels[sku] = null;
    }

    if (staticDataStore.labels[sku]) {
      return staticDataStore.labels[sku];
    }

    const products = getSkuSiblingsAndParent(sku);
    const productIds = {};
    Object.keys(products).forEach(function (sku) {
      staticDataStore.labels[sku] = [];
      productIds[products[sku].id] = sku;
    });

    const labels = await globalThis.rcsPhCommerceBackend.getData(
      'labels',
      { productIds: Object.keys(productIds) },
      null,
      drupalSettings.path.currentLanguage,
      ''
    );

    if (Array.isArray(labels) && labels.length) {
      labels.forEach(function (productLabels) {
        if (!productLabels.items.length) {
          return;
        }
        const productId = productLabels.items[0].product_id;
        const sku = productIds[productId];
        staticDataStore.labels[sku] = productLabels.items;
      });
    }

    return staticDataStore.labels[sku];
  }

  // Event listener to update static promotion.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined'
      || typeof e.detail.result.promotions === 'undefined') {
      return null;
    }

    const promotions = e.detail.result.promotions;
    // Update the promotions attribute based on the requirement.
    promotions.forEach((promotion, index) => {
      promotions[index] = {
        promo_web_url: promotion.url,
        text: promotion.label,
        context: promotion.context,
        type: promotion.type,
      }
    });
    e.detail.result.promotions = promotions;

  });

})(Drupal);
