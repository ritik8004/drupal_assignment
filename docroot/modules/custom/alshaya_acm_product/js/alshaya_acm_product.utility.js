/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

(function (Drupal, $, drupalSettings) {

  /**
   * Gets the required data for acq_product.
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
    var key = productKey === 'undefined' || !productKey ? 'productInfo' : productKey;
    if (typeof sku === 'undefined' || sku === null) {
      return drupalSettings[key];
    }

    if (typeof drupalSettings[key] === 'undefined' || typeof drupalSettings[key][sku] === 'undefined') {
      return null;
    }

    return drupalSettings[key][sku];
  }

  /**
   * Gets the configurable combinations for the given sku.
   *
   * @param {string} sku
   *   The sku value.
   *
   * @returns {object}
   *   The object containing the configurable combinations for the given sku.
   */
  window.commerceBackend.getConfigurableCombinations = function (sku) {
    return drupalSettings.configurableCombinations[sku];
  }

  /**
   * Gets the configurable color details.
   *
   * @param {string} sku
   *   The sku value.
   *
   * @returns {object}
   *   The configurable color details.
   */
  window.commerceBackend.getConfigurableColorDetails = function (sku) {
    var data = {};
    if (drupalSettings.sku_configurable_color_attribute) {
      data.sku_configurable_color_attribute = drupalSettings.sku_configurable_color_attribute;
    }
    if (drupalSettings.sku_configurable_options_color) {
      data.sku_configurable_options_color = drupalSettings.sku_configurable_options_color;
    }
    return data;
  }

  /**
   * Fetch the product data from backend.
   *
   * This is just a helper method for Drupal.alshayaSpc.getProductData() and
   * Drupal.alshayaSpc.getProductDataV2().
   * Do not invoke directly.
   * This is an async function.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} parentSKU
   *   (optional) The parent sku value.
   * @param {boolean} loadStyles
   *   (optional) Indicates if styled product need to be loaded.
   */
  window.commerceBackend.getProductDataFromBackend = function (sku, parentSKU = null, loadStyles = true) {
    var mainParentSKU = Drupal.hasValue(parentSKU) ? parentSKU : null;
    return $.ajax({
      url: Drupal.url('rest/v2/product/' + btoa(sku)) + '?context=cart',
      type: 'GET',
      dataType: 'json',
      beforeSend: function(xmlhttprequest, options) {
        options.requestOrigin = 'getProductData';
        return options;
      },
      success: function (response) {
        var image = '';
        if (response.extra_data !== undefined
          && response.extra_data['cart_image'] !== undefined
          && response.extra_data['cart_image']['url'] !== undefined) {
          image = response.extra_data['cart_image']['url'];
        }

        let attrOptions = response.configurable_values;
        if (attrOptions.length < 1
          && response.grouping_attribute_with_swatch !== undefined
          && response.grouping_attribute_with_swatch) {
          attrOptions = Drupal.alshayaSpc.getGroupingOptions(response.attributes);
        }

        var parentSKU = mainParentSKU || response.parent_sku || response.sku;

        Drupal.alshayaSpc.storeProductData({
          id: response.id,
          sku: response.sku,
          parentSKU: parentSKU,
          title: response.title,
          url: response.link,
          image: image,
          price: response.original_price,
          options: attrOptions,
          promotions: response.promotions,
          freeGiftPromotion: response.freeGiftPromotion || null,
          maxSaleQty: response.max_sale_qty,
          maxSaleQtyParent: response.max_sale_qty_parent,
          isNonRefundable: Drupal.alshayaSpc.getAttributeVal(response.attributes, 'non_refundable_products'),
          gtmAttributes: response.gtm_attributes,
          extraInfo: Drupal.hasValue(response.extraInfo) ? response.extraInfo : {},
        });
      }
    });
  }

  /**
   * Get the stock status of the given sku.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} parentSKU
   *   The parent sku value.
   */
  window.commerceBackend.getProductStatus = async function (sku, parentSKU) {
    // Bypass CloudFlare to get fresh stock data.
    // Rules are added in CF to disable caching for urls having the following
    // query string.
    // The query string is added since same APIs are used by MAPP also.
    return $.ajax({
      url: Drupal.url(`rest/v1/product-status/${btoa(sku)}`),
      data: { _cf_cache_bypass: '1' }
    }).then(function (response) {
      let stock = null;
      if (!Drupal.hasValue(response) || Drupal.hasValue(response.error)) {
        // Do nothing.
      } else {
        stock = response;
      }

      return stock;
    });
  }

  /**
   * Triggers stock refresh of the provided skus.
   *
   * @param {object} data
   *   The object of sku values and their requested quantity, like {sku1: qty1}.
   * @param function
   *   Function to Call Drupal API.
   */
  window.commerceBackend.triggerStockRefresh = function (data) {
    var params = new URLSearchParams();
    params.append('skus_quantity', JSON.stringify(data));
    params.append('action', 'refresh stock');

    var returnVal = navigator.sendBeacon(
      Drupal.url('spc/checkout-event'),
      params,
    );

    if (!returnVal) {
      logger.error('Error occurred while triggering checkout event refresh stock.');
    }
  }

  /**
   * This function does not have any implementation for V2 since for V2 we
   * do a call to Drupal to get the stock data.
   *
   * @param {string} sku
   *   SKU value for which stock is to be returned.
   *
   * @returns {Promise}
   *   Returns a promise so that await executes on the calling function.
   */
  window.commerceBackend.loadProductStockDataFromCart = async function loadProductStockDataFromCart(sku) {
    return true;
  }

  /**
   * Function to clear static cache. Has implementation only for V3.
   */
  window.commerceBackend.clearStockStaticCache = function clearStockStaticCache() {
    return null;
  }
})(Drupal, jQuery, drupalSettings);
