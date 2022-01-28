/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

/**
 * Local static data store.
 */
let staticDataStore = {
  cnc_status: {},
  configurableColorData: {},
};

/**
 * Utility function to check if an item is an object or not.
 *
 * @param {mixed} item
 *   The item to check.
 *
 * @returns {Boolean}
 *   Whether the item is an object or not.
 */
function isObject(item) {
  return (item && typeof item === 'object' && !Array.isArray(item));
}

/**
 * Deep merge two objects.
 *
 * @param {object} target
 *   The target item.
 * @param {object} source
 *   The source item.
 *
 * @return {object}
 *   The merged item.
 */
function mergeDeep(target, source) {
  let output = Object.assign({}, target);
  if (isObject(target) && isObject(source)) {
    Object.keys(source).forEach(key => {
      if (isObject(source[key])) {
        if (!(key in target)) {
          Object.assign(output, { [key]: source[key] });
        } else {
          output[key] = mergeDeep(target[key], source[key]);
        }
      } else {
        Object.assign(output, { [key]: source[key] });
      }
    });
  return output;
  }
}

/**
 * Gets the required data for rcs_product.
 *
 * @param {string} sku
 *   The product sku value.
 * @param {string} productKey
 *   The product view mode.
 * @param {Boolean} processed
 *   Whether we require the processed product data or not.
 *
 * @returns {Object}
 *    The product data.
 */
window.commerceBackend.getProductData = function (sku, productKey, processed) {
  if (typeof sku === 'undefined' || !sku) {
    var allStorageData = RcsPhStaticStorage.getAll();
    var productData = {};
    Object.keys(allStorageData).forEach(function (key) {
      if (key.startsWith('product_')) {
        if (typeof processed === 'undefined' || processed) {
          productData[allStorageData[key].sku] = processProduct(allStorageData[key]);
        }
        else {
          productData[allStorageData[key].sku] = allStorageData[key];
        }
      }
    });

    return productData;
  }

  var product = RcsPhStaticStorage.get('product_' + sku);
  if (product) {
    if (typeof processed === 'undefined' || processed) {
      return processProduct(product);
    }
    else {
      return product;
    }
  }

  return null;
}

/**
 * Get a heirarchy of combinations for an object.
 *
 * @param {object} options
 *   The combination object. Eg. {size: "X", color: "Y"}
 *
 * @returns {object}
 *   The hierarchy of combinations. Eg:
 *   {
 *     size: {
 *       "X": {
 *         color: {
 *           "Y"
 *         }
 *       }
 *     }
 *   }
 */
function getCombinationHierarchy(options) {
  const optionsClone = Object.assign({}, options);
  var combination = {};

  var [code, value] = Object.entries(optionsClone)[0]

  delete optionsClone[code];
  combination[code] = typeof combination[code] !== 'undefined' ? combination[code] : {};
  combination[code][value] = Object.keys(optionsClone).length > 0 ? getCombinationHierarchy(optionsClone) : 1;

  return combination;
}

/**
 * Returns a string containing the selected product combinations.
 *
 * @param {object} configurables
 *   The object of configurable attribute name and value.
 *
 * @returns {string}
 *   The string containing attribute names and values separated by delimiter.
 */
function getSelectedCombination(configurables) {
  var combination = '';

  Object.entries(configurables).forEach(function ([key, value]) {
    if (typeof value === 'undefined' || !value || value === '') {
      return;
    }
    combination = combination + key + '|' + value + '||';
  });

  return combination;
}

/**
 * Gets the configurables for the given product entity.
 *
 * @param {object} product
 *   The product entity.
 */
function getConfigurables(product) {
  const staticKey = product.sku + '_configurables';

  if (typeof staticDataStore[staticKey] !== 'undefined') {
    return staticDataStore[staticKey];
  }

  const configurables = {};
  product.configurable_options.forEach(function (option) {
    configurables[option.attribute_code] = {
      attribute_id: parseInt(atob(option.attribute_uid), 10),
      code: option.attribute_code,
      label: option.label,
      position: option.position,
      values: option.values.map(function (option_value) {
        return {
          label: option_value.store_label,
          value_id: option_value.value_index
        };
      })
    };
  });

  staticDataStore[staticKey] = configurables;

  return configurables;
}

