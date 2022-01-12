(function ($, Drupal) {
  'use strict';

  var getProductDataRequests = {};
  Drupal.alshayaSpc = Drupal.alshayaSpc || {};

  Drupal.alshayaSpc.clearCartData = function () {
    window.commerceBackend.removeCartDataFromStorage();
  };

  Drupal.alshayaSpc.getCartData = function () {
    // @todo find better way to get this using commerceBackend.
    var cart_data = Drupal.getItemFromLocalStorage('cart_data');
    if (cart_data && cart_data.cart !== undefined) {
      cart_data = cart_data.cart;
      if (cart_data.cart_id !== null) {
        return cart_data;
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
      data = Drupal.getItemFromLocalStorage(key);
    }
    catch (e) {
      // Do nothing, we will use PDP API to get the info again.
    }

    if (data) {
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

    $.ajax({
      url: Drupal.url('rest/v2/product/' + btoa(sku)) + '?context=cart',
      type: 'GET',
      dataType: 'json',
      beforeSend: function(xmlhttprequest, options) {
        options.requestOrigin = 'getProductData';
        return options;
      },
      success: function (response) {
        getProductDataRequests[sku]['api'] = 'finished';
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

        var data = Drupal.alshayaSpc.storeProductData({
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
  };

  Drupal.alshayaSpc.storeProductData = function (data) {
    var langcode = $('html').attr('lang');
    var key = ['product', langcode, data.sku].join(':');

    // This is to avoid the situation where a child product have more than one
    // Parent products. In this case it will fetch the product name and id of
    // the parent product from the local storage.
    var localStorageData = Drupal.getItemFromLocalStorage(key);
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
    };

    // Add product data in local storage with expiration time.
    Drupal.addItemInLocalStorage(
      key,
      productData,
      parseInt(drupalSettings.alshaya_spc.productExpirationTime) * 60,
    );

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
      // Set analytics data in hidden field.
      Drupal.SpcPopulateDataFromGA();
    }
  }

  Drupal.SpcPopulateDataFromGA = function () {
    // Check if ga is loaded.
    if (typeof window.ga === 'function' && window.ga.loaded && ga.getAll().length) {
      // Use GA function queue.
      ga(function () {
        $('#spc-ga-client-id').val(ga.getAll()[0].get('clientId'));
        $('#spc-ga-tracking-id').val(ga.getAll()[0].get('trackingId'));
      });

      return;
    }

    // Try to read again.
    setTimeout(Drupal.SpcPopulateDataFromGA, 500);
  };

  Drupal.alshayaSpc.getGAData = function () {
    const analytics = {};

    const trackingIdEle = document.getElementById('spc-ga-tracking-id');
    if (trackingIdEle) {
      analytics.trackingId = trackingIdEle.value;
    }

    const clientIdEle = document.getElementById('spc-ga-client-id');
    if (clientIdEle) {
      analytics.clientId = clientIdEle.value;
    }

    return analytics;
  };

  /**
   * Provides extra DataDog contexts.
   */
  document.addEventListener('dataDogContextAlter', (e) => {
    const context = e.detail;
    // These variables should be considered as helpers for troubleshooting but
    // may in some cases not be accurate.
    const uid = drupalSettings.userDetails.customerId;
    if (uid) {
      context.cCustomerId = uid;
    }

    const cartId = window.commerceBackend.getCartId();
    if (cartId) {
      context.cCartId = cartId;
      const cartData = Drupal.alshayaSpc.getCartData();
      if (cartData && typeof cartData.cart_id_int !== 'undefined') {
        context.cCartIdInt = cartData.cart_id_int;
      }
    }
  });

})(jQuery, Drupal);
