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
  Drupal.behaviors.alshayaColorSplitPdp = {
    attach: function (context, settings) {
      // For simple products grouped together.
      $('[data-drupal-selector="edit-variants-in-group"]').once('alshaya-color-split').on('change', function () {
        var node = $(this).parents('article.entity--type-node:first');
        var sku = $(node).attr('data-sku');
        if (typeof drupalSettings.productInfo[sku] === 'undefined') {
          return;
        }

        if ($(this).val() == $('[name="selected_variant_sku"]', node).val()) {
          return;
        }

        $('[name="selected_variant_sku"]', node).val($(this).val());

        var variantInfo = drupalSettings.productInfo[sku]['group'][$(this).val()];
        Drupal.updateGallery(node, variantInfo.layout,variantInfo.gallery);
        if ($(node).attr('data-vmode') === 'full') {
          var url = variantInfo.url[$('html').attr('lang')] + location.search;
          url = Drupal.removeURLParameter(url, 'selected');
          window.history.pushState(variantInfo, variantInfo.title, url);

          $('.language-switcher-language-url .language-link').each(function () {
            $(this).attr('href', variantInfo.url[$(this).attr('hreflang')])
          });

          if (typeof variantInfo.promotions !== 'undefined') {
            $('.promotions-full-view-mode', node).html(variantInfo.promotions);
          }

          if (typeof variantInfo.free_gift_promotions !== 'undefined') {
            $('.free-gift-promotions-full-view-mode', node).html(variantInfo.free_gift_promotions);
          }
        }
      });

      $('.sku-base-form').once('alshaya-color-split').on('variant-selected', function (event, variant, code) {
        var node = $(this).parents('article.entity--type-node:first');
        var sku = $(this).attr('data-sku');
        if (typeof drupalSettings.productInfo[sku] === 'undefined') {
          return;
        }

        var variantInfo = drupalSettings.productInfo[sku]['variants'][variant];

        // We can have mix of color split and normal products.
        // Avoid processing further if we have a product which is normal but
        // color split module is enabled.
        if (typeof variantInfo.url === 'undefined') {
          return;
        }

        // Avoid processing again and again for variants of same color.
        if ($(node).find('.content--item-code .field__value').html() === variantInfo.parent_sku) {
          return;
        }

        if ($(node).attr('data-vmode') === 'full') {
          var url = variantInfo.url[$('html').attr('lang')] + location.search;
          url = Drupal.removeURLParameter(url, 'selected');
          window.history.pushState(variantInfo, variantInfo.title, url);

          $('.language-switcher-language-url .language-link').each(function () {
            $(this).attr('href', variantInfo.url[$(this).attr('hreflang')])
          });

          if (typeof variantInfo.promotions !== 'undefined') {
            $('.promotions-full-view-mode', node).html(variantInfo.promotions);
          }

          if (typeof variantInfo.free_gift_promotions !== 'undefined') {
            $('.free-gift-promotions-full-view-mode', node).html(variantInfo.free_gift_promotions);
          }
        }

        $(node).find('.content--item-code .field__value').html(variantInfo.parent_sku);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
