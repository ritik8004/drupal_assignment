(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for checkout flow.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for checkout flow.
   */
  Drupal.behaviors.ZZ_alshaya_acm_checkout = {
    attach: function (context, settings) {
      if ($('.same-as-shipping:checked').val() === '2') {
        $('div[data-drupal-selector="edit-billing-address-address"]').show();
      }
      else if ($('.same-as-shipping:checked').val() === '1') {
        $('div[data-drupal-selector="edit-billing-address-address"]').hide();
      }
      else {
        setTimeout('jQuery(".same-as-shipping[value=1]").trigger("click").trigger("change")', 250);
      }
    }
  };

})(jQuery, Drupal);
