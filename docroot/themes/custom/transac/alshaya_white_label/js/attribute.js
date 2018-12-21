/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.select2OptionConvert = function () {
    if ($(window).width() > drupalSettings.show_configurable_boxes_after) {
      // Show the boxes again if we had hidden them when user resized window.
      $('.configurable-select .select2Option').show();
      // Hide the dropdowns when user resizes window and is now in desktop mode.
      $('.form-item-configurable-select').addClass('visually-hidden');
      Drupal.convertSelectListtoUnformattedList($('.form-item-configurable-select'));
    }
    else {
      // Show the dropdowns when user is in mobile mode.
      $('.form-item-configurable-select').removeClass('visually-hidden');
      // Hide the boxes if user loaded the page in desktop mode and then resized.
      $('.configurable-select .select2Option').hide();
    }

    // Always hide the dropdown for swatch field.
    $('.form-item-configurable-swatch').addClass('visually-hidden');

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

      $('.select2Option', that).find('.list-title .selected-text').html('');

      var clickedOption = $('select option:selected', that);
      if (!clickedOption.is(':disabled')) {
        if ($(this).parent().hasClass('form-item-configurables-article-castor-id')) {
          Drupal.alshaya_color_swatch_update_selected_label();
        }
        else {
          $('.select2Option', that).find('.list-title .selected-text').html(clickedOption.text());
        }
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
