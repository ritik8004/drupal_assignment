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
    if ($('.form-item-configurables-article-castor-id .select-buttons li:nth-child(2) a').attr('data-swatch-type') === 'Details') {
      $('.form-item-configurables-article-castor-id').addClass('product-swatch');
    }
    else {
      $('.form-item-configurables-article-castor-id').addClass('colour-swatch');
    }
    Drupal.magazine_swatches_count();

    $('.show-more-color').on('click', function (e) {
      $('.form-item-configurables-article-castor-id .select-buttons li').each(function () {
        if ($(this).hide()) {
          $(this).show();
        }
      });
      $(this).hide();
      $('.show-less-color').show();
    });

    $('.show-less-color').on('click', function (e) {
      Drupal.magazine_swatches_count();
      $(this).hide();
      $('.show-more-color').show();
    });
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
        $('.select2Option', that).find('.list-title .selected-text').html(clickedOption.text());
      }
    });
  };

  Drupal.magazine_swatches_count = function () {
    var colour_swatches = drupalSettings.colour_swatch_items_mob;
    var product_swatches = drupalSettings.product_swatch_items_mob;
    var swatch_items_to_show = 0;

    if ($(window).width() > 767) {
      colour_swatches = drupalSettings.colour_swatch_items_tab;
      product_swatches = drupalSettings.product_swatch_items_tab;
    }

    if ($(window).width() > 1024) {
      colour_swatches = drupalSettings.colour_swatch_items_desk;
      product_swatches = drupalSettings.product_swatch_items_desk;
    }

    if ($('.configurable-swatch.product-swatch').length > 0) {
      swatch_items_to_show = product_swatches;
    }
    else {
      swatch_items_to_show = colour_swatches;
    }

    if ($('.form-item-configurables-article-castor-id .select-buttons li').length > swatch_items_to_show) {
      $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').hide();
      $('.show-more-color').show();
    }
  }

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
