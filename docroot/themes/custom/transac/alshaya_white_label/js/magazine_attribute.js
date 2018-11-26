/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.select2OptionConvert = function () {
    // Show the boxes again if we had hidden them when user resized window.
    $('.configurable-select .select2Option').show();
    // Hide the dropdowns when user resizes window and is now in desktop mode.
    $('.form-item-configurable-select').addClass('visually-hidden');
    Drupal.convertSelectListtoUnformattedList($('.form-item-configurable-select'));

    // Always hide the dropdown for swatch field.
    $('.form-item-configurable-swatch').addClass('visually-hidden');

    Drupal.convertSelectListtoUnformattedList($('.form-item-configurable-swatch'));
    Drupal.magazine_swatches_count();
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

  /**
   * implementation of view more/less colour for swatches.
   */
  Drupal.magazine_swatches_count = function () {
    if ($('.form-item-configurables-article-castor-id .select-buttons li:nth-child(2) a').attr('data-swatch-type') === 'Details') {
      $('.form-item-configurables-article-castor-id').addClass('product-swatch');
    }
    else {
      $('.form-item-configurables-article-castor-id').addClass('colour-swatch');
    }

    var colour_swatches = drupalSettings.colour_swatch_items_mob;
    var product_swatches = drupalSettings.product_swatch_items_mob;
    var swatch_items_to_show = 0;

    if ($(window).width() > 767 && $(window).width() < 1025) {
      colour_swatches = drupalSettings.colour_swatch_items_tab;
      product_swatches = drupalSettings.product_swatch_items_tab;
    }

    else if ($(window).width() > 1024) {
      colour_swatches = drupalSettings.colour_swatch_items_desk;
      product_swatches = drupalSettings.product_swatch_items_desk;
    }

    if ($('.configurable-swatch.product-swatch').length > 0) {
      swatch_items_to_show = product_swatches + 1;
    }
    else {
      swatch_items_to_show = colour_swatches + 1;
    }

    if ($('.form-item-configurables-article-castor-id .select-buttons li').length > swatch_items_to_show) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
        $('.form-item-configurables-article-castor-id').addClass('swatch-toggle');
      }
      $('.form-item-configurables-article-castor-id').addClass('swatch-effect');
      $('.show-more-color').show();
    }
    else {
      $('.form-item-configurables-article-castor-id').addClass('simple-swatch-effect');
    }

    $('.show-more-color').on('click', function (e) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
      }
      else {
        $('.form-item-configurables-article-castor-id').addClass('swatch-toggle');
      }
      $(this).hide();
      $('.show-less-color').show();
    });

    $('.show-less-color').on('click', function (e) {
      if ($(window).width() > 767) {
        $('.form-item-configurables-article-castor-id .select-buttons li:gt(" ' + swatch_items_to_show + ' ")').slideToggle();
      }
      else {
        $('.form-item-configurables-article-castor-id').removeClass('swatch-toggle');
      }
      $(this).hide();
      $('.show-more-color').show();
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
