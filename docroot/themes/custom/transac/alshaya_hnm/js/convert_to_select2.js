/**
 * @file
 * Select2 select.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.select2select = {
    attach: function (context, settings) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-size .form-select').select2({
          minimumResultsForSearch: -1
        });
      }

      if ($(window).width() > 1024) {
        $('.form-item-sort-bef-combine .form-select').select2({
          minimumResultsForSearch: -1
        });

        $('.select2-select').once('bind-events').each(function () {
          var placeHolder = $(this).find('option[value=""]').text();
          $(this).select2({
            placeholder: placeHolder
          });
        });

        $('#table-cart-items .form-select').select2({
          minimumResultsForSearch: -1
        });

        $('#payment_details .form-select').select2({
          minimumResultsForSearch: -1
        });

        // See https://github.com/select2/select2/pull/5035.
        $('.cybersource-credit-card-exp-month-select').once('manage-disable').on('change', function () {
          setTimeout(function () {
            $('.cybersource-credit-card-exp-month-select').select2('destroy');

            $('.cybersource-credit-card-exp-month-select').select2({
              minimumResultsForSearch: -1
            });
          }, 50);
        });

        $('.alshaya-acm-customer-order-list-search .form-select').select2({
          minimumResultsForSearch: -1,
          dropdownCssClass: 'order-list-select'
        });

        // PDP page quantity field, also works in crosssell, upsell modal views.
        $('.form-item-quantity .form-select').select2({
          minimumResultsForSearch: -1
        });

        // Checkout.com Month & Year select fields.
        // TODO: Use this generic approach everywhere and cleanup this file.
        $('.alshaya-select2.form-select').select2({
          minimumResultsForSearch: -1
        });
      }
    }
  };
})(jQuery, Drupal);
