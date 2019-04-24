/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  var group_wrapper;
  var group_anchor_wrapper;
  var group_selected = {};

  Drupal.select2OptionConvert = function () {
    // Process configurable attributes which need to be shown as grouped.
    $('.form-item-configurable-select-group').once('bind-js').each(function () {
      // Add class to parent for styling.
      $(this).closest('.form-type-select').addClass('form-type-configurable-select-group');
      var that = $(this);

      // Hide current select, we will never show it.
      $(this).addClass('visually-hidden');

      // We will add all groups inside a wrapper to show only one at a time.
      group_wrapper = $('<div class="group-wrapper" />');

      // We will add all group anchors inside another wrapper to show together.
      group_anchor_wrapper = $('<div class="group-anchor-wrapper" />');

      // Get alternates from last option.
      var alternates = JSON.parse($(this).find('option:last').attr('group-data'));

      // Loop through all alternates to add anchor and dropdown for each.
      for (var i in alternates) {
        if (alternates.hasOwnProperty(i)) {
          Drupal.processAlternateForGroupedSelected(that, i, alternates[i]);
        }
      }

      if (typeof group_selected[$(this).attr('name')] !== 'undefined') {
        group_wrapper.find('.' + group_selected[$(this).attr('name')]).addClass('active');
        group_anchor_wrapper.find('.' + group_selected[$(this).attr('name')]).addClass('active');
      }
      else {
        group_wrapper.find('.group:first').addClass('active');
        group_anchor_wrapper.find('a:first').addClass('active');
      }

      $(this).after(group_wrapper);
      $(this).after(group_anchor_wrapper);
    });

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
          var selectedText = clickedOption.attr('selected-text')
            ? clickedOption.attr('selected-text')
            : clickedOption.text();
          $('.select2Option', that).find('.list-title .selected-text').html(selectedText);
        }
      }
    });
  };

  Drupal.processAlternateForGroupedSelected = function (that, i, alternate) {
    // Select needs to have some classes for styling and boxes JS.
    var select = $('<select class="form-item-configurable-select form-select" />');

    // Copy some other attributes.
    select.attr('data-selected-title', that.attr('data-selected-title'));
    select.attr('data-default-title', that.attr('data-default-title'));

    // Add each option with different display label based on alternate.
    that.find('option').each(function () {
      var option = $(this).clone();

      var group_data = $(this).attr('group-data');
      if ((typeof group_data == 'undefined')) {
        option.html($(this).html());
      }
      else {
        var option_alternates = JSON.parse(group_data);
        option.html(option_alternates[i]['value']);
        option.attr('selected-text', option_alternates[i]['label'] + '-' + option_alternates[i]['value']);
      }

      select.append(option);
    });

    // Bind to events and trigger same for original dropdown.
    select.on('change', function () {
      that.val($(this).val());
      that.trigger('change');
    });

    // Using group class to link anchor to its group.
    var group_class = 'group-' + alternate.label.toLowerCase();

    // Adding each group inside its own wrapper to let box JS work as is.
    var group = $('<div class="group ' + group_class + '" />');
    group.append(select);
    group_wrapper.append(group);

    // Adding anchor for each group.
    var anchor = $('<a href="#" class="' + group_class + '" />');
    anchor.html(alternate.label);
    anchor.on('click', function (event) {
      event.preventDefault();

      var group_class = 'group-' + $(this).html().toLowerCase();
      group_selected[$(this).parents('.form-type-configurable-select-group').find('.form-item-configurable-select-group').attr('name')] = group_class;

      // Remove active class from both anchor and group.
      $(this).closest('.form-type-select').find('.active').removeClass('active');

      // Add active class to anchor.
      $(this).addClass('active');

      // Add active class to linked group.
      $(this).closest('.form-type-select').find('.' + group_class).addClass('active');
    });

    group_anchor_wrapper.append(anchor);
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
