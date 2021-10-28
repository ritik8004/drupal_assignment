(function (Drupal) {
  'use strict';

  Drupal.behaviors.productModalView = {
    attach: function (context) {
      var productModalViewEvent = new CustomEvent('onModalLoad', { bubbles: true });
      document.dispatchEvent(productModalViewEvent);
    }
  };
})(Drupal);