/**
 * Get the configurable options for the given variant.
 *
 * @param product
 *  The main product object.
 * @param {object} variant
 *   The variant object.
 *
 * @return {array}
 *   The array of variant configurable options.
 */
function getVariantConfigurableOptions(product, variant) {
  const productConfigurables = getConfigurables(product);
  const variantConfigurableOptions = [];

  Object.keys(productConfigurables).forEach(function (attributeCode) {
    variantConfigurableOptions.push({
      attribute_id: `attr_${attributeCode}`,
      label: productConfigurables[attributeCode].label,
      value: window.commerceBackend.getAttributeValueLabel(attributeCode, variant.product[attributeCode]),
      value_id: variant.product[attributeCode],
    });
  });

  return variantConfigurableOptions;
}

/**
 * Get the product urls for the different languages.
 *
 * @param {string} urlKey
 *   The url key value of the product.
 * @param {string} langcode (optional)
 *   The specific langcode to get the product url for.
 */
function getProductUrls(urlKey, langcode = null) {
  const urls = {};
  drupalSettings.alshayaSpc.languages.forEach(function (language) {
    urls[language] = `/${language}/${urlKey}.html`;
  });

  return langcode ? urls[langcode] : urls;
}

/**
 * Gets the max sale quantity for the product.
 *
 * @param {object} product
 *   The product object.
 *
 * @returns {Number}
 *   The max sale quantity for the product or 0.
 */
function getMaxSaleQuantity(product) {
  return Drupal.hasValue(product.stock_data.max_sale_qty) ? product.stock_data.max_sale_qty : 0;
}

/**
 * Get to know if quantity limit is enabled.
 *
 * @returns {Boolean}
 *   true if quantity limit is enabled, else false.
 */
function isQuantityLimitEnabled() {
  return drupalSettings.alshayaRcs.quantityLimitEnabled;
}

/**
 * Get the max sale quantity message.
 *
 * @param {Number} maxSaleQty
 *   The max sale quantity value.
 *
 * @returns {String}
 *   The message.
 */
function getMaxSaleQtyMessage(maxSaleQty) {
  const hideMaxLimitMsg = drupalSettings.alshayaRcs.hide_max_qty_limit_message;
  let message = '';

  if (Drupal.hasValue(maxSaleQty) && maxSaleQty > 0 && !hideMaxLimitMsg) {
      message = handlebarsRenderer.render('product.order_quantity_limit', {
      message: Drupal.t('Limited to @max_sale_qty per customer', {'@max_sale_qty': maxSaleQty}),
      limit_reached: false,
    });
  }

  return message;
}

/**
 * Gets the variants for the given product entity.
 *
 * @param {object} product
 *   The product entity.
 */
