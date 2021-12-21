(function ($, Drupal, drupalSettings) {
  'use strict';
 /**
   * All custom js for product detail page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for product pdp path.
   */
  Drupal.behaviors.alshayaAcmProductPdpPath = {
    attach: function (context, settings) {
      // Trigger matchback color change on main product color change.
      $('article[data-vmode="full"] form:first .form-item-configurable-swatch').once('product-swatch-change').on('change', function () {
        var selected = $(this).val();
        Drupal.getSelectedForMatchback(selected);
      });
    }
  };

  Drupal.getSelectedProductFromQueryParam = function (viewMode, productInfo) {
    var variants = productInfo['variants'];
    // Use selected from query parameter only for main product.
    Drupal.getSelectedSkuFromQueryParameter(viewMode, variants);
  };

})(jQuery, Drupal, drupalSettings);
