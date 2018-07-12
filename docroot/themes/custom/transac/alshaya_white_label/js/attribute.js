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

      if ($(window).width() <= drupalSettings.show_configurable_boxes_after) {
        $('.form-item-configurable-select').on('change', function () {
          $(this).closest('.sku-base-form').find('.error').remove();
        });
      }

      $('.form-item-configurable-swatch').parent().addClass('configurable-swatch');
      $('#configurable_ajax .form-item-configurable-select').parent().addClass('configurable-select');

      /**
       * JS for converting select list for size to unformatted list on PDP pages.
       *
       * @param {object} element
       *   The HTML element inside which we want to convert select list into unformatted list.
       */
      function convertSelectListtoUnformattedList(element) {
        element.once('bind-events').each(function () {
          var that = $(this).parent();
          $('select', that).select2Option();

          $('.select2Option', that).find('.list-title .selected-text').remove();

          var clickedOption = $('select option:selected', that);
          if (!clickedOption.is(':disabled')) {
            $('.select2Option', that).find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span>');
          }
        });
      }

      function select2OptionConvert() {
        if ($(window).width() > drupalSettings.show_configurable_boxes_after) {
          convertSelectListtoUnformattedList($('.form-item-configurable-select'));
        }

        convertSelectListtoUnformattedList($('.form-item-configurable-swatch'));
      }
    }
  };

})(jQuery, Drupal);
