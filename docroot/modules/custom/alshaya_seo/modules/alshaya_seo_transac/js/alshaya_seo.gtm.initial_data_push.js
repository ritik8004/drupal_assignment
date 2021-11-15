/**
 * @file
 * Event Listener to alter datalayer.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.initial_data_push = {
    attach: function (context, settings) {
      document.addEventListener('alterInitialData', (e) => {
        let cartId = localStorage.getItem('cart_id');
        if (cartId) {
          e.detail.data().cart_id = cartId;
        }
      });
    }
  };
})(jQuery, Drupal);
