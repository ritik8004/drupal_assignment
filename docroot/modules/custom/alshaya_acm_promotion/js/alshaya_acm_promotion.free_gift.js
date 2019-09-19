(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for free gifts.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for free gifts.
   */
  Drupal.behaviors.alshayaAcmPromotionFreeGift = {
    attach: function (context, settings) {
      $('.sku-base-form').once('free-gifts').on('variant-selected', function () {
        $('.select-free-gift', $(this)).attr('disabled', 'disabled');

        if ($('[name="selected_variant_sku"]', $(this)).val()) {
          $('.select-free-gift', $(this)).removeAttr('disabled');
        }
      });
    }
  };

})(jQuery, Drupal);
