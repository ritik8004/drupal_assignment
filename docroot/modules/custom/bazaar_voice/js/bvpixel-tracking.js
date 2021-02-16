/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.bvpixel_tracking = {
    attach: function (context, settings) {
      var order_from_storage = localStorage.getItem('bvpixel_order_id');
      // Return if same order.
      // Else remove order from local storage if new order.
      if (order_from_storage != null) {
        if (order_from_storage == drupalSettings.bvpixel_order_details.order_id) {
          return;
        }
        else {
          localStorage.removeItem('bvpixel_order_id');
        }
      }
      var productsArray = [];
      for (var key in drupalSettings.bvpixel_order_details.products) {
        var obj = drupalSettings.bvpixel_order_details.products[key];
        productsArray.push(obj);
      }
      var TransactionData = {
        orderId: drupalSettings.bvpixel_order_details.order_id,
        city: drupalSettings.bvpixel_order_details.city,
        country: drupalSettings.bvpixel_order_details.country,
        email: drupalSettings.bvpixel_order_details.email,
        locale: drupalSettings.bvpixel_order_details.locale,
        nickname: drupalSettings.bvpixel_order_details.nickname,
        userId: drupalSettings.bvpixel_order_details.customer_id,
        tax: drupalSettings.bvpixel_order_details.tax,
        shipping: drupalSettings.bvpixel_order_details.shipping,
        total: drupalSettings.bvpixel_order_details.total,
        currency: drupalSettings.bvpixel_order_details.currency,
        items: productsArray,
      };
      window.bvCallback = function (BV) {
        BV.pixel.trackTransaction(TransactionData);

        // Store transaction info in local storage.
        localStorage.setItem('bvpixel_order_id', TransactionData.orderId);
      };
    }
  };
})(jQuery, Drupal, drupalSettings);
