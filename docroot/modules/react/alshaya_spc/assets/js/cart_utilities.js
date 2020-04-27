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

  Drupal.alshayaSpc.getProductData = function (sku, callback) {
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
      callback(data);
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

        var data = Drupal.alshayaSpc.storeProductData(
          response.sku,
          response.parent_sku,
          response.title,
          response.link,
          image,
          response.original_price,
          response.configurable_values,
          response.promotions
        );

        callback(data);
      }
    });
  };

  Drupal.alshayaSpc.storeProductData = function (sku, parentSKU, title, url, image, price, options, promotions) {
    var langcode = $('html').attr('lang');
    var key = ['product', langcode, sku].join(':');
    var data = {
      'sku': sku,
      'parentSKU': parentSKU,
      'title': title,
      'url': url,
      'image': image,
      'price': price,
      'options': options,
      'promotions': promotions,
      'created': new Date().getTime(),
    };

    localStorage.setItem(key, JSON.stringify(data));

    // Return as well if required for re-use.
    return data;
  };



})(jQuery, Drupal);