function getVariantsInfo(product) {
  const info = {};

  product.variants.forEach(function (variant) {
    const variantInfo = variant.product;
    const variantSku = variantInfo.sku;
    // @todo Add code for commented keys.
    info[variantSku] = {
      cart_image: window.commerceBackend.getCartImage(variant.product),
      cart_title: product.name,
      click_collect: window.commerceBackend.isProductAvailableForClickAndCollect(variantInfo),
      color_attribute: Drupal.hasValue(variantInfo.color_attribute) ? variantInfo.color_attribute : '',
      color_value: Drupal.hasValue(variantInfo.color) ? variantInfo.color : '',
      sku: variantInfo.sku,
      parent_sku: variantInfo.parent_sku,
      configurableOptions: getVariantConfigurableOptions(product, variant),
      // @todo Fetch layout dynamically.
      layout: drupalSettings.alshayaRcs.pdpLayout,
      gallery: '',
      stock: {
        qty: variantInfo.stock_data.qty,
        // We get only enabled products in the API.
        status: 1,
        in_stock: variantInfo.stock_status === 'IN_STOCK',
      },
      // @todo Implement this.
      description: '',
      price: globalThis.rcsPhRenderingEngine.computePhFilters(variantInfo, 'price'),
      finalPrice: globalThis.renderRcsProduct.getFormattedAmount(variantInfo.price_range.maximum_price.final_price.value),
      priceRaw: globalThis.renderRcsProduct.getFormattedAmount(variantInfo.price_range.maximum_price.final_price.value),
      promotionsRaw: product.promotions,
      // @todo Add free gift promotion value here.
      freeGiftPromotion: [],
      url: getProductUrls(variantInfo.url_key),
    }

    // Set max sale quantity data.
    info[variantSku].maxSaleQty = 0;
    info[variantSku].max_sale_qty_parent = false;

    if (isQuantityLimitEnabled()) {
      let maxSaleQuantity = product.maxSaleQty;
      // If max sale quantity is available at parent level, we use that.
      if (product.maxSaleQty > 0) {
        info[variantSku].max_sale_qty_parent = true;
      }

      // If order limit is not set for parent then get the order limit for each
      // variant.
      maxSaleQuantity = maxSaleQuantity > 0 ? maxSaleQuantity : getMaxSaleQuantity(variantInfo);
      if (maxSaleQuantity > 0) {
        info[variantSku].maxSaleQty = maxSaleQuantity;
        info[variantSku].stock.maxSaleQty = maxSaleQuantity;
        info[variantSku].stock.orderLimitMsg = getMaxSaleQtyMessage(maxSaleQuantity);
      }
    }
  });

  return info;
}

/**
 * Process the product so that it has the same structure as drupalSettings
 * productInfo key.
 *
 * @param {object} product
 *   The product object from the API response.
 *
 * @returns {Object}
 *    The processed product data.
 */
function processProduct(product) {
  var productData = {
    id: product.id,
    sku: product.sku,
    type: product.type_id,
    gtm_attributes: product.gtm_attributes,
    gallery: null,
    identifier: window.commerceBackend.cleanCssIdentifier(product.sku),
    cart_image: window.commerceBackend.getCartImage(product),
    cart_title: product.name,
    url: getProductUrls(product.url_key, drupalSettings.path.currentLanguage),
    price: globalThis.rcsPhRenderingEngine.computePhFilters(product, 'price'),
    finalPrice: globalThis.renderRcsProduct.getFormattedAmount(product.price_range.maximum_price.final_price.value),
    priceRaw: globalThis.renderRcsProduct.getFormattedAmount(product.price_range.maximum_price.regular_price.value),
    promotionsRaw: product.promotions,
    // @todo Add free gift promotion value here.
    freeGiftPromotion: [],
    is_non_refundable: product.non_refundable_products,
    stock: {
      qty: product.stock_data.qty,
      // We get only enabled products in the API.
      status: 1,
      in_stock: product.stock_status === 'IN_STOCK',
    },
  };

  let maxSaleQty = 0;

  if (productData.type === 'simple') {
    maxSaleQty = isQuantityLimitEnabled() ? getMaxSaleQuantity(product) : maxSaleQty;
  }
  else if (productData.type === 'configurable') {
    productData.configurables = getConfigurables(product);
    productData.variants = getVariantsInfo(product);

    if (isQuantityLimitEnabled()) {
      maxSaleQty = getMaxSaleQuantity(product);
    }
  }

  if (maxSaleQty > 0) {
    productData.maxSaleQty = maxSaleQty;
    productData.max_sale_qty_parent = false;
    productData.orderLimitMsg = getMaxSaleQtyMessage(maxSaleQty);
  }

  // Add general bazaar voice data to product data if present.
  if (typeof drupalSettings.alshaya_bazaar_voice !== 'undefined') {
    var bazaarVoiceData = drupalSettings.alshaya_bazaar_voice;
    bazaarVoiceData.product = {
      url: getProductUrls(product.url_key, drupalSettings.path.currentLanguage),
      title: product.name,
      image_url: '',
    }
    productData.alshaya_bazaar_voice = drupalSettings.alshaya_bazaar_voice;
  }

  return productData;
}

/**
 * Gets the configurable combinations for the given sku.
 *
 * @param {string} sku
 *   The sku value.
 *
 * @returns {object|null}
 *   The object containing the configurable combinations for the given sku.
 *   Returns null if no product is found.
 */
