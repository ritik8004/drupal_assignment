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
      $('.sku-base-form').once('alshaya-color-split').on('variant-selected', function (event, variant, code) {
        var node = $(this).parents('article.entity--type-node:first');
        var sku = $(this).attr('data-sku');
        if (typeof drupalSettings.productInfo[sku] === 'undefined') {
          return;
        }

        var variantInfo = drupalSettings.productInfo[sku]['variants'][variant];
        if ($(node).find('.content--item-code .field__value').html() === variantInfo.parent_sku) {
          return;
        }

        if ($(node).attr('data-vmode') === 'full') {
          var url = variantInfo.url[$('html').attr('lang')] + location.search;
          url = Drupal.removeURLParameter(url, 'selected');
          window.history.replaceState(variantInfo, variantInfo.title, url);

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
