(function ($, Drupal) {
  'use strict';

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

  Drupal.alshayaSpc.getProductData = function (sku, callback, extraData = {}) {
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
    if (data !== null && data.created - currentTime < expireTime) {
      callback(data, extraData);
      return;
    }

    var apiResponse = null;
    $.ajax({
      url: Drupal.url('rest/v1/product/' + sku) + '?context=cart',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        var image = '';

        if (response.extra_data !== undefined
          && response.extra_data['cart_image'] !== undefined
          && response.extra_data['cart_image']['url'] !== undefined) {
          image = response.extra_data['cart_image']['url'];
        }

        var parentSKU = response.parent_sku !== null
          ? response.parent_sku
          : response.sku;
        var data = Drupal.alshayaSpc.storeProductData({
          sku: response.sku,
          parentSKU: parentSKU,
          title: response.title,
          url: response.link,
          image: image,
          price: response.original_price,
          options: response.configurable_values,
          promotions: response.promotions,
          maxSaleQty: response.max_sale_qty,
          maxSaleQtyParent: response.max_sale_qty_parent,
          gtmAttributes: response.gtm_attributes,
        });

        callback(data, extraData);
      }
    });
  };

  Drupal.alshayaSpc.storeProductData = function (data) {
    var langcode = $('html').attr('lang');
    var key = ['product', langcode, data.sku].join(':');
    var productData = {
      'sku': data.sku,
      'parentSKU': data.parentSKU,
      'title': data.title,
      'url': data.url,
      'image': data.image,
      'price': data.price,
      'options': data.options,
      'promotions': data.promotions,
      'maxSaleQty': data.maxSaleQty,
      'maxSaleQtyParent': data.maxSaleQtyParent,
      'gtmAttributes': data.gtmAttributes,
      'created': new Date().getTime(),
    };

    localStorage.setItem(key, JSON.stringify(productData));

    // Return as well if required for re-use.
    return data;
  };



})(jQuery, Drupal);

