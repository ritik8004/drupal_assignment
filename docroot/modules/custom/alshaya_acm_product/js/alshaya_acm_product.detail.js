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
        // This can be triggered from two places:
        //   1. User click on matchback product.
        //   2. User click on main product and triggered from code to update matchback.
        // We want following to be executed only in #1 case, so the condition below.
        if (event.originalEvent !== undefined) {
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
        var viewMode = $(this).parents('article.entity--type-node').attr('data-vmode');
        var productKey = (viewMode === 'full') ? 'productInfo' : viewMode;

        // Fill the view mode form field.
        $(this).parents('article.entity--type-node[data-vmode="' + viewMode + '"]').find('.product-view-mode').val(viewMode);

        if (typeof drupalSettings[productKey] === 'undefined' || typeof drupalSettings[productKey][sku] === 'undefined') {
          return;
        }

        // On form load set order qty limit message.
        Drupal.disableLimitExceededProducts(sku, sku);

        var node = $(this).parents('article.entity--type-node:first');
        Drupal.updateGallery(node, drupalSettings[productKey][sku].layout, drupalSettings[productKey][sku].gallery);

        $(this).on('variant-selected', function (event, variant, code) {
          var sku = $(this).attr('data-sku');
          var selected = $('[name="selected_variant_sku"]', $(this)).val();
          var variantInfo = drupalSettings[productKey][sku]['variants'][variant];
          var parentSku = variantInfo.parent_sku;

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
          // On variant change, disable/enable Add to bag, quantity dropdown
          // and show message based on value in drupalSettings.
          Drupal.disableLimitExceededProducts(parentSku, selected);

          // Update quantity dropdown based on stock available for the variant.
          $('select[name="quantity"] option', this).each(function () {
            if (($(this).val() > variantInfo.stock.qty) || (variantInfo.stock.maxSaleQty !== 0 && ($(this).val() > variantInfo.stock.maxSaleQty))) {
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

      // Add 'each' with price on change of quantity if matchback is enabled.
      if ($('.price-suffix-matchback').length) {
        $('select.edit-quantity').once('product-edit-quantity').on('change', function () {
          var quantity = $(this).val();
          var productKey = ($(this).parents('article.entity--type-node').attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
          var eachSelector = $('.price-block-' + drupalSettings[productKey][$(this).closest('form').attr('data-sku')].identifier + ' .price-suffix-matchback');

          if (quantity > 1) {
            eachSelector.show();
          } else if (quantity <= 1) {
            eachSelector.hide();
          }
        });
      }
    }
  };

  Drupal.updateGallery = function (product, layout, gallery) {
    if (gallery === '' || gallery === null) {
      return;
    }

    if ($(product).find('.gallery-wrapper').length > 0) {
      // Since matchback products are also inside main PDP, when we change the variant
      // of the main PDP we'll get multiple .gallery-wrapper, so we are taking only the
      // first one which will be of main PDP to update main PDP gallery only.
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
    var sku = ($(form).parents('article.entity--type-node:first').length > 0)
      ? $(form).parents('article.entity--type-node:first').attr('data-sku')
      : $(form).attr('data-sku');

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
      type: 'GET',
    }).execute();
  };

  Drupal.getRelatedProductPosition = function () {
    var sku = $('article[data-vmode="full"]').attr('data-sku');
    var device = (window.innerWidth < 768) ? 'mobile' : 'desktop';
    var selector = (device == 'mobile') ? '.mobile-only-block' : '.above-mobile-block';
    var matchback = $('.horizontal-crossell' + selector);
    var upsell =  $('.horizontal-upell' + selector);
    var related = $('.horizontal-related' + selector);
    var scrollPoint = window.innerHeight + window.pageYOffset;

    if ((matchback.length > 0) && !matchback.hasClass('matchback-processed') && (scrollPoint > matchback.offset().top - scrollThreshold)) {
      matchback.addClass('matchback-processed');
      Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/crosssell/' + device + '?cacheable=1'));
    }
    if ((upsell.length > 0) && !upsell.hasClass('upsell-processed') && (scrollPoint > upsell.offset().top - scrollThreshold)) {
      upsell.addClass('upsell-processed');
      Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/upsell/' + device + '?cacheable=1'));
    }
    if ((related.length > 0) && !related.hasClass('related-processed') && (scrollPoint > related.offset().top - scrollThreshold)) {
      related.addClass('related-processed');
      Drupal.updateRelatedProducts(Drupal.url('related-products/' + sku + '/related/' + device + '?cacheable=1'));
    }
  };

  // Disable Add to bag and quantity dropdown when order limit exceed.
  Drupal.disableLimitExceededProducts = function (sku, selected) {
    var orderLimitMsgSelector = $('input[value=' + selected + ']').closest('.field--name-field-skus.field__items').siblings('.order-quantity-limit-message');
    var orderLimitMobileMsgSelector = $('input[value=' + selected + ']').closest('.field--name-field-skus.field__items').parents('.acq-content-product').find('.order-quantity-limit-message.mobile-only');
    var viewMode = $('input[value=' + selected + ']').parents('article.entity--type-node').attr('data-vmode');
    var productKey = (viewMode === 'full') ? 'productInfo' : viewMode;
    var parentInfo = typeof drupalSettings[productKey][sku] !== "undefined" ? drupalSettings[productKey][sku] : '';
    // At parent level, sku and selected will be same.
    var variantInfo = (typeof drupalSettings[productKey][sku] !== "undefined" &&
      typeof drupalSettings[productKey][sku]['variants'] !== "undefined" &&
      sku !== selected) ?
      drupalSettings[productKey][sku]['variants'][selected] : '';
    var variantToDisableSelector = $('input[value=' + selected + ']').closest('.sku-base-form');
    var orderLimitExceeded =  false;
    var orderLimitExceededMsg = '<span class="order-qty-limit-msg-inner-wrapper limit-reached">' +
      Drupal.t('Purchase limit has been reached') +
      '</span>';
    var cart_items = drupalSettings['cart_items'];

    // If limit exists at parent level.
    if ((parentInfo !== '') && (typeof parentInfo.maxSaleQty !== "undefined")) {
      var variantToDisableSelector = $('input[value=' + sku + ']').closest('.sku-base-form');
      var allVariants = parentInfo.variants ? Object.keys(parentInfo.variants) : [];

      // If cart is not empty.
      if (typeof cart_items !== "undefined") {
        var itemQtyInCart = 0;
        var orderLimitMsg = parentInfo.orderLimitMsg;

        if (allVariants.length !== 0) {
          $.each( cart_items, function( item, value ) {
            if ($.inArray( item, allVariants ) >= 0) {
              itemQtyInCart += value.qty;
            }
          });
        }
        else {
          itemQtyInCart = ($.inArray(selected, Object.keys(drupalSettings['cart_items'])) >= 0) ?
          cart_items[selected]['qty'] : 0;
        }

        if (itemQtyInCart >= parentInfo.maxSaleQty) {
          var orderLimitExceeded = true;
          var orderLimitMsg = orderLimitExceededMsg;
        }
      }
    }
    else if (variantInfo !== '') {
      var orderLimitMsg = variantInfo.orderLimitMsg;

      // If cart is not empty.
      if (typeof cart_items !== "undefined") {
        var selectedItemInCart = $.inArray(selected, Object.keys(cart_items));
        // If selected item is in cart.
        if (selectedItemInCart >= 0) {
          var itemQtyInCart = drupalSettings['cart_items'][selected]['qty'];

          if (itemQtyInCart >= variantInfo.stock.maxSaleQty) {
            var orderLimitExceeded = true;
            var orderLimitMsg = orderLimitExceededMsg;
          }
        }
      }
    }

    // Disable/Enable Add to Bag and quantity dropdown.
    variantToDisableSelector.find('.edit-add-to-cart.button').prop('disabled', orderLimitExceeded);
    variantToDisableSelector.find('.edit-quantity').prop('disabled', orderLimitExceeded);

    // Add order quantity limit message.
    orderLimitMsgSelector.html(orderLimitMsg);
    orderLimitMobileMsgSelector.html(orderLimitMsg);
  };

  // Cart limit exceeded for a variant.
  $.fn.LimitExceededInCart = function (sku, selected) {
    Drupal.disableLimitExceededProducts(sku, selected);
  }

  // This event is triggered on page load itself in attach (Drupal.behaviors.configurableAttributeBoxes)
  // once size boxes are shown properly
  // but since we can't control which attach is triggered first,
  // at times this event was getting bound after it was triggered. So keeping this outside attach.
  $(document).on('select-to-option-conversion-completed', '.sku-base-form.visually-hidden', function () {
    // Show add to cart form now.
    $(this).removeClass('visually-hidden');
    $(this).trigger('form-visible');

    if ($('.magazine-layout').length > 0 || $(window).width() < 768) {
      $('.content__title_wrapper').addClass('show-sticky-wrapper');
    }
  });

})(jQuery, Drupal, drupalSettings);
