(function ($, Drupal, drupalSettings) {
  'use strict';

  // The threshold for how far you should reach before loading related products.
  var scrollThreshold = 200;

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

      $('.sku-base-form.visually-hidden').once('sku-base-form-processed').on('select-to-option-conversion-completed', function () {
        // Show add to cart form now.
        $(this).removeClass('visually-hidden');
        $(this).trigger('form-visible');

        if ($('.magazine-layout').length > 0 || $(window).width() < 768) {
          $('.content__title_wrapper').addClass('show-sticky-wrapper');
        }
      });

      // Disable sharing and deliver blocks for OOS.
      $('.product-out-of-stock').once('page-load').each(function () {
        $(this).find('.sharethis-wrapper').addClass('out-of-stock');
        $(this).find('.c-accordion-delivery-options').each(function () {
          if ($(this).accordion()) {
            $(this).accordion('option', 'disabled', true);
          }
        });
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
      $('.acq-content-product-matchback .select2Option a').on('click', function (event) {
        if (event.originalEvent != undefined) {
          if (!$(this).closest('.select2Option').hasClass('matchback-color-processed')) {
            $(this).closest('.select2Option').addClass('matchback-color-processed');
          }
        }
      });
      // Trigger matchback color change on main product color change.
      $('article[data-vmode="full"] form:first .form-item-configurable-swatch').once('product-swatch-change').on('change', function () {
        if (!$('.acq-content-product-matchback .select2Option').hasClass('matchback-color-processed')) {
          var selected = $(this).val();
          var selectedList = $('article[data-vmode="matchback"] .form-item-configurable-swatch option[value="' + selected + '"]');
          var selectedIndex = selectedList.index();
          selectedList.parent().siblings('.select2Option').find('a[data-select-index="' + selectedIndex + '"]').click();
        }
      });

      $('.form-select[data-configurable-code]').once('bind-js').on('change', function () {
        var form = $(this).parents('form');
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
          form.trigger(
            'variant-selected',
            [
              combinations['byAttribute'][selectedCombination],
              code
            ]
          );
        }
      });

      $('.sku-base-form').once('load').each(function () {
        var sku = $(this).attr('data-sku');
        var productKey = ($(this).parents('article.entity--type-node').attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';

        if (typeof drupalSettings[productKey] === 'undefined' || typeof drupalSettings[productKey][sku] === 'undefined') {
          return;
        }

        var node = $(this).parents('article.entity--type-node:first');
        Drupal.updateGallery(node, drupalSettings[productKey][sku].layout, drupalSettings[productKey][sku].gallery);

        $(this).on('variant-selected', function (event, variant, code) {
          var sku = $(this).attr('data-sku');
          var selected = $('[name="selected_variant_sku"]', $(this)).val();
          var variantInfo = drupalSettings[productKey][sku]['variants'][variant];

          if (typeof variantInfo === 'undefined') {
            return;
          }

          $('.price-block-' + drupalSettings[productKey][sku].identifier, node).html(variantInfo.price);

          if (selected === '' && drupalSettings.showImagesFromChildrenAfterAllOptionsSelected) {
            Drupal.updateGallery(node, drupalSettings[productKey][sku].layout, drupalSettings[productKey][sku].gallery);
          }
          else {
            Drupal.updateGallery(node, drupalSettings[productKey][sku].layout, variantInfo.gallery);
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

        if (drupalSettings[productKey][sku]['variants']) {
          var variants = drupalSettings[productKey][sku]['variants'];
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
        var productKey = ($(this).parents('article.entity--type-node').attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';

        if (typeof drupalSettings[productKey] === 'undefined' || typeof drupalSettings[productKey][sku] === 'undefined') {
          return;
        }

        var node = $(this).parents('article.entity--type-node:first');
        Drupal.updateGallery(node, drupalSettings[productKey][sku].layout, drupalSettings[productKey][sku].gallery);
      });

      // Add related products on pdp on load and scroll.
      $(window).once('updateRelatedProductsLoad').on('load scroll', function () {
        Drupal.getRelatedProductPosition();
      });

    }
  };

  Drupal.updateGallery = function (product, layout, gallery) {
    if (gallery === '' || gallery === null) {
      return;
    }

    if ($(product).find('.gallery-wrapper').length > 0) {
      $(product).find('.gallery-wrapper').first().replaceWith(gallery);
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

  Drupal.getRelatedProductPosition = function () {
    var sku = $('article[data-vmode="full"]').attr('data-sku');
    var device = (window.innerWidth < 768) ? 'mobile' : 'desktop';
    var selector = (device == 'mobile') ? '.mobile-only-block' : '.above-mobile-block';
    var matchback = $('.horizontal-crossell' + selector);
    var upsell =  $('.horizontal-upell' + selector);
    var related = $('.horizontal-related' + selector);
    var scrollPoint = window.innerHeight + window.pageYOffset;

    if (!matchback.hasClass('matchback-processed') && (scrollPoint > matchback.offset().top - scrollThreshold)) {
      matchback.addClass('matchback-processed');
      Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/crosssell/' + device + '?cacheable=1'));
    }
    if (!upsell.hasClass('upsell-processed') && (scrollPoint > upsell.offset().top - scrollThreshold)) {
      upsell.addClass('upsell-processed');
      Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/upsell/' + device + '?cacheable=1'));
    }
    if (!related.hasClass('related-processed') && (scrollPoint > related.offset().top - scrollThreshold)) {
      related.addClass('related-processed');
      Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/related/' + device + '?cacheable=1'));
    }
  }

})(jQuery, Drupal, drupalSettings);
