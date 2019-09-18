/**
 * @file
 * Myaccount recent order.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.myaccountRecentOrder = {
    attach: function () {
      if ($('.recent__orders--list .order-summary-row').length) {
        var parentOrder = $('.recent__orders--list .order-summary-row');
        var listOrder = $('.recent__orders--list .order-item-row');

        $(listOrder).hide();

        $(parentOrder).on('click', function () {
          var $ub = $(this).nextAll().stop(true, true).fadeToggle('slow');
          listOrder.not($ub).hide();
          $ub.parent().toggleClass('open--accordion');
          listOrder.not($ub).parent().removeClass('open--accordion');
        });
      }
    }
  };
})(jQuery, Drupal);
