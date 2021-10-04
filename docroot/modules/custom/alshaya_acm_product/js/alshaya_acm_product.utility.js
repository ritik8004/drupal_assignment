(function (Drupal, jQuery) {
  window.commerceBackend = window.commerceBackend || {};

  /**
   * Fetch the product data from backend.
   *
   * @param {string} sku
   *   The sku value.
   * @param {string} parentSKU
   *   (optional) The parent sku value.
   */
  window.commerceBackend.getProductDataFromBackend = function (sku, parentSKU = null) {
    return jQuery.ajax({
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

        var parentSKU = response.parent_sku !== null
          ? response.parent_sku
          : response.sku;

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
    return await jQuery.ajax({
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
})(Drupal, jQuery);
