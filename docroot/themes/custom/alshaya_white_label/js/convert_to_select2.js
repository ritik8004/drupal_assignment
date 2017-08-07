/**
 * @file
 * Select2 select.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.select2select = {
    attach: function (context, settings) {
      $('.form-item-sort-bef-combine .form-select').select2();

      $('.form-item-configurables-size .form-select').select2();

      $('.select2-select').once('bind-events').each(function () {
        $(this).select2();
      });

      $('.alshaya-acm-customer-order-list-search .form-select').select2({
        minimumResultsForSearch: -1,
        dropdownCssClass: 'order-list-select'
      });

      // PDP page quantity field, also works in crosssell, upsell modal views.
      $('.form-item-quantity .form-select').select2({
        minimumResultsForSearch: -1
      });
    }
  };
})(jQuery, Drupal);
