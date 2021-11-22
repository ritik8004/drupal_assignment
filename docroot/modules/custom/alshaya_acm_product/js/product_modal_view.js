(function (Drupal) {
  'use strict';

  Drupal.behaviors.alshayaAcmProductModalView = {
    attach: function (context) {
      // Dispatch event on modal load each time to perform action on load.
      // Like we are rendering Sofa form and wishlist icon on modal load event.
      var productModalViewEvent = new CustomEvent('onModalLoad', { bubbles: true });
      document.dispatchEvent(productModalViewEvent);
    }
  };
})(Drupal);
