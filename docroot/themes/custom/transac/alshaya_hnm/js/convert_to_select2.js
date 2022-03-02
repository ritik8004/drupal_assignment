/**
 * @file
 * Select2 select.
 */

(function ($, Drupal) {

  Drupal.behaviors.select2select = {
    attach: function (context, settings) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-size .form-select').once('select2select').select2({
          minimumResultsForSearch: -1
        });
      }

      if ($(window).width() > 1024) {
        $('.form-item-sort-bef-combine .form-select').once('select2select').select2({
          minimumResultsForSearch: -1
        });

        $('.select2-select').once('select2select').each(function () {
          var placeHolder = $(this).find('option[value=""]').text();
          $(this).select2({
            placeholder: placeHolder
          });
        });

        $('.contact-us-feedback .form-select, .contact-us-type .form-select, .contact-us-reason .form-select').once('select2select').select2({
          minimumResultsForSearch: -1
        });

        $('#table-cart-items .form-select').once('select2select').select2({
          minimumResultsForSearch: -1
        });

        $('#payment_details .form-select').once('select2select').select2({
          minimumResultsForSearch: -1
        });

        // See https://github.com/select2/select2/pull/5035.
        $('.checkoutcom-credit-card-exp-month-select').once('manage-disable').on('change', function () {
          setTimeout(function () {
            $('.checkoutcom-credit-card-exp-month-select').select2('destroy');

            $('.checkoutcom-credit-card-exp-month-select').select2({
              minimumResultsForSearch: -1
            });
          }, 50);
        });

        $('.alshaya-acm-customer-order-list-search .form-select').once('select2select').select2({
          minimumResultsForSearch: -1,
          dropdownCssClass: 'order-list-select'
        });

        // PDP page quantity field, also works in crosssell, upsell modal views.
        $('.form-item-quantity .form-select').once('select2select').select2({
          minimumResultsForSearch: -1
        });

        // Checkout.com Month & Year select fields.
        // TODO: Use this generic approach everywhere and cleanup this file.
        $('.convert-to-select2.form-select').once('select2select').select2({
          minimumResultsForSearch: -1
        });
      }

      $('.select2-hidden-accessible').once('bind-refresh').on('refresh', function () {
        $(this).select2('destroy');

        var options = {
          minimumResultsForSearch: -1
        };

        try {
          var placeHolder = $(this).find('option[value=""]').text();
          if (placeHolder.length > 0) {
            options.placeholder = placeHolder;
          }
        }
        catch (e) {
          // Do nothing.
        }

        $(this).select2(options);
      });
    }
  };
})(jQuery, Drupal);
