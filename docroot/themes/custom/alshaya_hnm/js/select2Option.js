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
    var swatch_markup;

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
          option_id = $(this).val();
          swatch_markup = Drupal.alshaya_hm_images_generate_swatch_markup($(this), select, option_id, 'disabled', '');
          if (swatch_markup) {
            liHtml.append(swatch_markup);
          }
          else {
            liHtml.append('<span class="' + $(this).text() + '">' + $(this).html() + '</span>');
          }
        }
        else {
          option_id = $(this).val();
          swatch_markup = Drupal.alshaya_hm_images_generate_swatch_markup($(this), select, option_id, 'enabled', selectIndex);
          if (swatch_markup) {
            liHtml.append(swatch_markup);
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
      $(this).closest('.select2Option').find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span>');
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


(function ($) {
  'use strict';
  Drupal.behaviors.alshaya_product_zoom = {
    attach: function (context, settings) {
      // Set the value for selected option.
      $('.select-buttons', context).find('a.picked').each(function () {
        var selectedText = $(this).attr('class').replace(' picked', '');
        var selectedTextSelector = $(this).closest('ul').siblings('h4.list-title').find('.selected-text');
        if (selectedTextSelector.length > 0) {
          selectedTextSelector.text(selectedText);
        }
        else {
          $(this).closest('ul').siblings('h4.list-title').append('<span class="selected-text">' + selectedText + '</span>');
        }
      });
    }
  };
}(jQuery));

/**
 * Helper function to generate swatch markup.
 *
 * @param {Object} currentOption
 *   Option being processed
 * @param {Object} select
 *   Select list DOM
 * @param {string} option_id
 *   Value of option being processed
 * @param {string} status
 *   Enabled/Disabled option.
 * @param {string} selectIndex
 *   Index for the enabled options.
 *
 * @return {string}
 *   Swatch markup depending on the above options.
 */
Drupal.alshaya_hm_images_generate_swatch_markup = function (currentOption, select, option_id, status, selectIndex) {
  'use strict';

  if ((select.attr('data-drupal-selector') === 'edit-configurables-article-castor-id') &&
  (drupalSettings.hasOwnProperty('sku_configurable_options_color')) &&
  (drupalSettings.sku_configurable_options_color.hasOwnProperty(option_id))) {
    var sku_configurable_options_color = drupalSettings.sku_configurable_options_color;
    var swatch_type = sku_configurable_options_color[option_id].swatch_type;

    // If status is disabled, use <span>, otherwise use <a>.
    var markup = status === 'enabled' ? '<a href="#" class="' + currentOption.text() + '" data-select-index="' + selectIndex + '"' : '<span class="' + currentOption.text() + '"';
    // If swatch type is RGB, add provided value as background-color, otherwise use it as markup.
    markup += swatch_type === 'miniature_image' ? '>' + sku_configurable_options_color[option_id].display_value : ' style="background-color:' + sku_configurable_options_color[option_id].display_value + ';">';
    markup += status === 'enabled' ? '</a>' : '</span>';

    return markup;
  }
};
