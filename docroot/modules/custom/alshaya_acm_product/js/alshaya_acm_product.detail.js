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

      // Disable sharing and deliver blocks for OOS.
      $('.product-out-of-stock').once('page-load').each(function () {
        $(this).find('.sharethis-wrapper').addClass('out-of-stock');
        $(this).find('.c-accordion-delivery-options').each(function () {
          if ($(this).accordion()) {
            $(this).accordion('option', 'disabled', true);
          }
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

      // Adding class to identify that matchback product color is manually updated.
      $('.acq-content-product-matchback').on('click', '.select2Option a', function () {
        if (!$(this).closest('.select2Option').hasClass('matchback-color-processed')) {
          $(this).closest('.select2Option').addClass('matchback-color-processed');
        }
      });

      $('.form-select[data-configurable-code]').once('bind-js').on('change', function () {
        var form = $(this).closest('form');
        var sku = $(form).attr('data-sku');
        var combinations = drupalSettings.configurableCombinations[sku];
        var code = $(this).attr('data-configurable-code');
        var selected = $(this).val();

        Drupal.refreshConfigurables(form, code, selected);

        var currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
        $('[name="selected_variant_sku"]', form).val('');

        var selectedCombination = Drupal.getSelectedCombination(form);

        if (typeof combinations['byAttribute'][selectedCombination] !== 'undefined') {
          $('[name="selected_variant_sku"]', form).val(combinations['byAttribute'][selectedCombination]);
        }

        if (currentSelectedVariant != $('[name="selected_variant_sku"]', form).val()) {
          $(this).closest('.sku-base-form').trigger(
            'variant-selected',
            [
              combinations['byAttribute'][selectedCombination],
              code
            ]
          );
        }
        // Trigger matchback color change on main product color change.
        if ($(this).hasClass('form-item-configurable-swatch')) {
          if (!$('.acq-content-product-matchback .select2Option').hasClass('matchback-color-processed')) {
            $('article[data-vmode="matchback"] form').trigger(
              'product-color-changed',
              [
                combinations['byAttribute'][selectedCombination],
                code
              ]
            );
            var selectedList = $('article[data-vmode="matchback"] select[data-configurable-code="color"] option[value="' + selected + '"]');
            var selectedIndex = selectedList.index();
            selectedList.parent().siblings('.select2Option').find('a[data-select-index="' + selectedIndex + '"]').click();
          }
        }

      });

      $('.sku-base-form').once('load').each(function () {
        var sku = $(this).attr('data-sku');
        if (typeof drupalSettings.productInfo === 'undefined' || typeof drupalSettings.productInfo[sku] === 'undefined') {
          return;
        }

        var node = $(this).parents('article.entity--type-node:first');
        Drupal.updateGallery(node, drupalSettings.productInfo[sku].layout, drupalSettings.productInfo[sku].gallery);

        $(this).on('variant-selected product-color-changed', function (event, variant, code) {
          var sku = $(this).attr('data-sku');
          var selected = $('[name="selected_variant_sku"]', $(this)).val();
          var variantInfo = drupalSettings.productInfo[sku]['variants'][variant];

          if (typeof variantInfo === 'undefined') {
            return;
          }

          $('.price-block-' + drupalSettings.productInfo[sku].identifier, node).html(variantInfo.price);

          if (selected === '' && drupalSettings.showImagesFromChildrenAfterAllOptionsSelected) {
            Drupal.updateGallery(node, drupalSettings.productInfo[sku].layout, drupalSettings.productInfo[sku].gallery);
          }
          else {
            Drupal.updateGallery(node, drupalSettings.productInfo[sku].layout, variantInfo.gallery);
          }

          // Update quantity dropdown based on stock available for the variant.
          $('select[name="quantity"] option', this).each(function () {
            if ($(this).val() > variantInfo.stock.qty) {
              if ($(this).is(':selected')) {
                $('select[name="quantity"] option:first').attr('selected', 'selected').prop('selected', true);
              }

              $(this).attr('disabled', 'disabled').prop('disabled', true);
            }
            else {
              $(this).removeAttr('disabled').removeProp('disabled');
            }
          });

          $('select[name="quantity"]', this).trigger('refresh');

          if (typeof variantInfo['description'] !== 'undefined') {
            for (var i in variantInfo['description']) {
              $(variantInfo['description'][i]['selector'], node).html(variantInfo['description'][i]['markup']);
            }
          }
        });

        if (drupalSettings.productInfo[sku]['variants']) {
          var variants = drupalSettings.productInfo[sku]['variants'];
          var selectedSku = Object.keys(variants)[0];
          var selected = parseInt(Drupal.getQueryVariable('selected'));

          if (selected > 0) {
            for (var i in variants) {
              if (variants[i]['id'] === selected) {
                selectedSku = variants[i]['sku'];
                break;
              }
            }
          }
          else if (typeof variants[selectedSku]['parent_sku'] !== 'undefined') {
            // Try to get first child with parent sku matching. This could go
            // in color split but is generic enough so added here.
            for (var i in variants) {
              if (variants[i]['parent_sku'] === sku) {
                selectedSku = variants[i]['sku'];
                break;
              }
            }
          }

          var firstAttribute = $('.form-select[data-configurable-code]:first', this);
          var firstAttributeValue = drupalSettings.configurableCombinations[sku]['bySku'][selectedSku][firstAttribute.attr('data-configurable-code')];
          $(firstAttribute).removeProp('selected').removeAttr('selected');
          $('option[value="' + firstAttributeValue + '"]', firstAttribute).prop('selected', true).attr('selected', 'selected');
          $(firstAttribute).val(firstAttributeValue).trigger('refresh').trigger('change');
        }
      });


      // Show images for oos product on PDP.
      $('.out-of-stock').once('load').each(function () {
        var sku = $(this).parents('article.entity--type-node:first').attr('data-sku');
        if (typeof drupalSettings.productInfo === 'undefined' || typeof drupalSettings.productInfo[sku] === 'undefined') {
          return;
        }

        var node = $(this).parents('article.entity--type-node:first');
        Drupal.updateGallery(node, drupalSettings.productInfo[sku].layout, drupalSettings.productInfo[sku].gallery);
      });

      // Add related products on pdp.
      var sku = $('article[data-vmode="full"]').attr('data-sku');
      var device = (window.innerWidth < 768) ? 'mobile' : 'desktop';
      var selector = (device == 'mobile') ? '.mobile-only-block' : '.above-mobile-block';
      var matchback = $('.horizontal-crossell' + selector);
      var upsell =  $('.horizontal-upell' + selector);
      var related = $('.horizontal-related' + selector);


      $(window).once('updateRelatedProducts').on('load', function () {
        if (!matchback.hasClass('matchback-processed')) {
          matchback.addClass('matchback-processed');
          Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/crosssell/' + device + '?cacheable=1'));
        }
        if (!upsell.hasClass('upsell-processed')) {
          upsell.addClass('upsell-processed');
          Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/upsell/' + device + '?cacheable=1'));
        }
        if (!related.hasClass('related-processed')) {
          related.addClass('related-processed');
          Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/related/' + device + '?cacheable=1'));
        }
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

    if (typeof Drupal.blazyRevalidate !== 'undefined') {
      Drupal.blazyRevalidate();
    }

    if (layout === 'pdp-magazine') {
      // Set timeout so that original behavior attachment is not affected.
      setTimeout(function () {
        Drupal.behaviors.magazine_gallery.attach(document);
      }, 1);
    }
    else {
      // Hide the thumbnails till JS is applied.
      // We use opacity through a class on parent to ensure JS get's applied
      // properly and heights are calculated properly.
      $('#product-zoom-container', product).addClass('whiteout');
      setTimeout(function () {
        Drupal.behaviors.alshaya_product_zoom.attach(document);
        Drupal.behaviors.alshaya_product_mobile_zoom.attach(document);

        // Show thumbnails again.
        $('#product-zoom-container', product).removeClass('whiteout');
      }, 1);
    }
  };

  Drupal.refreshConfigurables = function (form, selectedCode, selectedValue) {
    var sku = $(form).parents('article.entity--type-node:first').attr('data-sku');
    var combinations = drupalSettings.configurableCombinations[sku]['combinations'];

    var selectedValues = Drupal.getSelectedValues(form);
    for (var code in selectedValues) {
      if (code == selectedCode) {
        break;
      }

      combinations = combinations[code][selectedValues[code]];
    }

    if (typeof combinations[selectedCode] === 'undefined') {
      return;
    }

    if (combinations[selectedCode][selectedValue] === 1) {
      return;
    }
    var nextCode = Object.keys(combinations[selectedCode][selectedValue])[0];
    var nextValues = Object.keys(combinations[selectedCode][selectedValue][nextCode]);
    Drupal.alshayaAcmProductSelectConfiguration(form, nextCode, nextValues);

    var select = $('[data-configurable-code="' + nextCode + '"]', form);
    Drupal.refreshConfigurables(form, nextCode, select.val());
  };

  Drupal.alshayaAcmProductSelectConfiguration = function (form, selectedCode, possibleValues) {
    var select = $('[data-configurable-code="' + selectedCode + '"]', form);

    select.val('');

    select.find('option')
      .prop('disabled', true)
      .attr('disabled', 'disabled')
      .removeProp('selected')
      .removeAttr('selected');

    for (var i in possibleValues) {
      select.find('option[value="' + possibleValues[i] + '"]')
        .removeProp('disabled')
        .removeAttr('disabled');
    }

    select.find('option:not([disabled]):first')
      .prop('selected', true)
      .attr('selected', 'selected')
      .trigger('refresh');
  };

  Drupal.getSelectedValues = function (form) {
    var selectedValues = {};
    $('[data-configurable-code]', form).each(function () {
      var selectedVal = $(this).val();
      if (selectedVal !== '' && selectedVal !== null && typeof selectedVal !== 'undefined') {
        selectedValues[$(this).attr('data-configurable-code')] = selectedVal;
      }
    }, selectedValues);

    return selectedValues;
  };

  Drupal.getSelectedCombination = function (form) {
    var selectedValues = Drupal.getSelectedValues(form);
    var selectedCombination = '';

    for (var code in selectedValues) {
      selectedCombination += code + '|' + selectedValues[code] + '||';
    }

    return selectedCombination;
  };

  Drupal.updateRelatedProducts = function (url) {
    Drupal.ajax({
      url: url,
      progress: {type: 'throbber'},
    }).execute();
  }

  $(window).on('load', function () {
    // Show add to cart form now.
    $('.sku-base-form').each(function () {
      $(this).removeClass('visually-hidden');
      $(this).trigger('form-visible');
    });

    if ($('.magazine-layout').length > 0 || $(window).width() < 768) {
      $('.content__title_wrapper').addClass('show-sticky-wrapper');
    }
  });

})(jQuery, Drupal, drupalSettings);
