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
    configurableCombinations: {},
    configurables: {},
    // This will prevent multiple requests to fetch same product data.
    productDataFromBackend: {},
    recentOrdersData: {},
    orderDetailsData: {},
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
   * Checks if a product is free gift or not.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @return boolean
   */
  window.commerceBackend.isFreeGiftSku = function isFreeGiftSku(product) {
    var isFreeGift = false;
    if (Drupal.hasValue(product.price_range)
      && Drupal.hasValue(product.price_range.maximum_price)
      && Drupal.hasValue(product.price_range.maximum_price.final_price)
    ) {
      var productPrice = parseFloat(product.price_range.maximum_price.final_price.value);
      isFreeGift = productPrice === 0 || productPrice === 0.01;
    }
    return isFreeGift;
  };

  /**
   * Sets product data to storage.
   *
   * @param {object} product
   *   The raw product object.
   * @param {string} context
   *   Context in which the product data was fetched.
   * @param {string} productSku
   *   The product SKU.
   */
  window.commerceBackend.setRcsProductToStorage = function setRcsProductToStorage(product, context, productSku = null) {
    var sku = Drupal.hasValue(productSku) ? productSku : product.sku;
    product.context = Drupal.hasValue(context) ? context : null;
    globalThis.RcsPhStaticStorage.set('product_data_' + sku, product);
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
      var attribute_id = atob(option.attribute_uid);
      // We let the pseudo attribute remain as an integer.
      attribute_id = (attribute_id == drupalSettings.psudo_attribute)
        ? parseInt(attribute_id, 10)
        : attribute_id;

      configurables[option.attribute_code] = {
        attribute_id,
        code: option.attribute_code,
        label: option.label,
        position: option.position,
        values: option.values.map(function (option_value) {
          return {
            label: option_value.store_label,
            value_id: option_value.value_index,
            // Adding this extra attribute because we are doing weight based
            // sorting and we need a fixed attribute name for the index value.
            // @see window.commerceBackend.getSortedAttributeValues().
            value_index: option_value.value_index
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
      let label = productConfigurables[attributeCode].label;
      const optionId = productConfigurables[attributeCode].attribute_id;
      const optionValue = variant.product[attributeCode];
      let value = window.commerceBackend.getAttributeValueLabel(attributeCode, variant.product[attributeCode]);

      // Check if we have a replacement for the attributes.
      if (Drupal.hasValue(drupalSettings.alshayaRcs.configurableFieldReplacements)) {
        const { configurableFieldReplacements } = drupalSettings.alshayaRcs;
        Object.keys(configurableFieldReplacements).forEach(function (field) {
          if (configurableFieldReplacements[field].display_configurable_for === attributeCode &&
            Drupal.hasValue(variant.product[field])
          ) {
            // Override default values.
            attributeCode = field;
            label = configurableFieldReplacements[field].label;
            value = variant.product[field];
          }
        });
      }

      if (Drupal.hasValue(drupalSettings.alshaya_spc.sizeGroupAttribute)
        && attributeCode === drupalSettings.alshaya_spc.sizeGroupAttribute) {
        let sizeGroup = '';
        const sizeGroupAlternates = drupalSettings.alshaya_spc.sizeGroupAlternates;
        Object.keys(sizeGroupAlternates).forEach(function (key) {
          const value = variant.product[key];
          const valueLabel = window.commerceBackend.getAttributeValueLabel(key, value);
          sizeGroup += `${valueLabel}(${sizeGroupAlternates[key]}) `;
        });
        value = sizeGroup;
      }

      variantConfigurableOptions.push({
        attribute_code: `attr_${attributeCode}`,
        attribute_id: `attr_${attributeCode}`,
        label: label,
        option_id: optionId,
        option_value: optionValue,
        value: value,
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
   * @param {object} variantParentProduct
   *   The variant parent.
   */
  function getVariantsInfo(product, variantParentProduct) {
    const info = {};
    product.variants.forEach(function (variant) {
      const variantInfo = variant.product;
      const variantSku = variantInfo.sku;
      const variantParentSku = variantInfo.parent_sku;
      // Use URL from parent if not available in child - we add in variants only for styled products.
      const productUrl = Drupal.hasValue(variantInfo.url_key)
        ? getProductUrls(variantInfo.url_key)
        : getProductUrls(product.url_key);
      // @todo Add code for commented keys.
      info[variantSku] = {
        id: variant.product.id,
        cart_image: window.commerceBackend.getCartImage(variant.product),
        cart_title: product.name,
        click_collect: window.commerceBackend.isProductAvailableForClickAndCollect(variantInfo),
        color_attribute: Drupal.hasValue(variantInfo.color_attribute) ? variantInfo.color_attribute : '',
        color_value: Drupal.hasValue(variantInfo.color) ? variantInfo.color : '',
        fit: Drupal.hasValue(variantInfo.fit) ? variantInfo.fit : '',
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
        priceRaw: globalThis.renderRcsProduct.getFormattedAmount(variantInfo.price_range.maximum_price.regular_price.value),
        promotionsRaw: product.promotions,
        freeGiftPromotion: variantInfo.free_gift_promotion,
        url: productUrl,
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
          info[variantSku].orderLimitMsg = getMaxSaleQtyMessage(maxSaleQuantity);
        }
      }

      var productInfoAlterEvent = new CustomEvent('rcsProductInfoAlter', {
        detail: {
          data: {
            processedProduct: info[variantSku],
            rawProduct: variantInfo
          }
        }
      });
      document.dispatchEvent(productInfoAlterEvent);
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
      freeGiftPromotion: product.free_gift_promotion,
      is_non_refundable: product.non_refundable_products,
      stock: {
        qty: product.stock_data.qty,
        // We get only enabled products in the API.
        status: 1,
        in_stock: product.stock_status === 'IN_STOCK',
      },
    };

    // Set max sale quantity.
    setMaxSaleQty(productData, product.stock_data.max_sale_qty);

    if (productData.type === 'configurable') {
      productData.configurables = getConfigurables(product);
      productData.variants = getVariantsInfo(product, productData);
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

    var productInfoAlterEvent = new CustomEvent('rcsProductInfoAlter', {
      detail: {
        data: {
          processedProduct: productData,
          rawProduct: product
        }
      }
    });
    document.dispatchEvent(productInfoAlterEvent);

    return productData;
  }

  /**
   * Set max sale quantity.
   *
   * @param {object} productData
   *   The product data.
   *
   * @param {integer} maxSaleQty
   *   The quantity.
   */
  function setMaxSaleQty(productData, maxSaleQty) {
    productData.maxSaleQty = 0;
    if (!isQuantityLimitEnabled()) {
      return;
    }

    if (maxSaleQty > 0) {
      productData.maxSaleQty = maxSaleQty;
      productData.max_sale_qty_parent = false;
      productData.orderLimitMsg = getMaxSaleQtyMessage(maxSaleQty);
    }
  }

  function fetchAndProcessCustomAttributes() {
    var response = globalThis.RcsPhStaticStorage.get('product_options');

    // Process the data to extract what we require and format it into an object.
    response && Object.entries(response).forEach(function eachCustomAttribute([attrCode, options]) {
      var allOptionsForAttribute = {};
      // Proceed only if `attribute_options` exists.
      if (Drupal.hasValue(options)) {
        options.forEach(function (optionValue) {
          allOptionsForAttribute[optionValue.value] = optionValue;
        })
      }
      // Set to static storage.
      staticDataStore['attrLabels'][attrCode] = allOptionsForAttribute;
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
    Object.keys(configurables).forEach(function eachConfigurable(attributeName) {
      var sortedValues = window.commerceBackend.getSortedAttributeValues(configurables[attributeName].values, attributeName);
      if (Drupal.hasValue(sortedValues)) {
        configurablesClone[attributeName].values = sortedValues;
      }
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
        combinations.by_sku[variantSku][configurableCodes[i]] = attributeVal ? attributeVal.toString() : '';

        combinations.attribute_sku[configurableCodes[i]] = typeof combinations.attribute_sku[configurableCodes[i]] !== 'undefined'
          ? combinations.attribute_sku[configurableCodes[i]]
          : {};

        combinations.attribute_sku[configurableCodes[i]][attributeVal] = typeof combinations.attribute_sku[configurableCodes[i]][attributeVal] !== 'undefined'
          ? combinations.attribute_sku[configurableCodes[i]][attributeVal]
          : [];
        combinations.attribute_sku[configurableCodes[i]][attributeVal].push(variantSku);
      }
    });

    combinations.firstChild = rawProductData.firstChild;
    if (!(Drupal.hasValue(combinations.firstChild))
      && Drupal.hasValue(combinations.attribute_sku)
    ) {
      var firstChild = Object.entries(combinations.attribute_sku)[0];
      firstChild = Object.entries(firstChild[1]);
      combinations.firstChild = firstChild[0][1][0];
    }

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
      });

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
              RcsEventManager.fire('alshayaRcsAlterSwatch', {
                detail: {
                  sku,
                  colorOptionsList,
                  variantSku,
                  variant: variants[variantSku],
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

    }

    staticDataStore.configurableColorData[sku] = data;
    return data;
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
   * @param {boolean} loadStyles
   *   (optional) Indicates if styled product need to be loaded.
   */
  window.commerceBackend.getProductDataFromBackend = async function (sku, parentSKU = null, loadStyles = true) {
    var mainSKU = Drupal.hasValue(parentSKU) ? parentSKU : sku;
    staticDataStore.productDataFromBackend[mainSKU] = Drupal.hasValue(staticDataStore.productDataFromBackend[mainSKU])
      ? staticDataStore.productDataFromBackend[mainSKU]
      : {};
    if (Drupal.hasValue(staticDataStore.productDataFromBackend[mainSKU][sku])) {
      return staticDataStore.productDataFromBackend[mainSKU][sku];
    }
    // Get the product data.
    // The product will be fetched and saved in static storage.
    staticDataStore.productDataFromBackend[mainSKU][sku] = globalThis.rcsPhCommerceBackend.getData('product_by_sku', {sku: mainSKU}).then(async function productsFetched(response){
      if (Drupal.hasValue(window.commerceBackend.getProductsInStyle)) {
        await window.commerceBackend.getProductsInStyle(response, loadStyles);
      }
      window.commerceBackend.setRcsProductToStorage(response, null, mainSKU);
      await window.commerceBackend.processAndStoreProductData(mainSKU, sku, 'productInfo');
    });

    return staticDataStore.productDataFromBackend[mainSKU][sku];
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
    if (!(Drupal.hasValue(staticDataStore['attrLabels'])
      || Drupal.hasValue(staticDataStore['attrLabels'][attrName]))) {
      fetchAndProcessCustomAttributes();
    }

    // Return the label.
    if (Drupal.hasValue(staticDataStore['attrLabels'][attrName])
      && Drupal.hasValue(staticDataStore['attrLabels'][attrName][attrValue])) {
      return staticDataStore['attrLabels'][attrName][attrValue]['label'];
    }

    return '';
  };

  /**
   * Get the first child with media.
   *
   * @param {object} product
   *   The raw product object.
   *
   * @return {object|null}
   *   The first child raw product object or null if no child with media found.
   *
   * @see \Drupal\alshaya_acm_product\SkuImagesManager::getFirstChildWithMedia()
   */
  const getFirstChildWithMedia = function (product) {
    var firstChild;
    // If we have first child sku defined, try to check if the first child variant
    // has media or not.
    if (Drupal.hasValue(product.firstChild)) {
      firstChild = product.variants.find(function findFirstChildWithMedia(variant) {
        return product.firstChild === variant.product.sku && variant.product.hasMedia ? variant.product : false;
      });
    }
    // If the first child variant does not have media,
    // look for other variants that does.
    if (!Drupal.hasValue(firstChild)) {
      firstChild = product.variants.find(function findAnyChildWithMedia(variant) {
        return variant.product.hasMedia ? variant.product : false;
      });
    }

    return Drupal.hasValue(firstChild) ? firstChild.product : null;
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

  // Expose the function to global level.
  window.commerceBackend.getSkuForGallery = getSkuForGallery;

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
    return Drupal.hasValue(galleryProduct.media) ? galleryProduct.media[0] : null;
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
    if (!Drupal.hasValue(product)) {
      return null;
    }
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
   * @return {object}
   *   The price object.
   */
  window.commerceBackend.getPrices = function (product, formatted) {
    return {
      price : formatted ? globalThis.renderRcsProduct.getFormattedAmount(product.price_range.maximum_price.regular_price.value) : product.price_range.maximum_price.regular_price.value,
      finalPrice: formatted ? globalThis.renderRcsProduct.getFormattedAmount(product.price_range.maximum_price.final_price.value) : product.price_range.maximum_price.final_price.value,
      percent_off: product.price_range.maximum_price.discount.percent_off,
    };
  };

  /**
   * Clears static cache for stock data.
   */
  window.commerceBackend.clearStockStaticCache = function clearStockStaticCache() {
    staticDataStore.cartItemsStock = {};
  }

  /**
   * Returns list of product attribute with labels.
   *
   * @param {object} data
   *   Graphql response for additional product attributes.
   *
   * @returns {object}
   *  List of product attributes values and labels.
   */
  function processProductAttributes (data) {
    // Process custom attributes metadata.
    var attributesMetadata = [];
    data.customAttributeMetadata.items.forEach(function eachValue(value) {
      attributesMetadata[value.attribute_code] = {};
      for (let i = 0; i < value.attribute_options.length; i++) {
        let option = value.attribute_options[i];
        attributesMetadata[value.attribute_code][option.value] = option.label;
      }
    });
    // Get labels for product attributes from custom attribute metadata.
    var productAttributeValues = data.products.items[0];
    var productAttributes = {};
    Object.entries(productAttributeValues).forEach(function (value) {
      if (Drupal.hasValue(value[1])) {
        // Split comma separated product attributes.
        let value_arr = value[1].split(',');
        productAttributes[value[0]] = [];
        value_arr.forEach(function (option) {
          productAttributes[value[0]].push(attributesMetadata[value[0]][option]);
        });
      }
    });
    return productAttributes;
  };

  /**
   * Get additional product attributes.
   *
   * @param {string} sku
   *   SKU value for which additional attributes is to be returned.
   * @param {object} attributesVariable
   *   Product attributes lists.
   *
   * @returns {object}
   *   List of product attributes values and labels.
   */
  window.commerceBackend.getAdditionalAttributes = async function getAdditionalAttributes(sku, attributesVariable) {
    // Get product attributes and custom attribute metadata and labels.
    var response = await rcsPhCommerceBackend.getData('product_additional_attributes', {
      sku: sku ,
      attributes: attributesVariable,
    })
    var productAttributes = Drupal.hasValue(response.data)
      ? processProductAttributes(response.data)
      : [];
    return productAttributes;
  };

  /**
   * Clears static cache of product data.
   */
  window.commerceBackend.resetStaticStoragePostProductUpdate = function resetStaticStoragePostProductUpdate() {
    staticDataStore.configurableCombinations = {};
    staticDataStore.configurableColorData = {};
    staticDataStore.configurables = {};
    staticDataStore.labels = {};
  }

  /**
   * Get the processed price for render.
   *
   * @param {Object} price
   *   Price object.
   *
   * @returns {Object}
   *   Processed price object which can be used for rendering via handlebars.
   */
  window.commerceBackend.getPriceForRender = function getPriceForRender(price) {
    let currencyConfig = drupalSettings.alshaya_spc.currency_config;
    // @todo Work on from/to prices for products.
    const item = {
      display_mode: 'simple',
    };
    item.discount = price.price_range.maximum_price.discount;
    item.regular_price = {
      value: price.price_range.maximum_price.regular_price.value,
      currency_code: currencyConfig.currency_code,
      currency_code_position: currencyConfig.currency_code_position,
      decimal_points: currencyConfig.decimal_points,
    };
    item.final_price = {
      value: price.price_range.maximum_price.final_price.value,
      currency_code: currencyConfig.currency_code,
      currency_code_position: currencyConfig.currency_code_position,
      decimal_points: currencyConfig.decimal_points,
    };
    return item;
  }

  /**
   * Check if the product is in stock.
   *
   * @param {object} entity
   *   The product entity.
   *
   * @returns {Boolean}
   *   True if product is in stock, else false.
   */
  window.commerceBackend.isProductInStock = function isProductInStock(entity) {
    if (entity.stock_status === 'OUT_OF_STOCK') {
      return false;
    }

    // @todo Check for free gifts when checking the variants.
    // For configurable product, if all variants are OOS, then we consider the
    // product to be OOS.
    if (entity.type_id === 'configurable') {
      const isAnyVariantInStock = entity.variants.some((variant) =>
        variant.product.stock_status === 'IN_STOCK'
      );
      if (!isAnyVariantInStock) {
        return false;
      }
    }

    return true;
  }

/**
 * Get SKU based on attribute option id.
 *
 * @param {string} $sku
 *   The parent sku value.
 * @param {string} attribute
 *   Attribute to search for.
 * @param {Number} option_id
 *   Option id for selected attribute.
 *
 * @return {string}
 *   SKU value matching the attribute option id.
 */
window.commerceBackend.getChildSkuFromAttribute = function getChildSkuFromAttribute(sku, attribute, option_id) {
  const combinations = window.commerceBackend.getConfigurableCombinations(sku);

  if (!Drupal.hasValue(combinations.attribute_sku) ) {
    Drupal.alshayaLogger('warning', 'No combination available for any attributes in SKU @sku', {
      '@sku': sku
    });
    return null;
  }
  if (!Drupal.hasValue(combinations.attribute_sku[attribute][option_id])) {
    Drupal.alshayaLogger('warning', 'No combination available for attribute @attribute and option @option_id for SKU @sku', {
      '@attribute': attribute,
      '@option_id': option_id,
      '@sku': sku
    });
    return null;
  }

  return combinations.attribute_sku[attribute][option_id][0];
}


/**
 * Utility function to get the the weight key based sorted attributes.
 *
 * @param {array} optionValues
 *   An array containing all the configurable attributes.
 * @param {string} attributeName
 *   The machine name of the option attribute.
 *
 * @returns {array}
 *   Array containing the weight key based sorted configurable attributes.
 */
window.commerceBackend.getSortedAttributeValues = function getSortedAttributeValues(optionValues, attributeName) {
  // Get the list of all the attributes and their values.
  var allAttributes = getAllCustomAttributes();
  var unsortedValues = {};
  var sortedValues  = [];
  optionValues.forEach(function eachValue(value) {
    if (Drupal.hasValue(value.value_index) && Drupal.hasValue(allAttributes[attributeName])) {
      var weight = allAttributes[attributeName][String(value.value_index)]
        ? allAttributes[attributeName][String(value.value_index)].weight
        : 99999;
      unsortedValues[weight] = value;
    }
  });

  if (Drupal.hasValue(unsortedValues)) {
    Object.keys(unsortedValues).sort(function (a, b) { return a - b; }).forEach(function eachElement(value,) {
      sortedValues.push(unsortedValues[parseInt(value, 10)]);
    });
  }

  return sortedValues;
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
        variant.product.parent_sku = product.sku;
      });

      // Sort the configurable options based on config in Drupal.
      var sortedConfigurableOptions = [];
      var sortedConfigurableAttributes = [];

      // Logic to sort the sequencing of configurable attributes as per
      // the sequence mentioned in configurable form settings.
      const configurableAttributeWeights = drupalSettings.configurableAttributes;
      if (Drupal.hasValue(configurableAttributeWeights) && Drupal.hasValue(product.configurable_options)) {
        // Add all the attributes as per config.
        configurableAttributeWeights.forEach(function eachConfigurableAttribute(attribute) {
          product.configurable_options.forEach(function eachConfigurableOption(option) {
            if (option.attribute_code === attribute
              && !sortedConfigurableAttributes.includes(attribute)) {
              // Get the sorted attribute values.
              option.values = window.commerceBackend.getSortedAttributeValues(option.values, option.attribute_code);
              sortedConfigurableOptions.push(option);
              sortedConfigurableAttributes.push(attribute);
            }
          });
        });

        // Add all the attributes which are there in the product but not in
        // config in the end.
        product.configurable_options.forEach(function eachConfigurableOption(option) {
          if (!sortedConfigurableAttributes.includes(option.attribute_code)) {
            // Get the sorted attribute values.
            option.values = window.commerceBackend.getSortedAttributeValues(option.values, option.attribute_code);
            sortedConfigurableOptions.push(option);
            sortedConfigurableAttributes.push(option.attribute_code);
          }
        });
      }

      product.configurable_options = sortedConfigurableOptions;
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
