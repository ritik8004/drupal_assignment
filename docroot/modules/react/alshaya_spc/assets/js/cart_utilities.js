(function ($, Drupal) {
  'use strict';

  var getProductDataRequests = {};
  Drupal.alshayaSpc = Drupal.alshayaSpc || {};

  Drupal.alshayaSpc.clearCartData = function () {
    localStorage.removeItem('cart_data');
  };

  Drupal.alshayaSpc.getCartData = function () {
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
    if (data !== null && ((currentTime - data.created) < expireTime)) {
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
      url: Drupal.url('rest/v1/product/' + sku) + '?context=cart',
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

