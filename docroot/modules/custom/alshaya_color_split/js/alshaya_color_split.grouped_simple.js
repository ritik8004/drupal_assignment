(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   * Custom js around color split for add to cart form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for add to cart form.
   */
  Drupal.behaviors.alshayaColorSplitGroupSimple = {
    attach: function (context, settings) {
      $('[data-drupal-selector="edit-variants-in-group"]').once('alshaya-color-split').on('change', function () {
        var node = $(this).parents('article.entity--type-node:first');
        var sku = $(node).attr('data-sku');
        var viewMode = $(node).attr('data-vmode');
        var productKey = Drupal.getProductKeyForProductViewMode(viewMode);
        if (typeof drupalSettings[productKey][sku] === 'undefined') {
          return;
        }

        if ($(this).val() == $('[name="selected_variant_sku"]', node).val()) {
          return;
        }

        $('[name="selected_variant_sku"]', node).val($(this).val());

        var variantInfo = drupalSettings[productKey][sku]['group'][$(this).val()];
        Drupal.updateGallery(node, variantInfo.layout,variantInfo.gallery);

        // Trigger event for other modules to hook into.
        $(node).trigger('group-item-selected', [$(this).val()]);

        // Update sameday and express delivery labels on variant change.
        if (drupalSettings.expressDelivery !== 'undefined' && drupalSettings.expressDelivery.enabled) {
          for (var option in variantInfo.delivery_options) {
            $(node).find('.' + option).removeClass('active in-active');
            $(node).find('.' + option).addClass(variantInfo.delivery_options[option].status);;
          }
          $(node).find('.express-delivery').removeClass('active in-active');
          $(node).find('.express-delivery').addClass(variantInfo.express_delivery_class);
        }

        // Trigger an event on variant select.
        // Only considers variant when url is changed.
        var currentSelectedVariantEvent = new CustomEvent('onSkuVariantSelect', {
          bubbles: true,
          detail: {
            data: {
              viewMode,
              sku: $(this).val(),
              variantSelected: $(this).val(),
              title: variantInfo.cart_title,
            }
          }
        });
        document.dispatchEvent(currentSelectedVariantEvent);

        if (viewMode === 'full' || viewMode === 'matchback' || viewMode === 'matchback_mobile') {
          $(node).find('.content--item-code .field__value').html($(this).val());

          if (window.location.pathname !== variantInfo.url[$('html').attr('lang')]) {
            var url = variantInfo.url[$('html').attr('lang')] + location.search;
            url = Drupal.removeURLParameter(url, 'selected');
            window.history.replaceState(variantInfo, variantInfo.title, url);
          }

          $('.language-switcher-language-url .language-link').each(function () {
            $(this).attr('href', variantInfo.url[$(this).attr('hreflang')])
          });

          if (typeof variantInfo.promotions !== 'undefined') {
            $('.promotions-full-view-mode', node).html(variantInfo.promotions);
          }

          if (typeof variantInfo.free_gift_promotions !== 'undefined') {
            $('.free-gift-promotions-full-view-mode', node).html(variantInfo.free_gift_promotions);
          }
          // Refresh price block as per sku data.
          $(node).find('.content__title_wrapper .price-block').html(variantInfo.price);
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
