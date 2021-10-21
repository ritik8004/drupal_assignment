(function ($, Drupal) {
  'use strict';

  var getProductDataRequests = {};
  Drupal.alshayaSpc = Drupal.alshayaSpc || {};

  Drupal.alshayaSpc.clearCartData = function () {
    window.commerceBackend.removeCartDataFromStorage();
  };

  Drupal.alshayaSpc.getCartData = function () {
    // @todo find better way to get this using commerceBackend.
    var cart_data = localStorage.getItem('cart_data');
    if (cart_data) {
      cart_data = JSON.parse(cart_data);
      if (cart_data && cart_data.cart !== undefined) {
        cart_data = cart_data.cart;
        if (cart_data.cart_id !== null) {
          return cart_data;
        }
      }
    }

    return null;
  };

  Drupal.alshayaSpc.getCartDataAsUrlQueryString = function (cartData) {
    var data = {};
    data.products = [];
    data.cart = {
      'subtotal': cartData.totals['subtotal_incl_tax'],
      'applied_rules': cartData.appliedRules,
    };

    for (var i in cartData.items) {
      data.products.push({
        'sku': cartData.items[i].sku,
        'quantity': cartData.items[i].qty,
        'price': cartData.items[i].price,
      })
    }

    return $.param(data);
  };

  Drupal.alshayaSpc.getLocalStorageProductData = function(sku, callback, extraData) {
    var langcode = $('html').attr('lang');
    var key = ['product', langcode, sku].join(':');

    var data = null;

    try {
      data = JSON.parse(localStorage.getItem(key));
    }
    catch (e) {
      // Do nothing, we will use PDP API to get the info again.
    }

    var expireTime = drupalSettings.alshaya_spc.productExpirationTime * 60 * 1000;
    var currentTime = new Date().getTime();
    if (data !== null && ((currentTime - data.created) > expireTime)) {
      try {
        callback(data, extraData);
      }
      catch(e) {
        Drupal.logJavascriptError('getLocalStorageProductData fail', e, GTM_CONSTANTS.CART_ERRORS);
      }
      return true;
    }
    return false;
  }

  Drupal.alshayaSpc.removeLocalStorageProductData = function (sku) {
    drupalSettings.alshayaSpc.languages.forEach(function (langcode) {
      var key = ['product', langcode, sku].join(':');
      localStorage.removeItem(key);
    });
  }

  Drupal.alshayaSpc.getProductData = function (sku, callback, extraData) {
    extraData = extraData || {};

    // If we can get data successfully from local storage we don't want to
    // make api request.
    if (Drupal.alshayaSpc.getLocalStorageProductData(sku, callback, extraData)) {
      return;
    }

    // If api request is already strated, Store the request, callback and
    // extraData info in an object, to trigger callback function call on
    // success of api request.
    if (getProductDataRequests[sku] && getProductDataRequests[sku]['api'] === 'requested') {
      getProductDataRequests[sku]['callbacks'][callback] = {
        callback: callback,
        extraData: extraData,
      };
      return;
    }

    // Before we make api request, store the request, callback and extraData
    // info in an object, to avoid making duplicate api request for same sku.
    // and trigger callback function call on success of api request.
    if (!getProductDataRequests[sku]) {
      getProductDataRequests[sku] = {
        'api': 'requested',
        'callbacks': {},
      }
      getProductDataRequests[sku]['callbacks'][callback] = {
        callback: callback,
        extraData: extraData,
      };
    }

    const parentSKU = Drupal.hasValue(extraData.parentSKU) ? extraData.parentSKU : null;
    window.commerceBackend.getProductDataFromBackend(sku, parentSKU).then(function () {
      getProductDataRequests[sku]['api'] = 'finished';
    });
  };

  Drupal.alshayaSpc.storeProductData = function (data) {
    var langcode = $('html').attr('lang');
    var key = ['product', langcode, data.sku].join(':');

    // This is to avoid the situation where a child product have more than one
    // Parent products. In this case it will fetch the product name and id of
    // the parent product from the local storage.
    var localStorageData = JSON.parse(localStorage.getItem(key));
    if (localStorageData != null && localStorageData.gtmAttributes !== undefined) {
      data.gtmAttributes.id = localStorageData.gtmAttributes.id ? localStorageData.gtmAttributes.id : data.gtmAttributes.id;
      data.gtmAttributes.name = localStorageData.gtmAttributes.name ? localStorageData.gtmAttributes.name : data.gtmAttributes.name;
      data.parentSKU = localStorageData.parentSKU ? localStorageData.parentSKU : data.parentSKU;
      data.title = localStorageData.title ? localStorageData.title : data.title;
      data.url = localStorageData.url ? localStorageData.url : data.url;
    }
    var productData = {
      'id': data.id,
      'sku': data.sku,
      'parentSKU': data.parentSKU,
      'title': data.title,
      'url': data.url,
      'image': data.image,
      'price': data.price,
      'options': data.options,
      'promotions': data.promotions,
      'freeGiftPromotion': data.freeGiftPromotion || null,
      'maxSaleQty': data.maxSaleQty,
      'maxSaleQtyParent': data.maxSaleQtyParent,
      'gtmAttributes': data.gtmAttributes,
      'isNonRefundable': data.isNonRefundable,
      'created': new Date().getTime(),
      'stock': data.stock,
      'cncEnabled': data.cncEnabled,
    };

    localStorage.setItem(key, JSON.stringify(productData));

    // Return as well if required for re-use.
    return data;
  };

  Drupal.alshayaSpc.getAttributeVal = function (attrResp, attrKey) {
    for (var i in attrResp) {
       if (attrResp[i].key === attrKey && attrResp[i].value === '1') {
         return attrResp[i].value;
       }
    }
    return null;
  };

  // To get the name of grouping attribute
  Drupal.alshayaSpc.getGroupingAttribute = function (attrResp, attrKey) {
    for (var i in attrResp) {
      if (attrResp[i].key === attrKey) {
        return attrResp[i].value;
      }
    }
    return null;
  };

  // To get the options value for grouping attribute.
  Drupal.alshayaSpc.getGroupingOptions = function (attrResp) {
    var groupAttribute = Drupal.alshayaSpc.getGroupingAttribute(attrResp, 'grouping_attributes');
    if (groupAttribute === null) {
      return null;
    }

    let groupingOptions = [];
    const attrLabel = Drupal.t('@attr_label', { '@attr_label': groupAttribute });
    for (var i in attrResp) {
      if (attrResp[i].key === groupAttribute) {
        groupingOptions = [{
          label: attrLabel,
          value: attrResp[i].value,
        }];
        return groupingOptions;
      }
    }
    return groupingOptions;
  };

  /**
   * Processes product data and stores it to local storage.
   *
   * @param {string} viewMode
   *   The product view mode, eg. matchback.
   * @param {object} productData
   *   An object containing some processed product data.
   */
  window.commerceBackend.processAndStoreProductData = function (parentSku, variantSku, viewMode) {
    var productInfo = window.commerceBackend.getProductData(parentSku, viewMode);
    var options = [];
    var productUrl = productInfo.url;
    var price = productInfo.priceRaw;
    var promotions = productInfo.promotionsRaw;
    var freeGiftPromotion = productInfo.freeGiftPromotion;
    var productDataSKU = parentSku;
    var parentSKU = parentSku;
    var maxSaleQty = productInfo.maxSaleQty;
    var maxSaleQtyParent = productInfo.max_sale_qty_parent;
    var gtmAttributes = productInfo.gtm_attributes;
    var isNonRefundable = productInfo.is_non_refundable;
    var productName = productInfo.cart_title;
    var productImage = productInfo.cart_image;
    var stock = productInfo.stock;
    var cncEnabled = productInfo.click_collect;

    if (productInfo.type === 'configurable') {
      var productVariantInfo = productInfo['variants'][variantSku];
      productDataSKU = variantSku;
      price = productVariantInfo.priceRaw;
      parentSKU = productVariantInfo.parent_sku;
      promotions = productVariantInfo.promotionsRaw;
      freeGiftPromotion = productVariantInfo.freeGiftPromotion || freeGiftPromotion;
      options = productVariantInfo.configurableOptions;
      maxSaleQty = productVariantInfo.maxSaleQty;
      maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

      if (typeof productVariantInfo.url !== 'undefined') {
        var langcode = $('html').attr('lang');
        productUrl = productVariantInfo.url[langcode];
      }
      gtmAttributes.price = productVariantInfo.gtm_price || price;
      stock = Drupal.hasValue(productVariantInfo.stock) ? productVariantInfo.stock : stock;
      cncEnabled = Drupal.hasValue(productVariantInfo.click_collect) ? productVariantInfo.click_collect : cncEnabled;
    }
    else if (typeof productInfo.group !== 'undefined') {
      var productVariantInfo = productInfo.group[parentSku];
      price = productVariantInfo.priceRaw;
      parentSKU = productVariantInfo.parent_sku;
      promotions = productVariantInfo.promotionsRaw;
      freeGiftPromotion = productVariantInfo.freeGiftPromotion || freeGiftPromotion;
      if (typeof productVariantInfo.grouping_options !== 'undefined'
        && productVariantInfo.grouping_options.length > 0) {
        options = productVariantInfo.grouping_options;
      }
      maxSaleQty = productVariantInfo.maxSaleQty;
      maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

      var langcode = $('html').attr('lang');
      productUrl = productVariantInfo.url[langcode];
      gtmAttributes.price = productVariantInfo.gtm_price || price;
    }

    // Store proper variant sku in gtm data now.
    gtmAttributes.variant = productDataSKU;
    Drupal.alshayaSpc.storeProductData({
      sku: productDataSKU,
      parentSKU: parentSKU,
      title: productName,
      url: productUrl,
      image: productImage,
      price: price,
      options: options,
      promotions: promotions,
      freeGiftPromotion: freeGiftPromotion,
      maxSaleQty: maxSaleQty,
      maxSaleQtyParent: maxSaleQtyParent,
      gtmAttributes: gtmAttributes,
      isNonRefundable: isNonRefundable,
      stock: stock,
      cncEnabled,
    });
  }

  Drupal.behaviors.spcCartUtilities = {
    attach: function(context) {
      // Ajax success to trigger callbacks once api request from
      // Drupal.alshayaSpc.getProductData finished.
      $(document).once('getProductData-success').ajaxSuccess(function( event, xhr, settings ) {
        if (!settings.hasOwnProperty('requestOrigin') || settings.requestOrigin !== 'getProductData') {
          return;
        }

        // Check if the xhr status is successful.
        // ref: docroot/libraries/jqueryvalidate/lib/jquery.form.js:623
        if (xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) {
          var data = xhr.responseJSON;
          if (Object.keys(getProductDataRequests[data.sku]['callbacks']).length > 0) {
            for (var key in getProductDataRequests[data.sku]['callbacks']) {
              var callbackObj = getProductDataRequests[data.sku]['callbacks'][key];
              Drupal.alshayaSpc.getLocalStorageProductData(data.sku, callbackObj.callback, callbackObj.extraData);
            }
            // Delete the object for the sku, as we don't need this data on the
            // page any more.
            delete getProductDataRequests[data.sku];
          }
        }
      });
    }
  }

})(jQuery, Drupal);
