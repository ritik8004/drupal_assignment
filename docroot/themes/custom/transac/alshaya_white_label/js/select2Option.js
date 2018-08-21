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
    select.addClass('visually-hidden');

    var buttonsHtml = $('<div class="select2Option"></div>');
    var selectIndex = 0;
    var addOptGroup = function (optGroup) {
      if (optGroup.attr('label')) {
        buttonsHtml.append('<strong>' + optGroup.attr('label') + '</strong>');
      }
      var ulHtml = $('<ul class="select-buttons">');
      optGroup.children('option').each(function () {
        var liHtml = $('<li></li>');

        if ($(this).attr('swatch-image')) {
          liHtml.addClass('li-swatch-image');
          var swatchImage = '<img src="' + $(this).attr('swatch-image') + '" alt="' + $(this).text() + '" />';
          if (selectIndex === 0) {
            liHtml.hide();
          }
          else if ($(this).attr('disabled') || select.attr('disabled')) {
            liHtml.addClass('disabled');
            liHtml.append('<span class="' + $(this).text() + '">' + swatchImage + '</span>');
          }
          else {
            liHtml.append('<a href="#" class="' + $(this).text().replace(/\s+/g, '-') + '" data-select-index="' + selectIndex + '">' + swatchImage + '</a>');
          }
        }
        else {
          if (selectIndex === 0) {
            liHtml.hide();
          }
          else if ($(this).attr('disabled') || select.attr('disabled')) {
            liHtml.addClass('disabled');
            liHtml.append('<span class="' + $(this).text() + '">' + $(this).text() + '</span>');
          }
          else {
            liHtml.append('<a href="#" class="' + $(this).text() + '" data-select-index="' + selectIndex + '">' + $(this).text() + '</a>');
          }
        }

        // Mark current selection as "picked".
        if ((!options || !options.noDefault) && $(this).attr('selected')) {
          liHtml.children('a, span').addClass('picked');
        }
        ulHtml.append(liHtml);
        selectIndex++;
      });

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

    labeltext = select.attr('data-default-title');

    if (select.val() !== '' && select.val() !== null) {
      labeltext = select.attr('data-selected-title');
    }

    labeltext = '<h4 class="list-title"><span>' + labeltext + ' : </span><span class="selected-text"></span></h4>';
    buttonsHtml.prepend(labeltext);

    buttonsHtml.find('a').on('click', function (e) {
      e.preventDefault();
      var clickedOption = $(select.find('option')[$(this).attr('data-select-index')]);

      // Do nothing, it is already the selected one.
      if (clickedOption.is(':selected')) {
        return;
      }

      $(this).closest('.sku-base-form').find('label.error, span.error, div.error').remove();
      $(this).closest('.select2Option').find('.list-title .selected-text').html(clickedOption.text());
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

    select.parent().find('.select2Option').remove();
    select.after(buttonsHtml);
  });
};
