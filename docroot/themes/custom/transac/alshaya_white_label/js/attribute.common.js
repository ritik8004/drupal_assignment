/**
 * @file
 * Common code for attributes.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * JS for converting select list for size to unformatted list on PDP pages.
   *
   * @param {object} element
   *   The HTML element inside which we want to convert select list into unformatted list.
   */
  Drupal.convertSelectListtoUnformattedList = function (element) {
    element.once('convert-select-list-to-unformatted-list').each(function () {
      $(this).on('refresh', function () {
        var that = $(this).parent();

        $(this).select2Option();
        $(this).find('.list-title .selected-text').html('');

        var clickedOption = $('option:selected', this);
        if (!clickedOption.is(':disabled')) {
          var selectedText = clickedOption.attr('selected-text')
            ? clickedOption.attr('selected-text')
            : clickedOption.text();
          $('.select2Option', that).find('.list-title .selected-text').html(selectedText);

          Drupal.alshayaSelect2OptionUpdateSelectedLabel();
        }
      });

      $(this).trigger('refresh');
    });
  };

  Drupal.behaviors.configurableAttributeBoxes = {
    attach: function (context, settings) {
      $('.form-item-configurable-swatch').once('configurableAttributeBoxes').parent().addClass('configurable-swatch');
      $('.form-item-configurable-select').once('configurableAttributeBoxes').parent().addClass('configurable-select');

      // Show mobile slider only on mobile resolution.
      Drupal.select2OptionConvert();

      // Trigger event for other scripts to act after select options conversion
      // is completed.
      $('.sku-base-form.visually-hidden').trigger('select-to-option-conversion-completed');

      $(window).on('resize', function (e) {
        Drupal.select2OptionConvert();
      });

      if ($(window).width() <= drupalSettings.show_configurable_boxes_after) {
        $('.form-item-configurable-select, .form-item-configurable-swatch').once('configurableAttributeBoxes').on('change', function () {
          $(this).closest('form').find('div.error, label.error, span.error').remove();
        });
      }
    }
  };
})(jQuery, Drupal);
