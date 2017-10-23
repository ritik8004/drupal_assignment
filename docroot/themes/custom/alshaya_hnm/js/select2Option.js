/**
 * @file
 * JQuery Plugin to convert Select to Unformatted list.
 */

jQuery.fn.select2Option = function (options) {
  'use strict';

  return this.each(function () {
    var $ = jQuery;
    var select = $(this);
    var labeltext = '';
    var option_id;
    var sku_configurable_options_color;
    select.hide();

    var buttonsHtml = $('<div class="select2Option"></div>');
    var selectIndex = 0;
    var addOptGroup = function (optGroup) {
      if (optGroup.attr('label')) {
        buttonsHtml.append('<strong>' + optGroup.attr('label') + '</strong>');
      }
      var ulHtml = $('<ul class="select-buttons">');
      optGroup.children('option').each(function () {

        var liHtml = $('<li></li>');
        if (selectIndex === 0) {
          liHtml.hide();
          var defaultTitle = $(this).parent().attr('data-default-title');
          if (typeof defaultTitle !== 'undefined' && defaultTitle !== false) {
            labeltext = '<h4 class="list-title"><span>' + $(this).parent().attr('data-default-title') + ' : <span></h4>';
          }
          else {
            labeltext = '<h4 class="list-title"><span>' + $(this).text() + ' : <span></h4>';
          }
        }
        else if ($(this).attr('disabled') || select.attr('disabled')) {
          liHtml.addClass('disabled');
          if ((select.attr('data-drupal-selector') === 'edit-configurables-color') &&
            (drupalSettings.hasOwnProperty('sku_configurable_options_color')) &&
      drupalSettings.sku_configurable_options_color.hasOwnProperty($(this).val())) {
            option_id = $(this).val();
            sku_configurable_options_color = drupalSettings.sku_configurable_options_color;
            if (sku_configurable_options_color[option_id].swatch_type === 'color_block') {
              liHtml.append('<span class="' + $(this).text() + '" style="background-color:' + sku_configurable_options_color[option_id].display_value + ';"></span>');
            }
            else if (sku_configurable_options_color[option_id].swatch_type === 'miniature_image') {
              liHtml.append('<span class="' + $(this).text() + '">' + sku_configurable_options_color[option_id].display_value + '</span>');
            }
          }
          else {
            liHtml.append('<span class="' + $(this).text() + '">' + $(this).html() + '</span>');
          }
        }
        else {
          if ((select.attr('data-drupal-selector') === 'edit-configurables-color') &&
      (drupalSettings.hasOwnProperty('sku_configurable_options_color')) &&
      drupalSettings.sku_configurable_options_color.hasOwnProperty($(this).val())) {
            option_id = $(this).val();
            sku_configurable_options_color = drupalSettings.sku_configurable_options_color;
            if (sku_configurable_options_color[option_id].swatch_type === 'color_block') {
              liHtml.append('<a href="#" class="' + $(this).text() + '" data-select-index="' + selectIndex + '" style="background-color:' + sku_configurable_options_color[option_id].display_value + ';"></a>');
            }
            else if (sku_configurable_options_color[option_id].swatch_type === 'miniature_image') {
              liHtml.append('<a href="#" class="' + $(this).text() + '" data-select-index="' + selectIndex + '">' + sku_configurable_options_color[option_id].display_value + '</a>');
            }
          }
          else {
            liHtml.append('<a href="#" class="' + $(this).text() + '" data-select-index="' + selectIndex + '">' + $(this).html() + '</a>');
          }
        }

        // Mark current selection as "picked".
        if ((!options || !options.noDefault) && $(this).attr('selected')) {
          liHtml.children('a, span').addClass('picked');
        }
        ulHtml.append(liHtml);
        selectIndex++;
      });
      buttonsHtml.prepend(labeltext);
      buttonsHtml.append(ulHtml);
    };

    var optGroups = select.children('optgroup');
    if (optGroups.length === 0) {
      addOptGroup(select);
    }
    else {
      optGroups.each(function () {
        addOptGroup($(this));
      });
    }

    select.after(buttonsHtml);

    buttonsHtml.find('a').on('click', function (e) {
      e.preventDefault();
      var clickedOption = $(select.find('option')[$(this).attr('data-select-index')]);
      $(this).closest('.select2Option').find('.list-title .selected-text').remove();
      $(this).closest('.sku-base-form').find('.error').remove();
      $(this).closest('.select2Option').find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span');
      if ($(this).hasClass('picked')) {
        $(this).removeClass('picked');
        clickedOption.removeProp('selected');
      }
      else {
        buttonsHtml.find('a, span').removeClass('picked');
        $(this).addClass('picked');
        clickedOption.prop('selected', true);
      }
      select.trigger('change');
    });
  });
};
