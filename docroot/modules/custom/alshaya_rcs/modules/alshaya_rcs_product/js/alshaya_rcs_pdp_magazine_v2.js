(function (Drupal, drupalSettings, $) {
  RcsEventManager.addListener('alshayaPageEntityLoaded', function (e) {
    var processedProduct = [];
    var configurableCombinations = [];
    var productLabel = [];
    const mainProduct = e.detail.entity;
    // Prepare data for productinfo to be used in new pdp layout.
    const productInfoV1 = window.commerceBackend.getProductData(mainProduct.sku, false, true);
    const productInfoV2 = processProductMagV2(mainProduct, productInfoV1);
    processedProduct[mainProduct.sku] = {...productInfoV1, ...productInfoV2};
    if (mainProduct.type_id === 'configurable') { 
      configurableCombinations[mainProduct.sku] = window.commerceBackend.getConfigurableCombinations(mainProduct.sku)
      productLabel = window.commerceBackend.getProductLabel(mainProduct.sku);
    }
    // Pass product data into pdp layout react component.
    window.alshayaRenderPdpMagV2(processedProduct, configurableCombinations, productLabel);
  });

  /**
   * Process product data as per maganize v2 data format.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @returns {Object}
   *    The product data.
   */
  function processProductMagV2(product, processedProduct) {
    const productData = {
      brandLogo: product.brand_logo_data,
      description: getProcessedDesc(product, 'description'),
      expressDelivery: product.express_delivery,
      is_product_buyable: product.is_buyable,
      shortDesc: getProcessedDesc(product, 'shortDesc'),
      stockQty: product.stock_data.qty,
      stockStatus: product.stock_status === 'IN_STOCK',
      title: {'label': product.name},
      rawGallery: updateGallery(product, product.name),
    }
    if (product.type_id === 'configurable') {
      productData.variants = getVariantsInfoMagV2(product, processedProduct.variants);
    }

    return productData;
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
      info[variantInfo.sku] = processedVariants[variantInfo.sku];
      info[variantInfo.sku]['rawGallery'] = updateGallery(variantInfo, product.name);
    });

    return info;
  }

  /**
   * Process product description as per maganize v2 data format.
   *
   * @param {object} product
   *   The raw product object.
   * @param {object} descType
   *   The description type.
   *
   * @returns {Object}
   *   The product description.
   */
  function getProcessedDesc(product, descType) {
    var description = '';
    switch(descType) {
      case 'description':
        description = [{
          'value': {
            '#markup': Drupal.hasValue(product.description) ? product.description.html : '',
          }
        }];
        break;
      case 'shortDesc':
        description = Drupal.hasValue(product.short_description) ? product.short_description.html : '';
        break;
    }

    return description;
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
    var imageData = {};
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

    const pager_flag = (thumbnails.length > drupalSettings.alshayaRcs.pdpGalleryPagerLimit) 
      ? 'pager-yes' : 'pager-no';

    return imageData = {
      'pager_flag': pager_flag,
      'sku': child.sku,
      'thumbnails': thumbnails,
    };
  }

})(Drupal, drupalSettings, jQuery);
