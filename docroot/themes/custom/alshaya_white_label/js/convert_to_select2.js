/**
 * @file
 * Select2 select.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.select2select = {
    attach: function (context, settings) {
      $('.select2-select').once('bind-events').each(function () {
        $(this).select2();
      });

      $('.alshaya-acm-customer-order-list-search .form-select').select2({
        minimumResultsForSearch: -1,
        dropdownCssClass: 'order-list-select'
      });
    }
  };
})(jQuery, Drupal);
