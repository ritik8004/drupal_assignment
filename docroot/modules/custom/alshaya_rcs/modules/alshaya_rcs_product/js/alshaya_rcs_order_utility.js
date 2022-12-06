window.commerceBackend = window.commerceBackend || {};

(function orderUtility(Drupal, drupalSettings) {
  /**
   * Gets the label for provided attribute.
   *
   * @param {Object} mainProduct
   *   Main product object, i.e. Configurable product in case of configurable or
   *   Simple in case of simple product.
   * @param {string} attrCode
   *   Product attribute code.
   *
   * @returns {string|Boolean}
   *   Label if available or false if not available.
   */
   function getLabel(mainProduct, attrCode) {
    var label = '';

    // For color attribute, return the configured color label.
    if (Drupal.hasValue(drupalSettings.alshayaColorSplit)
      && drupalSettings.alshayaColorSplit.colorAttribute === attrCode) {
      label = drupalSettings.alshayaColorSplit.colorLabel;
    }
    else {
      Drupal.hasValue(mainProduct.configurable_options)
        && mainProduct.configurable_options.some(function eachOption(option) {
        if (option.attribute_code === attrCode) {
          label = option.label;
          return true;
        }
        return false;
      });
    }

    return label;
  }

  /**
   * Gets the option ID for given attribute code.
   *
   * @param {Object} product
   *   Product object.
   * @param {String} attrCode
   *   Attribute code.
   *
   * @returns {String}
   *   Option Id.
   */
  function getOptionId(product, attrCode) {
    var optionId = null;
    product.configurable_options.some(function eachOption(option) {
      if (attrCode === option.attribute_code) {
        optionId = atob(option.attribute_uid);
        return true;
      }
      return false;
    });

    return optionId;
  }

  /**
   * Fetch the product options.
   *
   * @param {Object} mainProduct
   *   Main product object.
   * @param {Object} product
   *   Current variant object.
   *
   * @returns {Array}
   *   Array of product options in format [{value: value, label:label}].
   */
  window.commerceBackend.getProductOptions = function getProductOptions(mainProduct, product) {
    var options = [];
    if (!Drupal.hasValue(product)) {
      return options;
    }

    Drupal.hasValue(product.attributes) && product.attributes.forEach(function eachAttribute(attr) {
      options.push({
        attribute_id: 'attr_' + attr.code,
        value: attr.label,
        label: getLabel(mainProduct, attr.code),
        option_id: getOptionId(mainProduct, attr.code).toString(),
        option_value: attr.value_index.toString(),
      });
    });

    // Check if color split is enabled.
    if (window.commerceBackend.getProductsInStyle) {
      var colorAttribute = drupalSettings.alshayaColorSplit.colorAttribute;
      if (Drupal.hasValue(product.product[colorAttribute])) {
        var label = window.commerceBackend.getAttributeValueLabel(colorAttribute, product.product[colorAttribute]);
        options.push({
          attribute_id: 'attr_' + colorAttribute,
          value: label,
          label: getLabel(mainProduct, colorAttribute),
          option_id: drupalSettings.psudo_attribute,
          option_value: product.product[colorAttribute],
        });
      }
    }

    return options;
  }
})(Drupal, drupalSettings);
