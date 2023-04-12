/**
 * Global variable which will contain pdp magazine related data.
 */
 window.commerceBackend = window.commerceBackend || {};

(function (Drupal, drupalSettings, RcsEventManager) {
  const staticDataStore = {
    pdpPromotion: [],
  };

  // Call event after entity load and process product data.
  RcsEventManager.addListener('alshayaPageEntityLoaded', async function pageEntityLoaded(e) {
    var mainProduct = e.detail.entity;
    var mainProductClone = null;
    try {
      mainProductClone = JSON.parse(JSON.stringify(mainProduct));
    } catch (e) {
      mainProductClone = mainProduct;
    }

    if (Drupal.hasValue(window.commerceBackend.getProductsInStyle)) {
      mainProduct = await window.commerceBackend.getProductsInStyle(mainProduct);
    }

    var processedProduct = {};
    var configurableCombinations = {};
    // Prepare data for productinfo to be used in new pdp layout.
    const productInfoV1 = window.commerceBackend.getProductData(mainProduct.sku, false, true);
    const productInfoV2 = processProductMagV2(mainProduct, productInfoV1);
    processedProduct[mainProduct.sku] = {...productInfoV1, ...productInfoV2};
    if (mainProduct.type_id === 'configurable') {
      configurableCombinations[mainProduct.sku] = processConfigurableCombinations(mainProduct.sku);
      configurableCombinations[mainProduct.sku].firstChild = getFirstChild(mainProductClone);
    }

    // Get the product labels.
    processedProduct[mainProduct.sku].labels = {};
    if (Drupal.hasValue(window.commerceBackend.getProductLabels)) {
      Object.assign(processedProduct[mainProduct.sku].labels,
        await window.commerceBackend.getProductLabels(mainProduct.sku));
    }

    // If free gift is enabled, alter product data to support magv2 pdp free gifts.
    // Check <PdpFreeGift> react component.
    if (Drupal.hasValue(window.commerceBackend.processFreeGiftDataReactRender)) {
      processedProduct = await window.commerceBackend.processFreeGiftDataReactRender(processedProduct, mainProduct.sku);
    }

    // Pass product data into pdp layout react component.
    window.alshayaRenderPdpMagV2(processedProduct, configurableCombinations);
  });

  /**
   * Get first child of SKU.
   *
   * @param {Object} product
   *   Product object.
   *
   * @returns {String|null}
   *   SKU value or null.
   */
  function getFirstChild(product) {
    var firstChild = null;
    // @todo Add a general way to figure out the first child instead of adding
    // separate check for OOS products.
    // We might need to investigate the method
    // window.commerceBackend.getConfigurableCombinations() and see if the
    // order of the attributes is the same as in V2.
    if (window.commerceBackend.isProductInStock(product)
      && product.variants.length
    ) {
      product.variants.some(function eachVariant(variant) {
        if (window.commerceBackend.isProductInStock(variant.product)) {
          firstChild = variant.product.sku;
          return true;
        }
        return false;
      });
    }
    else {
      var combinations = window.commerceBackend.getConfigurableCombinations(product.sku);
      try {
        var sortedVariants = Object.values(Object.values(combinations['attribute_sku'])[0])[0];
        firstChild = sortedVariants[0];
      } catch (e) {
        // Do nothing.
      }
    }

    return firstChild;
  }

  /**
   * Get stock status for the product.
   *
   * @param {Object} product
   *   Product object.
   */
  function getProductStockStatus(product) {
    var status = false;
    var inStock = 'IN_STOCK';

    if (product.type_id === 'configurable'
      && Drupal.hasValue(product.variants)
    ) {
      product.variants.some(function eachVariant(variant) {
        if (variant.product.stock_status === inStock) {
          status = true;
          return true;
        }
        return false;
      });
    }
    else if (product.type_id === 'simple' && product.stock_status === inStock) {
      status = true;
    }

    return status;
  }

  /**
   * Process product data as per maganize v2 data format.
   *
   * @param {object} product
   *   The raw product object.
   * @param {object} processedProduct
   *   The processed product object.
   *
   * @returns {Object}
   *    The product data.
   */
  function processProductMagV2(product, processedProduct) {
    const productData = {
      brandLogo: product.brand_logo_data,
      description: Drupal.hasValue(product.description) ? product.description.html : '',
      expressDelivery: product.express_delivery,
      is_product_buyable: product.is_buyable,
      shortDesc: Drupal.hasValue(product.short_description) ? product.short_description.html : '',
      stockQty: product.stock_data.qty,
      stockStatus: getProductStockStatus(product),
      title: {'label': product.name},
      rawGallery: updateGallery(product, product.name),
      additionalAttributes: Drupal.hasValue(product.description.additional_attributes) && Object.keys(product.description.additional_attributes).length ? product.description.additional_attributes : {},
      quickFit: Drupal.hasValue(product.quick_fit) ? product.quick_fit : '',
    }
    if (product.type_id === 'configurable') {
      productData.variants = getVariantsInfoMagV2(product, processedProduct.variants);
    }

    return productData;
  }

  /**
   * Process swatch data for pdpMagV2.
   *
   * @param {object} mainSKU
   *   The main sku.
   *
   * @returns {Object}
   *    The product data.
   */
  function processConfigurableCombinations(mainSKU) {
    var combinations = window.commerceBackend.getConfigurableCombinations(mainSKU);
    var colorDetails = window.commerceBackend.getConfigurableColorDetails(mainSKU);
    // Allow other module to override the changes.
    RcsEventManager.fire('alshayaRcsAlterProcessConfigurableCombinations', {
      detail: {
        combinations,
        colorDetails
      }
    });

    return combinations;
  }

  /**
   * Gets the variants for the given product entity.
   *
   * @param {object} product
   *   The product entity.
   *
   * @returns {Object}
   *   The variant info.
   */
   function getVariantsInfoMagV2(product, processedVariants) {
    const info = {};
    product.variants.forEach(function (variant) {
      const variantInfo = variant.product;
      if (window.commerceBackend.isProductInStock(variantInfo)
        && Drupal.hasValue(processedVariants[variantInfo.sku])
      ) {
        info[variantInfo.sku] = processedVariants[variantInfo.sku];
        info[variantInfo.sku]['rawGallery'] = updateGallery(variantInfo, product.name);
      }
    });

    return info;
  }

  /**
   * Process gallery data as per new pdp layout format.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @returns {Object}
   *   The gallery for pdp new layout.
   */
  function updateGallery(product, name) {
    var gallery = {};
    var thumbnails = [];

    const child = window.commerceBackend.getSkuForGallery(product);
    child.media.forEach(function (gallery, key) {
      thumbnails[key] = {
        'fullurl': gallery.url,
        'label': name,
        'mediumurl': gallery.medium,
        'thumburl': gallery.thumbnails,
        'zoomurl': gallery.zoom,
        'type': 'image',
      };
    });

    if (thumbnails.length > 0) {
      gallery = {
        'pager_flag': (thumbnails.length > drupalSettings.alshayaRcs.pdpGalleryPagerLimit)
          ? 'pager-yes' : 'pager-no',
        'sku': child.sku,
        'thumbnails': thumbnails,
      };
    }

    return gallery;
  }

  /**
   * Get Product promotion labels.
   *
   * @param {string} skuMainCode
   *   The parent sku value.
   *
   * @returns {Object}
   *   Product promotion labels.
   */
  window.commerceBackend.getPdpPromotionLabels = function getPdpPromotionLabels(skuMainCode) {
    const staticStorageKey = `pdpPromotion_${skuMainCode}`;

    let promotionData = Drupal.hasValue(staticDataStore.pdpPromotion[staticStorageKey])
      ? staticDataStore.pdpPromotion[staticStorageKey]
      : null;

    if (promotionData !== null) {
      return promotionData;
    }

    return globalThis.rcsPhCommerceBackend.getData('single_product_by_sku', {
      sku: skuMainCode,
    }).then(function(response) {
      if (Drupal.hasValue(response.data)) {
        const promotionVal = [];
        if (Drupal.hasValue(response.data.products.items[0].promotions)) {
          const promotionData = response.data.products.items[0].promotions;
          promotionData.forEach((promotion, index) => {
            promotionVal[index] = {
              promo_web_url: promotion.url,
              text: promotion.label,
              context: promotion.context,
              type: promotion.type,
            };
          });
        }
        promotionData = promotionVal;
        staticDataStore.pdpPromotion[staticStorageKey] = promotionData;

        return promotionData;
      }
      // If graphQL API is returning Error.
      Drupal.alshayaLogger('error', 'Error while calling the graphQL to fetch product promotion info for sku: @sku', {
        '@sku': skuMainCode,
      });

      return null;
    });
  }

})(Drupal, drupalSettings, RcsEventManager);
