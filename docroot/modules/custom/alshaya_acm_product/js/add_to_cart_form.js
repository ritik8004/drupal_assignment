(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for add to cart form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for add to cart form.
   */
  Drupal.behaviors.alshayaAcmProductAddToCartForm = {
    attach: function (context, settings) {

      // Disable shareing and deliver blocks for OOS.
      $('.product-out-of-stock').once('page-load').each(function () {
        $(this).find('.sharethis-wrapper').addClass('out-of-stock');
        $(this).find('.c-accordion-delivery-options').each(function () {
          $(this).accordion('option', 'disabled', true);
        })
      });

      $('.edit-add-to-cart').once('js-to-move-error-message').on('click', function () {
        if ($(this).closest('form').hasClass('ajax-submit-prevented')) {
          $('.form-item > label.error', $(this).closest('form')).each(function () {
            var parent = $(this).closest('.form-item');
            if (parent.find('.select2Option').length > 0) {
              $('.selected-text', $(parent)).append($(this));
            }
            else {
              $('label.form-required', $(parent)).append($(this));
            }
          });
        }
      });

      $('.form-select[data-configurable-code]').once('bind-js').on('change', function () {
        var form = $(this).closest('form');
        var sku = $(form).attr('data-sku');
        var combinations = drupalSettings.configurableCombinations[sku];
        var code = $(this).attr('data-configurable-code');
        var selected = $(this).val();
        var currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
        $('[name="selected_variant_sku"]', form).val('');

        if (typeof combinations['bySku'][code] !== 'undefined') {
          for (var i in combinations['bySku'][code][selected]) {
            $('[data-configurable-code="' + i + '"]', form).val('');
            $('[data-configurable-code="' + i + '"]', form)
              .find('option')
              .prop('disabled', true)
              .attr('disabled', 'disabled');

            for (var j in combinations['bySku'][code][selected][i]) {
              $('[data-configurable-code="' + i + '"]', form)
                .find('option[value="' + combinations['bySku'][code][selected][i][j] + '"]')
                .removeProp('disabled')
                .removeAttr('disabled');
            }

            $('[data-configurable-code="' + i + '"]', form).trigger('refresh');
          }
        }

        var selectedCombination = Drupal.getSelectedCombination(form);

        if (typeof combinations['byAttribute'][selectedCombination] !== 'undefined') {
          $('[name="selected_variant_sku"]', form).val(combinations['byAttribute'][selectedCombination]);
        }

        if (currentSelectedVariant != $('[name="selected_variant_sku"]', form).val()) {
          $(this).closest('.sku-base-form').trigger('variant-selected');
        }
      });

      $('.sku-base-form').once('load').each(function () {
        $(this).find('.form-select[data-configurable-code]').val('');

        // @TODO: Select based on selected query param or color.
        $(this).find('.form-select[data-configurable-code]:first')
          .find('option:not([disabled]):first')
          .prop('selected', true)
          .attr('selected', 'selected')
          .trigger('change');
      });
    }
  };

  Drupal.getSelectedCombination = function (form) {
    var selectedCombination = '';
    $('[data-configurable-code]', form).each(function () {
      var selectedVal = $(this).val();
      if (selectedVal === '' || selectedVal === null || typeof selectedVal === 'undefined') {
        if ($(this).find('option:not([disabled])').length === 1) {
          $(this).find('option:not([disabled])').prop('selected', true).attr('selected', 'selected').trigger('change');
          return;
        }
      }
      else {
        selectedCombination += $(this).attr('data-configurable-code') + '|' + selectedVal + '||';
      }
    }, selectedCombination);

    return selectedCombination;
  };

  $(window).on('load', function () {
    // Show add to cart form now.
    $('.sku-base-form').removeClass('visually-hidden');

    if ($('.magazine-layout').length > 0 || $(window).width() < 768) {
      $('.content__title_wrapper').addClass('show-sticky-wrapper');
    }
  });

  $.fn.reloadPage = function () {
    window.location.reload();
  };

  $.fn.hideLoader = function () {
    $('.ajax-progress, .ajax-progress-throbber').remove();
  }

})(jQuery, Drupal);