window.commerceBackend.getConfigurableCombinations = function (sku) {
  var staticKey = 'product' + sku + '_configurableCombinations';
  if (typeof staticDataStore[staticKey] !== 'undefined') {
    return staticDataStore[staticKey];
  }

  var productData = window.commerceBackend.getProductData(sku);
  if (!productData) {
    return null;
  }

  const configurables = productData.configurables;
  // @todo The configurables should be sorted.
  const configurableCodes = Object.keys(configurables);

  const combinations = {
    by_sku: {},
    attribute_sku: {},
    by_attribute: {},
    combinations: {},
    configurables: configurables,
    firstChild: '',
  };

  const rawProductData = window.commerceBackend.getProductData(sku, false, false);

  rawProductData.variants.forEach(function (variant) {
    const product = variant.product;
    const variantSku = product.sku;
    let attributeVal = null;

    for (let i = 0; i < configurableCodes.length; i++) {
      attributeVal = product[configurableCodes[i]];

      if (typeof attributeVal === 'undefined') {
        return;
      }

      combinations.by_sku[variantSku] = typeof combinations.by_sku[variantSku] !== 'undefined'
        ? combinations.by_sku[variantSku]
        : {};
      combinations.by_sku[variantSku][configurableCodes[i]] = attributeVal;

      combinations.attribute_sku[configurableCodes[i]] = typeof combinations.attribute_sku[configurableCodes[i]] !== 'undefined'
        ? combinations.attribute_sku[configurableCodes[i]]
        : {};

      combinations.attribute_sku[configurableCodes[i]][attributeVal] = typeof combinations.attribute_sku[configurableCodes[i]][attributeVal] !== 'undefined'
        ? combinations.attribute_sku[configurableCodes[i]][attributeVal]
        : [];
      combinations.attribute_sku[configurableCodes[i]][attributeVal].push(variantSku);
    }
  });

  var firstChild = Object.entries(combinations.attribute_sku)[0];
  firstChild = Object.entries(firstChild[1]);
  combinations.firstChild = firstChild[0][1][0];

  // @todo: Add check for simple product.
  Object.keys(combinations.by_sku).forEach(function (sku) {
    const combinationString = getSelectedCombination(combinations.by_sku[sku]);
    combinations.by_attribute[combinationString] = sku;
  });

  var nestedCombination = {};
  Object.keys(combinations.by_sku).forEach(function (sku) {
    const combinationHierarchy = getCombinationHierarchy(combinations.by_sku[sku]);
    nestedCombination = mergeDeep(nestedCombination, combinationHierarchy);
  });

  combinations.combinations = nestedCombination;

  // Mapping.
  combinations.bySku = combinations.by_sku;
  combinations.byAttribute = combinations.by_attribute;

  staticDataStore[staticKey] = combinations;

  return combinations;
}

/**
 * Check if product is available for click and collect.
 *
 * @param {object} product
 *   The raw product entity.
 *
 * @returns {Boolean}
 *   Returns true if CnC is enabled else false.
 *
 * @see alshaya_acm_product_available_click_collect().
 */
window.commerceBackend.isProductAvailableForClickAndCollect = function (product) {
  if (Drupal.hasValue(staticDataStore.cnc_status[product.sku])) {
    return staticDataStore.cnc_status[product.sku];
  }
  // Product could be either available for ship to store or for reserve and
  // collect. In both cases click and collect option will be considered as
  // available.
  // Magento provides for 2 for disabled and 1 for enabled.
  staticDataStore.cnc_status[product.sku] = (drupalSettings.alshaya_click_collect.status === 'enabled')
    && (parseInt(product.ship_to_store, 10) === 1 || parseInt(product.reserve_and_collect, 10) === 1);

  return staticDataStore.cnc_status[product.sku];
}

/**
 * Prepares a string for use as a CSS identifier (element, class, or ID name).
 *
 * This is the JS implementation of \Drupal\Component\Utility\Html::getClass().
 * Link below shows the syntax for valid CSS identifiers (including element
 * names, classes, and IDs in selectors).
 *
 * @param {string} identifier
 *   The string to clean.
 *
 * @returns {string}
 *   The cleaned string which can be used as css class/id.
 *
 * @see http://www.w3.org/TR/CSS21/syndata.html#characters
 */
