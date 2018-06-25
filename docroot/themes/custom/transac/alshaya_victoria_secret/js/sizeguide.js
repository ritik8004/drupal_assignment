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

      /**
       * JS for converting select list for size to unformatted list on PDP pages.
       *
       * @param {object} element
       *   The HTML element inside which we want to convert select list into unformatted list.
       */
      function convertSelectListtoUnformattedList(element) {
        element.once('bind-events').each(function () {
          $('select', $(this)).select2Option();

          var clickedOption = $('.select2Option li a.picked', $(this));
          $('.select2Option', $(this)).find('.list-title .selected-text').remove();
          $('.select2Option', $(this)).find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span>');
        });
      }

      function select2OptionConvert() {
        if ($(window).width() > 767) {
          convertSelectListtoUnformattedList($('.form-item-configurables-band-size, .form-item-configurables-cup-size, .form-item-configurables-size'));
        }

        convertSelectListtoUnformattedList($('.form-item-configurables-color-description'));
      }
    }
  };

})(jQuery, Drupal);
