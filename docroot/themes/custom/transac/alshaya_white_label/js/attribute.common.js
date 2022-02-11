/**
 * @file
 * Common code for attributes.
 */

(function ($, Drupal) {

  /**
   * JS for converting select list for size to unformatted list on PDP pages.
   *
   * @param {object} element
   *   The HTML element inside which we want to convert select list into unformatted list.
   * @param {boolean} isGroupData
   *   If attribute is grouped.
   */
  Drupal.convertSelectListtoUnformattedList = function (element, isGroupData) {
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

          if (!isGroupData) {
            Drupal.alshayaSelect2OptionUpdateSelectedLabel();
          }
        }
      });

      $(this).trigger('refresh');
    });
  };

  Drupal.behaviors.configurableAttributeBoxes = {
    attach: function (context, settings) {
      var form = $('.sku-base-form', context).not('[data-sku *= "#"]');
      if (form.length === 0) {
        return;
      }

      $('.form-item-configurable-swatch', form).once('configurableAttributeBoxes').parent().addClass('configurable-swatch');
      $('.form-item-configurable-select', form).once('configurableAttributeBoxes').parent().addClass('configurable-select');

      // Show mobile slider only on mobile resolution.
      Drupal.select2OptionConvert(context);

      // Trigger event for other scripts to act after select options conversion
      // is completed.
      if (form.hasClass('visually-hidden')) {
        form.trigger('select-to-option-conversion-completed');
      }

      $(window).on('resize', function (e) {
        Drupal.select2OptionConvert(context);
      });

      if ($(window).width() <= drupalSettings.show_configurable_boxes_after) {
        $('.form-item-configurable-select, .form-item-configurable-swatch', form).once('configurableAttributeBoxes').on('change', function () {
          $(this).closest('form').find('div.error, label.error, span.error').remove();
        });
      }
    }
  };
})(jQuery, Drupal);
