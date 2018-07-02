/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  // From Nik: Can we remove this file?
  Drupal.behaviors.sizeguide = {
    attach: function (context, settings) {
      // Show mobile slider only on mobile resolution.
      select2OptionConvert();
      $(window).on('resize', function (e) {
        select2OptionConvert();
      });

      if ($(window).width() < 1025) {
        $('.acq-content-product .form-item-configurables-size select').on('change', function () {
          $(this).closest('.sku-base-form').find('.error').remove();
        });

        $('.acq-content-product-modal .form-item-configurables-size select').on('change', function () {
          $(this).closest('.sku-base-form').find('.error').remove();
        });
      }

      // JS for converting select list for size to unformatted list on PDP pages.
      function select2OptionConvert() {
        if ($(window).width() > 767) {
          $('.acq-content-product .form-item-configurables-size, .acq-content-product-modal .form-item-configurables-size, .acq-content-product .form-item-configurables-article-castor-id, .acq-content-product-modal .form-item-configurables-article-castor-id, .block-basket-horizontal-recommendation .form-item-configurables-article-castor-id').once('bind-events').each(function () {
            $('select', $(this)).select2Option();

            var clickedOption = $('.select2Option li a.picked', $(this));
            $('.select2Option', $(this)).find('.list-title .selected-text').remove();
            $('.select2Option', $(this)).find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span>');
          });
        }

        $('.acq-content-product .form-item-configurables-article-castor-id, .acq-content-product-modal .form-item-configurables-article-castor-id, .block-basket-horizontal-recommendation .form-item-configurables-article-castor-id').once('bind-events').each(function () {
          $('select', $(this)).select2Option();
        });
      }
    }
  };

})(jQuery, Drupal);
