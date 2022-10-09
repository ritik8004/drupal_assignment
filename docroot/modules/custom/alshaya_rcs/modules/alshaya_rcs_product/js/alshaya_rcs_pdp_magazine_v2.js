(function (Drupal, drupalSettings, $) {
  // Call event after entity load and process product data.
  RcsEventManager.addListener('alshayaPageEntityLoaded', async function pageEntityLoaded(e) {
    var mainProduct = e.detail.entity;
    if (Drupal.hasValue(window.commerceBackend.getProductsInStyle)) {
      mainProduct = await window.commerceBackend.getProductsInStyle(mainProduct);
    }
    var processedProduct = [];
    var configurableCombinations = [];
    // Prepare data for productinfo to be used in new pdp layout.
    const productInfoV1 = window.commerceBackend.getProductData(mainProduct.sku, false, true);
    const productInfoV2 = processProductMagV2(mainProduct, productInfoV1);
    processedProduct[mainProduct.sku] = {...productInfoV1, ...productInfoV2};
    if (mainProduct.type_id === 'configurable') {
      configurableCombinations[mainProduct.sku] = processConfigurableCombinations(mainProduct.sku);
    }
    // Pass product data into pdp layout react component.
    window.alshayaRenderPdpMagV2(processedProduct, configurableCombinations);
  });

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
      stockStatus: product.stock_status === 'IN_STOCK',
      title: {'label': product.name},
      rawGallery: updateGallery(product, product.name),
      additionalAttributes: (Object.keys(product.description.additional_attributes).length > 0) ? product.description.additional_attributes : '',
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
    // Set swatch data if available.
    if (Drupal.hasValue(combinations.configurables[colorDetails.sku_configurable_color_attribute])) {
      combinations.configurables[colorDetails.sku_configurable_color_attribute]['isSwatch'] = true;
      combinations.configurables[colorDetails.sku_configurable_color_attribute].values.forEach(function(option, key){
        switch(colorDetails.sku_configurable_options_color[option.value_id].swatch_type) {
          case 'RGB':
            combinations.configurables[colorDetails.sku_configurable_color_attribute].values[key].swatch_color = colorDetails.sku_configurable_options_color[option.value_id].display_value;
            combinations.configurables[colorDetails.sku_configurable_color_attribute].values[key].swatch_type = colorDetails.sku_configurable_options_color[option.value_id].swatch_type;
            break;
          case 'Image':
            combinations.configurables[colorDetails.sku_configurable_color_attribute].values[key].swatch_image = colorDetails.sku_configurable_options_color[option.value_id].display_value;
            combinations.configurables[colorDetails.sku_configurable_color_attribute].values[key].swatch_type = colorDetails.sku_configurable_options_color[option.value_id].swatch_type;
            break;
          default:
            combinations.configurables[colorDetails.sku_configurable_color_attribute].values[key].swatch_type = colorDetails.sku_configurable_options_color[option.value_id].swatch_type;
        }
      });
    }

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
      info[variantInfo.sku] = processedVariants[variantInfo.sku];
      info[variantInfo.sku]['rawGallery'] = updateGallery(variantInfo, product.name);
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

    return {
      'pager_flag': pager_flag,
      'sku': child.sku,
      'thumbnails': thumbnails,
    };
  }

})(Drupal, drupalSettings, jQuery);
