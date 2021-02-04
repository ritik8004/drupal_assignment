/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.bvpixel_tracking = {
    attach: function (context, settings) {
      var itemsArray = [];
      for (var key in drupalSettings.bvpixel_order_details.items) {
        var obj = drupalSettings.bvpixel_order_details.items[key];
        itemsArray.push(obj);
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
        items: itemsArray,
      };
      window.bvCallback = function (BV) {
        BV.pixel.trackTransaction(TransactionData);
      };
    }
  };
})(jQuery, Drupal, drupalSettings);
