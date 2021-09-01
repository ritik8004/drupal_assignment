/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

/**
 * Local static data store.
 */
staticDataStore = {};

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
  }
  return output;
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
      attribute_id: atob(option.attribute_uid),
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
 *  The product entity.
 * @param {object} variant
 *   The variant object.
 *
 * @return {array}
 *   The array of variant configurable options.
 */
function getVariantConfigurableOptions(product, variantAttributes) {
  const productConfigurables = getConfigurables(product);

  return variantAttributes.map(function (attribute) {
    return {
      attribute_id: attribute.code,
      label: productConfigurables[attribute.code].label,
      value: attribute.label,
      value_id: attribute.value_index,
    }
  });
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
    // @todo Add code for commented keys.
    info[variantInfo.sku] = {
      // cart_image: '',
      // cart_title: '',
      click_collect: window.commerceBackend.isProductAvailableForClickAndCollect(variant),
      // color_attribute: '',
      // color_value: '',
      sku: variantInfo.sku,
      parent_sku: product.sku,
      configurableOptions: getVariantConfigurableOptions(product, variant.attributes),
      identifier: window.commerceBackend.cleanCssIdentifier(variantInfo.sku),
      // @todo Fetch layout dynamically.
      layout: 'classic',
      gallery: '',
      stock: {
        qty: variantInfo.stock_data.qty,
        // We get only enabled products in the API.
        status: 1,
        maxSaleQty: variantInfo.stock_data.max_sale_qty,
      },
      // @todo Implement this.
      description: '',
      price: rcsPhRenderingEngine.computePhFilters(variantInfo, 'price'),
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
    sku: product.sku,
    type: product.type_id,
    gtm_attributes: product.gtm_attributes,
    gallery: null,
  };

  if (productData.type === 'configurable') {
    productData.configurables = getConfigurables(product);
    productData.variants = getVariantsInfo(product);
  }

  return productData;
}

/**
 * Gets the required data for rcs_product.
 *
 * @param {string} sku
 *   The product sku value.
 *
 * @returns {Object|null}
 *    The processed product data else null if no product is found.
 */
window.commerceBackend.getProductData = function (sku) {
  var product = RcsPhStaticStorage.get('product_' + sku);
  if (product) {
    return processProduct(product);
  }

  return null;
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

  productData.variants = Object.keys(productData.variants).map(function (variantSku) {
    const variant = productData.variants[variantSku];
    const variantConfigurableAttributes = {};
    variant.configurableOptions.forEach(function (variantConfigurableOption) {
      variantConfigurableAttributes[variantConfigurableOption.attribute_id] = variantConfigurableOption.value_id;
    })

    for (var i = 0; i < configurableCodes.length; i++) {
      if (typeof variantConfigurableAttributes[configurableCodes[i]] === 'undefined') {
        delete(productData.variants[variantSku]);
        return;
      }
      var attributeVal = variantConfigurableAttributes[configurableCodes[i]];

      combinations.by_sku[variantSku] = typeof combinations.by_sku[variantSku] !== 'undefined'
        ? combinations.by_sku[variantSku]
        : {};
      combinations.by_sku[variantSku][configurableCodes[i]] = attributeVal;

      combinations.attribute_sku[configurableCodes[i]] = typeof combinations.attribute_sku[configurableCodes[i]] !== 'undefined'
        ? combinations.attribute_sku[configurableCodes[i]]
        : {};
      combinations.attribute_sku[configurableCodes[i]][attributeVal] = variantSku;
    }
  });

  var firstChild = Object.entries(combinations.attribute_sku)[1];
  firstChild = Object.entries(firstChild[1]);
  combinations.firstChild = firstChild[0][1];

  // @todo: Add check for simple product.
  // @todo Add sorting for the configurable options based on weights.
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
  // Product could be either available for ship to store or for reserve and
  // collect. In both cases click and collect option will be considered as
  // available.
  // Magento provides for 2 for disabled and 1 for enabled.

  return (drupalSettings.alshaya_click_collect.status === 'enabled')
    && (parseInt(product.ship_to_store, 10) === 1 || parseInt(product.reserve_and_collect, 10) === 1);
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