window.commerceBackend.cleanCssIdentifier = function (identifier) {
  let cleanedIdentifier = identifier;

  // In order to keep '__' to stay '__' we first replace it with a different
  // placeholder after checking that it is not defined as a filter.
  cleanedIdentifier = cleanedIdentifier
                        .replaceAll('__', '##')
                        .replaceAll(' ', '-')
                        .replaceAll('_', '-')
                        .replaceAll('/', '-')
                        .replaceAll('[', '-')
                        .replaceAll(']', '')
                        .replaceAll('##', '__');

  // Valid characters in a CSS identifier are:
  // - the hyphen (U+002D)
  // - a-z (U+0030 - U+0039)
  // - A-Z (U+0041 - U+005A)
  // - the underscore (U+005F)
  // - 0-9 (U+0061 - U+007A)
  // - ISO 10646 characters U+00A1 and higher
  // We strip out any character not in the above list.
  cleanedIdentifier = cleanedIdentifier.replaceAll(/[^\u{002D}\u{0030}-\u{0039}\u{0041}-\u{005A}\u{005F}\u{0061}-\u{007A}\u{00A1}-\u{FFFF}]/gu, '');

  // Identifiers cannot start with a digit, two hyphens, or a hyphen followed by a digit.
  cleanedIdentifier = cleanedIdentifier.replace(/^[0-9]/, '_').replace(/^(-[0-9])|^(--)/, '__');

  return cleanedIdentifier.toLowerCase();
}

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
async function getProductLabelsData (sku) {
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

/**
 * Get the labels data for the selected SKU.
 *
 * @param {object} product
 *   The product wrapper jquery object.
 * @param {string} sku
 *   The sku for which labels is to be retreived.
 * @param {string} mainSku
 *   The main sku for the product being displayed.
 */
function renderProductLabels(product, sku, mainSku) {
  getProductLabelsData(mainSku).then(function (labelsData) {
    globalThis.rcsPhRenderingEngine.render(
      drupalSettings,
      'product-labels',
      {
        sku,
        mainSku,
        type: 'pdp',
        labelsData,
        product,
      },
    );
  });
}

/**
 * Renders the gallery for the given SKU.
 *
 * @param {object} product
 *   The jQuery product object.
 * @param {string} layout
 *   The layout value.
 * @param {string} productGallery
 *   The gallery for the product.
 * @param {string} sku
 *   The SKU value.
 * @param {string} parentSku
 *   The parent SKU value if exists.
 */
window.commerceBackend.updateGallery = async function (product, layout, productGallery, sku, parentSku) {
  const mainSku = typeof parentSku !== 'undefined' ? parentSku : sku;
  const productData = window.commerceBackend.getProductData(mainSku, null, false);
  const viewMode = product.parents('.entity--type-node').attr('data-vmode');

  if (typeof parentSku === 'undefined') {
    rawProduct = productData;
  }
  else {
    Object.values(productData.variants).forEach(function (productVariant) {
      if (sku === productVariant.product.sku) {
        rawProduct = productVariant.product;
      }
    });
  }

  // Maps gallery value from backend to the appropriate filter.
  let galleryType = null;
  switch (drupalSettings.alshayaRcs.pdpLayout) {
    case 'pdp-magazine':
      galleryType = drupalSettings.alshayaRcs.pdpGalleryType === 'classic' ? 'classic-gallery' : 'magazine-gallery';
      break;
  }

  const gallery = globalThis.rcsPhRenderingEngine
    .render(
      drupalSettings,
      galleryType,
      {
        galleryLimit: viewMode === 'modal' ? 'modal' : 'others',
        // The simple SKU.
        sku,
      },
      { },
      // rawProduct,
      productData,
      drupalSettings.path.currentLanguage,
      null,
    );

  if (gallery === '' || gallery === null) {
    return;
  }

  // Here we render the product labels asynchronously.
  // If we try to do it synchronously, then javascript moves on to other tasks
  // while the labels are fetched from the API.
  // This causes discrepancy in the flow, since in V1 the updateGallery()
  // executes completely in one flow.
  renderProductLabels(product, sku, mainSku);

  if (jQuery(product).find('.gallery-wrapper').length > 0) {
    // Since matchback products are also inside main PDP, when we change the variant
    // of the main PDP we'll get multiple .gallery-wrapper, so we are taking only the
    // first one which will be of main PDP to update main PDP gallery only.
    jQuery(product).find('.gallery-wrapper').first().replaceWith(gallery);
  }
  else {
    jQuery(product).find('#product-zoom-container').replaceWith(gallery);
  }

  if (layout === 'pdp-magazine') {
    // Set timeout so that original behavior attachment is not affected.
    setTimeout(function () {
      Drupal.behaviors.magazine_gallery.attach(document);
      Drupal.behaviors.pdpVideoPlayer.attach(document);
    }, 1);
  }
  else {
    // Hide the thumbnails till JS is applied.
    // We use opacity through a class on parent to ensure JS get's applied
    // properly and heights are calculated properly.
    jQuery('#product-zoom-container', product).addClass('whiteout');
    setTimeout(function () {
      Drupal.behaviors.alshaya_product_zoom.attach(document);
      Drupal.behaviors.alshaya_product_mobile_zoom.attach(document);

      // Show thumbnails again.
      jQuery('#product-zoom-container', product).removeClass('whiteout');
    }, 1);
  }
};

/**
 * Gets the configurable color details.
 *
 * @param {string} sku
 *   The sku value.
 *
 * @returns {object}
 *   The configurable color details.
 *
 * @see https://github.com/acquia-pso/alshaya/blob/6.7.0/docroot/modules/custom/alshaya_acm_product/alshaya_acm_product.module#L1513
 */
window.commerceBackend.getConfigurableColorDetails = function (sku) {
  if (Drupal.hasValue(staticDataStore.configurableColorData[sku])) {
    return staticDataStore.configurableColorData[sku];
  }

  const colorAttributeConfig = drupalSettings.alshayaRcs.colorAttributeConfig;
  const isSupportsMultipleColor = Drupal.hasValue(colorAttributeConfig.configurable_color_attribute);
  const configColorAttribute = colorAttributeConfig.configurable_color_attribute;

  if (isSupportsMultipleColor) {
    const colorLabelAttribute = colorAttributeConfig.configurable_color_label_attribute.replace('attr_', '');
    const colorCodeAttribute = colorAttributeConfig.configurable_color_code_attribute.replace('attr_', '');
    // Translate color attribute option values to the rgb color values &
    // expose the same in Drupal settings to javascript.
    const combinations = window.commerceBackend.getConfigurableCombinations(sku);
    const rawProductData = window.commerceBackend.getProductData(sku, false, false);
    const configurableOptions = rawProductData.configurable_options;

    const variants = {};
    const skuConfigurableOptionsColor = {};

    // Do this mapping for easy access.
    rawProductData.variants.forEach(function (variant) {
      variants[variant.product.sku] = variant;
    })

    configurableOptions.forEach(function (option) {
      option.values.forEach(function (value) {
        if (Drupal.hasValue(combinations.attribute_sku[configColorAttribute][value.value_index])) {
          combinations.attribute_sku[configColorAttribute][value.value_index].forEach(function (variantSku) {
            const colorOptionsList = {
              display_label: window.commerceBackend.getAttributeValueLabel(option.attribute_code, variants[variantSku].product[colorLabelAttribute]),
              swatch_type: 'RGB',
              display_value: variants[variantSku].product[colorCodeAttribute],
            };

            // The behavior is same as
            // hook_alshaya_acm_product_pdp_swath_type_alter().
            RcsEventManager.fire('alshayaRcsAlterPdpSwatch', {
              detail: {
                sku,
                colorOptionsList,
                variantSku,
              }
            });

            skuConfigurableOptionsColor[value.value_index] = colorOptionsList;
          });
        }
      });
    });

    const data = {
      sku_configurable_options_color: skuConfigurableOptionsColor,
      sku_configurable_color_attribute: configColorAttribute,
    }
    staticDataStore.configurableColorData[sku] = data;

    return data;
  }
}
