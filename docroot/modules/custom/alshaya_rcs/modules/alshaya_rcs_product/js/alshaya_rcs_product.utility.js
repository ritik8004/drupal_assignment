/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function (Drupal, drupalSettings, $) {

  /**
   * Local static data store.
   */
  let staticDataStore = {
    cnc_status: {},
    configurableColorData: {},
    attrLabels: {},
    cartItemsStock: {},
    labels: {},
    parent: {},
    configurableCombinations: {},
    configurables: {},
  };

  /**
   * Checks if current user is authenticated or not.
   *
   * @returns {bool}
   *   True if user is authenticated, else false.
   */
  function isUserAuthenticated() {
    return Boolean(window.drupalSettings.userDetails.customerId);
  }

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
   * Sets product data to storage.
   *
   * @param {object} product
   *   The raw product object.
   * @param {string} context
   *   Context in which the product data was fetched.
   */
  window.commerceBackend.setRcsProductToStorage = function setRcsProductToStorage(product, context) {
    product.context = Drupal.hasValue(context) ? context : null;
    globalThis.RcsPhStaticStorage.set('product_data_' + product.sku, product);
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
      var allStorageData = globalThis.RcsPhStaticStorage.getAll();
      var productData = {};
      Object.keys(allStorageData).forEach(function (key) {
        if (key.startsWith('product_data_')) {
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

    var product = globalThis.RcsPhStaticStorage.get('product_data_' + sku);
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
    var sku = product.sku;

    if (typeof staticDataStore.configurables[sku] !== 'undefined') {
      return staticDataStore.configurables[sku];
    }

    var configurables = {};
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

    staticDataStore.configurables[sku] = configurables;

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

    if (Drupal.hasValue(maxSaleQty) && !hideMaxLimitMsg) {
        message = handlebarsRenderer.render('product.order_quantity_limit', {
        message: Drupal.t('Limited to @max_sale_qty per customer', {'@max_sale_qty': maxSaleQty}),
        limit_reached: false,
      });
    }

    return message;
  }

  /**
   * Returns layout value for product.
   *
   * @param {object} product
   *   The processed product object.
   *
   * @returns {string}
   *   The product layout.
   */
  function getLayoutFromContext(product) {
    var layout = drupalSettings.alshayaRcs.pdpLayout;
    switch (product.context) {
      case 'modal':
        layout = 'pdp-magazine' ? 'modal-magazine' : layout;
        break;
    }

    return layout;
  }

  /**
   * Returns the context value for the product.
   *
   * @param {object} product
   *   Processed product object.
   *
   * @returns {string}
   *   Returns the product context value.
   */
  function getContext(product) {
    return product.context;
  }

  // Expose the function to global level.
  window.commerceBackend.getProductContext = getContext;

  /**
   * Gets the variants for the given product entity.
   *
   * @param {object} product
   *   The product entity.
   */
  function getVariantsInfo(product) {
    const info = {};
    var combinations = window.commerceBackend.getConfigurableCombinations(product.sku);
    product.variants.forEach(function (variant) {
      const variantInfo = variant.product;
      const variantSku = variantInfo.sku;
      // Do not process data for OOS variants.
      if (!Drupal.hasValue(combinations.bySku[variantSku])) {
        return;
      }
      const variantParentSku = variantInfo.parent_sku;
      const variantParentProduct = window.commerceBackend.getProductData(null, null, false)[variantParentSku];
      // @todo Add code for commented keys.
      info[variantSku] = {
        cart_image: window.commerceBackend.getCartImage(variant.product),
        cart_title: product.name,
        click_collect: window.commerceBackend.isProductAvailableForClickAndCollect(variantInfo),
        color_attribute: Drupal.hasValue(variantInfo.color_attribute) ? variantInfo.color_attribute : '',
        color_value: Drupal.hasValue(variantInfo.color) ? variantInfo.color : '',
        sku: variantInfo.sku,
        parent_sku: variantParentSku,
        configurableOptions: getVariantConfigurableOptions(product, variant),
        layout: getLayoutFromContext(product),
        context: getContext(product),
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
        gtm_price: globalThis.renderRcsProduct.getFormattedAmount(variantInfo.price_range.maximum_price.final_price.value),
        deliveryOptions: variantInfo.deliveryOptions,
      }

      // Set max sale quantity data.
      info[variantSku].maxSaleQty = 0;
      info[variantSku].max_sale_qty_parent = false;

      if (isQuantityLimitEnabled()) {
        let maxSaleQuantity = variantParentProduct.maxSaleQty;
        // If max sale quantity is available at parent level, we use that.
        if (variantParentProduct.maxSaleQty > 0) {
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
      layout: getLayoutFromContext(product),
      context: getContext(product),
      gallery: null,
      identifier: Drupal.cleanCssIdentifier(product.sku),
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

  function fetchAndProcessCustomAttributes() {
    var response = globalThis.rcsPhCommerceBackend.getDataSynchronous('product-option');
    // Process the data to extract what we require and format it into an object.
    response.data.customAttributeMetadata.items
    && response.data.customAttributeMetadata.items.forEach(function eachCustomAttribute(option) {
      var allOptionsForAttribute = {};
      option.attribute_options.forEach(function (optionValue) {
        allOptionsForAttribute[optionValue.value] = optionValue.label;
      })
      // Set to static storage.
      staticDataStore['attrLabels'][option.attribute_code] = allOptionsForAttribute;
    });
  }

  /**
   * Returns all the custom attributes with values.
   *
   * @returns {object}
   *  Custom attributes with values.
   */
  function getAllCustomAttributes() {
    if (!Drupal.hasValue(staticDataStore['attrLabels'])) {
      fetchAndProcessCustomAttributes();
    }
    return staticDataStore['attrLabels'];
  }

  /**
   * Sorts and returns the configurable attribute values for products.
   *
   * @param {object} configurables
   *   Configurables data.
   *
   * @returns {object}
   *   Configurables data with sorted values.
   */
  function getSortedConfigurableAttributes(configurables) {
    var configurablesClone = JSON.parse(JSON.stringify(configurables));
    var allAttributes = getAllCustomAttributes();
    Object.keys(configurables).forEach(function eachConfigurable(attributeName) {
      var unsortedValues = {};
      var sortedValues = [];
      configurables[attributeName].values.forEach(function eachValue(value) {
        var key = Object.keys(allAttributes[attributeName]).indexOf(String(value.value_id));
        unsortedValues[key] = value;
      });

      Object.keys(unsortedValues).sort().forEach(function eachElement(value, index) {
        sortedValues.push(unsortedValues[value]);
      });
      configurablesClone[attributeName].values = sortedValues;
    });

    return configurablesClone;
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
    if (typeof staticDataStore.configurableCombinations[sku] !== 'undefined') {
      return staticDataStore.configurableCombinations[sku];
    }

    const rawProductData = window.commerceBackend.getProductData(sku, false, false);
    if (!rawProductData) {
      return null;
    }

    var configurables = getConfigurables(rawProductData);
    configurables = getSortedConfigurableAttributes(configurables);
    const configurableCodes = Object.keys(configurables);

    const combinations = {
      by_sku: {},
      attribute_sku: {},
      by_attribute: {},
      combinations: {},
      configurables: configurables,
      firstChild: '',
    };


    rawProductData.variants.forEach(function (variant) {
      const product = variant.product;
      // Don't consider OOS products.
      if (product.stock_status === 'OUT_OF_STOCK') {
        return;
      }
      // Prepare the attributes variable to have key value pair.
      let attributes = [];
      variant.attributes.forEach((item) => {
        attributes[item['code']] = item['value_index'];
      });

      const variantSku = product.sku;
      let attributeVal = null;

      for (let i = 0; i < configurableCodes.length; i++) {
        attributeVal = product[configurableCodes[i]];

        if (typeof attributeVal === 'undefined') {
          // Validate if the configurable code is available in attributes list.
          if (typeof attributes[configurableCodes[i]] === 'undefined') {
            return;
          }
          else {
            attributeVal = attributes[configurableCodes[i]];
          }
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

    staticDataStore.configurableCombinations[sku] = combinations;

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

    var colorAttributeConfig = drupalSettings.alshayaRcs.colorAttributeConfig;
    var supportsMultipleColor = Drupal.hasValue(colorAttributeConfig.support_multiple_attributes);
    var data = {};
    var rawProductData = window.commerceBackend.getProductData(sku, false, false);
    var productType = rawProductData.type_id;

    if (supportsMultipleColor && productType === 'configurable') {
      var configColorAttribute = colorAttributeConfig.configurable_color_attribute;
      var combinations = window.commerceBackend.getConfigurableCombinations(sku);
      var colorLabelAttribute = colorAttributeConfig.configurable_color_label_attribute.replace('attr_', '');
      var colorCodeAttribute = colorAttributeConfig.configurable_color_code_attribute.replace('attr_', '');
      // Translate color attribute option values to the rgb color values &
      // expose the same in Drupal settings to javascript.
      var configurableOptions = rawProductData.configurable_options;

      var variants = {};
      var skuConfigurableOptionsColor = {};

      // Do this mapping for easy access.
      rawProductData.variants.forEach(function (variant) {
        variants[variant.product.sku] = variant;
      })

      configurableOptions.forEach(function (option) {
        option.values.forEach(function (value) {
          if (Drupal.hasValue(combinations.attribute_sku[configColorAttribute]
            && Drupal.hasValue(combinations.attribute_sku[configColorAttribute][value.value_index]))
          ) {
            combinations.attribute_sku[configColorAttribute][value.value_index].forEach(function (variantSku) {
              var colorOptionsList = {
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

        data = {
          sku_configurable_options_color: skuConfigurableOptionsColor,
          sku_configurable_color_attribute: configColorAttribute,
        }
      });

      staticDataStore.configurableColorData[sku] = data;

      return data;
    }
  }

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
    globalThis.rcsPhCommerceBackend.getDataSynchronous('single_product_by_sku', {sku: mainSKU});

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
    var product = await Drupal.alshayaSpc.getProductDataV2(sku, parentSKU);
    var stock = await window.commerceBackend.loadProductStockDataFromCart(sku);

    return {
      stock: stock.qty,
      in_stock: (stock.status === 'IN_STOCK'),
      cnc_enabled: product.cncEnabled,
      max_sale_qty: stock.max_sale_qty,
    };
  };

  /**
   * Sets product stock data in local storage.
   *
   * @param {string} sku
   *   Simple SKU value.
   * @param {object} stockData
   *   Stock data.
   */
  function setProductStockDataToStorage(sku, stockData) {
    var productData = Drupal.alshayaSpc.getLocalStorageProductDataV2(sku);
    productData.stock = {
      in_stock: stockData.status === 'IN_STOCK',
      qty: stockData.qty,
      // We get only enabled products in the API.
      status: 1,
    }

    var langcode = $('html').attr('lang');
    var key = ['product', langcode, sku].join(':');
    // Add product data in local storage with expiration time.
    Drupal.addItemInLocalStorage(
      key,
      productData,
      parseInt(drupalSettings.alshaya_spc.productExpirationTime) * 60,
    );
  }

  /**
   * Triggers stock refresh of the provided skus.
   *
   * @param {object} data
   *   The object of sku values and their requested quantity, like {sku1: qty1}.
   *   Pass null here to refresh stock for all products in the cart.
   * @returns {Promise}
   *   The stock status for all skus.
   */
  window.commerceBackend.triggerStockRefresh = async function (data) {
    const cartData = Drupal.alshayaSpc.getCartData();
    const skus = {};

    // Do not proceed for empty cart.
    if (!(cartData && Drupal.hasValue(cartData.items))) {
      return;
    }

    Object.values(cartData.items).forEach(function (item) {
      const sku = item.sku;
      // If data is null, we want to process for all skus.
      if (data !== null && !Drupal.hasValue(data[sku])) {
        return;
      }

      Drupal.alshayaSpc.getLocalStorageProductData(sku, function (productData) {
        // Check if error is triggered when stock data in local storage is
        // greater than the requested quantity.
        if (data === null || (productData.stock.qty > data[sku])) {
          skus[item.parentSKU] = sku;
        }
      });
    });

    const skuValues = Object.keys(skus);
    if (!skuValues.length) {
      return;
    }

    window.commerceBackend.clearStockStaticCache();
    // As static cache is cleared above, this will now make a new call to the
    // cart API to fetch fresh stock data. Later calls will fetch from this
    // static cache only.
    await window.commerceBackend.loadProductStockDataFromCart(null);
    // Now store the product data to local storage.
    await Object.entries(skus).forEach(async function ([ parentSku, sku ]) {
      var stockData = await window.commerceBackend.loadProductStockDataFromCart(sku);
      setProductStockDataToStorage(sku, stockData);
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
    if (Drupal.hasValue(staticDataStore['attrLabels'][attrName])) {
      return staticDataStore['attrLabels'][attrName][attrValue];
    }

    fetchAndProcessCustomAttributes();

    // Return the label.
    return Drupal.hasValue(staticDataStore['attrLabels'][attrName][attrValue])
      ? staticDataStore['attrLabels'][attrName][attrValue]
      : '';
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
      return Drupal.hasValue(variant.product.hasMedia) ? variant.product : false;
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
    let isProductConfigurable = product.type_id === 'configurable';

    switch (drupalSettings.alshayaRcs.useParentImages) {
      case 'never':
        if (isProductConfigurable) {
          child = getFirstChildWithMedia(product);
        }
        break;

      case 'always':
        if (isProductConfigurable) {
          if (!Drupal.hasValue(product.media_gallery)) {
            child = getFirstChildWithMedia(product);
          }
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
   * Gets the parent SKU for the given SKU.
   *
   * @param {string} mainSku
   *   The main sku. This will be used to fetch the product data from static
   *   storage.
   * @param {string} sku
   *   SKU value.
   *
   * @returns {string}
   *   The parent SKU value.
   */
  function getParentSkuBySku(mainSku, sku) {
    if (Drupal.hasValue(staticDataStore.parent[sku])) {
      return staticDataStore.parent[sku];
    }
    staticDataStore.parent[sku] = null;
    var productData = window.commerceBackend.getProductData(mainSku);
    if (Drupal.hasValue(productData.variants)
      && Drupal.hasValue(productData.variants[sku])
      && Drupal.hasValue(productData.variants[sku].parent_sku)
    ) {
      staticDataStore.parent[sku] = productData.variants[sku].parent_sku;
    }

    return staticDataStore.parent[sku];
  }

  /**
   * Fetches product labels from backend, processes and stores them in storage.
   *
   * @param {string} mainSku
   *   Main sku value.
   */
  async function processAllLabels(mainSku) {
    // If labels have already been fetched for mainSku, they will be available
    // in static storage. Hence no need to process them again.
    if (typeof staticDataStore.labels[mainSku] !== 'undefined') {
      return;
    }

    // Fetch the parent and siblings of the product.
    const products = getSkuSiblingsAndParent(mainSku);
    const productIds = {};
    Object.keys(products).forEach(function (sku) {
      staticDataStore.labels[sku] = [];
      productIds[products[sku].id] = sku;
    });

    // Fetch all sku values, both for the main product and the styled products.
    var allProductsData = window.commerceBackend.getProductData();
    Object.keys(allProductsData).forEach(function eachProduct(productSku) {
      staticDataStore.labels[productSku] = [];
      productIds[allProductsData[productSku].id] = productSku;
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
  }

  /**
   * Gets the labels data for the given product ID.
   *
   * @param mainSku
   *   The sku value.
   *
   * @returns object
   *   The labels data for the given product ID.
   */
  window.commerceBackend.getProductLabelsData = async function getProductLabelsData(mainSku, skuForLabel) {
    await processAllLabels(mainSku);
    // If it is a child product not having any label, we display the labels
    // from the parent.
    var parentSku = getParentSkuBySku(mainSku, skuForLabel);
    if (Drupal.hasValue(parentSku)) {
      Object.assign(staticDataStore.labels[skuForLabel], staticDataStore.labels[parentSku]);
    }

    return staticDataStore.labels[skuForLabel];
  }

  /**
   * Clears static cache for stock data.
   */
  window.commerceBackend.clearStockStaticCache = function clearStockStaticCache() {
    staticDataStore.cartItemsStock = {};
  }

  /**
   * Gets the stock data for cart items.
   *
   * @param {string} sku
   *   SKU value for which stock is to be returned.
   *
   * @returns {Promise}
   *   Returns a promise so that await executes on the calling function.
   */
  window.commerceBackend.loadProductStockDataFromCart = async function loadProductStockDataFromCart(sku) {
    // Load the stock data.
    var cartId = window.commerceBackend.getCartId();
    if (Drupal.hasValue(staticDataStore.cartItemsStock[sku])) {
      return staticDataStore.cartItemsStock[sku];
    }
    var isAuthUser = isUserAuthenticated();
    var authenticationToken = isAuthUser
      ? 'Bearer ' + window.drupalSettings.userDetails.customerToken
      : null;

    return rcsPhCommerceBackend.getData('cart_items_stock', { cartId }, null, null, null, false, authenticationToken).then(function processStock(response) {
      const cartKey = isAuthUser ? 'customerCart' : 'cart';
      // Do not proceed if for some reason there are no cart items.
      if (!response[cartKey].items.length) {
        return;
      }
      response[cartKey].items.forEach(function eachCartItem(cartItem) {
        if (!Drupal.hasValue(cartItem)) {
          return;
        }
        var stockData = null;
        if (cartItem.product.type_id === 'configurable') {
          stockData = cartItem.configured_variant.stock_data;
          stockData.status = cartItem.configured_variant.stock_status;
          staticDataStore.cartItemsStock[cartItem.configured_variant.sku] = stockData;
        }
        else {
          stockData = cartItem.product.stock_data;
          stockData.status = cartItem.product.stock_status;
          staticDataStore.cartItemsStock[cartItem.product.sku] = stockData;
        }
      });

      return staticDataStore.cartItemsStock[sku];
    });
  }

  /**
   * Clears static cache of product data.
   */
  window.commerceBackend.resetStaticStoragePostProductUpdate = function resetStaticStoragePostProductUpdate() {
    staticDataStore.configurableCombinations = {};
    staticDataStore.configurableColorData = {};
    staticDataStore.configurables = {};
    staticDataStore.labels = {};
  }

  // Event listener to update static promotion.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty or event data is not for product.
    if (!Drupal.hasValue(e.detail.result)
      || !Drupal.hasValue(e.detail.result.sku)) {
      return null;
    }

    // Set parent sku value for all the variants.
    var product = e.detail.result;
    if (product.type_id === 'configurable') {
      product.variants.forEach(function eachVariant(variant) {
        variant.parent_sku = product.sku;
      });
    }

    var promotionVal = [];
    if (Drupal.hasValue(product.promotions)) {
      var promotions = product.promotions;
      // Update the promotions attribute based on the requirement.
      promotions.forEach((promotion, index) => {
        promotionVal[index] = {
          promo_web_url: promotion.url,
          text: promotion.label,
          context: promotion.context,
          type: promotion.type,
        }
      });
    }

    product.promotions = promotionVal;
  });
})(Drupal, drupalSettings, jQuery);
