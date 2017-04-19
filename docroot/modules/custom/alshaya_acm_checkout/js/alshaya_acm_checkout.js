(function ($, Drupal) {
  "use strict";

  var $pager = null;

  /**
   * All custom js for checkout flow.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for checkout flow.
   */
  Drupal.behaviors.alshaya_acm_checkout = {
    attach: function (context, settings) {
      if ($('.same-as-shipping:checked').val() == 2) {
        $('div[data-drupal-selector="edit-billing-address-address"]').show();
      }
      else {
        $('div[data-drupal-selector="edit-billing-address-address"]').hide();
      }
    }
  };

})(jQuery, Drupal);
