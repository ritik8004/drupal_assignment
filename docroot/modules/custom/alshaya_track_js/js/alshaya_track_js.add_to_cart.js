(function ($, Drupal) {
  'use strict';

  $('body').once('track-add-to-cart-success').on('product-add-to-cart-success', '.sku-base-form', function (event) {
    Drupal.logViaTrackJs({
     'message': 'Add to cart successful.',
     'postData': event.detail.postData,
    });
  });

  $('body').once('track-add-to-cart-failed').on('product-add-to-cart-failed', '.sku-base-form', function (event) {
    Drupal.logViaTrackJs({
      'message': 'Add to cart failed.',
      'postData': event.detail.postData,
      'error': event.detail.message,
    });
  });

  $('body').once('track-add-to-cart-error').on('product-add-to-cart-error', '.sku-base-form', function (event) {
    Drupal.logViaTrackJs({
      'message': 'Add to cart ajax call failed.',
      'postData': event.detail.postData,
      'error': event.detail.message,
    });
  });

})(jQuery, Drupal);
