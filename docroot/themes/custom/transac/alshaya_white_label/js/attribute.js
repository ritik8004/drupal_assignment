/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.select2OptionConvert = function () {
    if ($(window).width() > drupalSettings.show_configurable_boxes_after) {
      Drupal.convertSelectListtoUnformattedList($('.form-item-configurable-select'));
    }
    else {
      $('.form-item-configurable-select').removeClass('visually-hidden');
    }

    Drupal.convertSelectListtoUnformattedList($('.form-item-configurable-swatch'));
  };

  /**
   * JS for converting select list for size to unformatted list on PDP pages.
   *
   * @param {object} element
   *   The HTML element inside which we want to convert select list into unformatted list.
   */
  Drupal.convertSelectListtoUnformattedList = function (element) {
    element.once('bind-events').each(function () {
      var that = $(this).parent();
      $('select', that).select2Option();

      $('.select2Option', that).find('.list-title .selected-text').remove();

      var clickedOption = $('select option:selected', that);
      if (!clickedOption.is(':disabled')) {
        $('.select2Option', that).find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span>');
      }
    });
  };

  Drupal.behaviors.configurableAttributeBoxes = {
    attach: function (context, settings) {
      $('.form-item-configurable-swatch').parent().addClass('configurable-swatch');
      $('.form-item-configurable-select').parent().addClass('configurable-select');

      // Show mobile slider only on mobile resolution.
      Drupal.select2OptionConvert();
      $(window).on('resize', function (e) {
        Drupal.select2OptionConvert();
      });

      if ($(window).width() <= drupalSettings.show_configurable_boxes_after) {
        $('.form-item-configurable-select, .form-item-configurable-swatch').on('change', function () {
          $(this).closest('.sku-base-form').find('div.error, label.error, span.error').remove();
        });
      }
    }
  };

})(jQuery, Drupal);
