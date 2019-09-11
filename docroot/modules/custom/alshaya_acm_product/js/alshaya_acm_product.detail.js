(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   * All custom js for product detail page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for product detail page.
   */
  Drupal.behaviors.alshayaAcmProductPdp = {
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

      $('#configurable_ajax .form-select').once('bind-js').on('change', function () {
        var form = $(this).closest('form');
        var sku = $(this).parents('article.entity--type-node:first').attr('data-sku');
        var combinations = drupalSettings.configurableCombinations[sku];
        var code = $(this).attr('data-configurable-code');
        var selected = $(this).val();
        var currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
        $('[name="selected_variant_sku"]', form).val('');

        if (typeof combinations['bySku'][code] !== 'undefined') {
          for (var i in combinations['bySku'][code][selected]) {
            var select = $('[data-configurable-code="' + i + '"]', form);

            select.val('');
            select.find('option')
              .prop('disabled', true)
              .attr('disabled', 'disabled')
              .removeProp('selected')
              .removeAttr('selected');

            for (var j in combinations['bySku'][code][selected][i]) {
              select.find('option[value="' + combinations['bySku'][code][selected][i][j] + '"]')
                .removeProp('disabled')
                .removeAttr('disabled');
            }

            // If there is only one option left, select it by default.
            var availableOptions = select.find('option:not([disabled])');
            if (availableOptions.length === 1) {
              var option = $(availableOptions).first();
              $(option).attr('selected', 'selected');
              select.val($(option).val());
            }

            select.trigger('refresh');
          }
        }

        var selectedCombination = Drupal.getSelectedCombination(form);
        var firstPossibleCombination = selectedCombination;

        if (typeof combinations['byAttribute'][selectedCombination] !== 'undefined') {
          $('[name="selected_variant_sku"]', form).val(combinations['byAttribute'][selectedCombination]);
        }
        else {
          firstPossibleCombination = Drupal.getFirstPossibleCombination(form, combinations['bySku']);
        }

        if (form.attr('selected-combination') != firstPossibleCombination) {
           if (typeof combinations['byAttribute'][firstPossibleCombination] !== 'undefined') {
             form.attr('selected-combination', firstPossibleCombination);
             $(this).parents('article.entity--type-node:first').trigger(
               'combination-changed',
               [
                 combinations['byAttribute'][firstPossibleCombination],
                 code
               ]
             );
           }
        }

        if (currentSelectedVariant != $('[name="selected_variant_sku"]', form).val()) {
          $(this).parents('article.entity--type-node:first').trigger('variant-selected');
        }
      });

      $('article.entity--type-node').once('load').each(function () {
        var sku = $(this).attr('data-sku');
        if (typeof drupalSettings.productInfo[sku] === 'undefined') {
          return;
        }

        $(this).find('#configurable_ajax .form-select').val('');

        // @TODO: Select based on selected query param or color.
        $(this).find('#configurable_ajax .form-select:first')
          .find('option:not([disabled]):first')
          .prop('selected', true)
          .attr('selected', 'selected')
          .trigger('change');

        Drupal.updateGallery(this, drupalSettings.productInfo[sku].layout, drupalSettings.productInfo[sku].gallery);

        $(this).on('combination-changed', function (event, variant, code) {
          var sku = $(this).attr('data-sku');
          var selected = $('[name="selected_variant_sku"]', $(this)).val();
          var variantInfo = drupalSettings.productInfo[sku]['variants'][variant];

          $(this).find('.price-block').html(variantInfo.price);

          if (selected === '' && drupalSettings.showImagesFromChildrenAfterAllOptionsSelected) {
            Drupal.updateGallery(this, drupalSettings.productInfo[sku].layout, drupalSettings.productInfo[sku].gallery);
          }
          else {
            Drupal.updateGallery(this, drupalSettings.productInfo[sku].layout, variantInfo.gallery);
          }

          // @TODO: Update quantity dropdown.
        });

        $(this).on('variant-selected', function (event, variant) {
          var sku = $(this).attr('data-sku');
          var selected = $('[name="selected_variant_sku"]', $(this)).val();
          var variantInfo = drupalSettings.productInfo[sku]['variants'][selected];
          Drupal.updateGallery(this, drupalSettings.productInfo[sku].layout, variantInfo.gallery);
        });
      });
    }
  };

  Drupal.updateGallery = function (product, layout, gallery) {
    if (gallery === '' || gallery === null) {
      return;
    }

    if ($(product).find('.gallery-wrapper').length > 0) {
      $(product).find('.gallery-wrapper').replaceWith(gallery);
    }
    else {
      $(product).find('#product-zoom-container').replaceWith(gallery);
    }

    if (layout === 'magazine') {
      Drupal.behaviors.magazine_gallery.attach(product);
    }
    else {
      Drupal.behaviors.alshaya_product_zoom.attach(product);
    }
  }
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

  Drupal.getFirstPossibleCombination = function (form, combinations) {
    var firstPossibleCombination = '';
    $('[data-configurable-code]', form).each(function () {
      var selectedVal = $(this).val();
      var attributeCode = $(this).attr('data-configurable-code');
      if (selectedVal === '' || selectedVal === null || typeof selectedVal === 'undefined') {
        selectedVal = Object.values(combinations[attributeCode])[0];

        if (typeof selectedVal != 'string' && typeof selectedVal != 'number') {
          selectedVal = Object.keys(combinations[attributeCode])[0];
        }
      }

      firstPossibleCombination += attributeCode + '|' + selectedVal + '||';
      combinations = combinations[attributeCode][selectedVal];
    }, firstPossibleCombination);

    return firstPossibleCombination;
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

})(jQuery, Drupal, drupalSettings);
