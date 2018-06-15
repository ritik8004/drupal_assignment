/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sizeguide = {
    attach: function (context, settings) {
      // Show mobile slider only on mobile resolution.
      select2OptionConvert();
      $(window).on('resize', function (e) {
        select2OptionConvert();
      });

      if ($(window).width() < 1025) {
        $('.form-item-configurable-select').on('change', function () {
          $(this).closest('.sku-base-form').find('.error').remove();
        });
      }

      // JS for converting select list for size to unformatted list on PDP pages.
      function select2OptionConvert() {
        if ($(window).width() > 1024) {
          $('.form-item-configurable-select').once('bind-events').each(function () {
            var that = $(this).parent();
            $('select', that).select2Option();

            var clickedOption = $('.select2Option li a.picked', that);
            $('.select2Option', that).find('.list-title .selected-text').remove();
            $('.select2Option', that).find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span>');
          });
        }
      }
    }
  };

})(jQuery, Drupal);
