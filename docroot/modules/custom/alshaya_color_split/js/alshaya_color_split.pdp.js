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
      $('article.entity--type-node').once('alshaya-color-split').on('combination-changed variant-selected', function (event, variant, code) {
        var sku = $(this).attr('data-sku');
        if (typeof drupalSettings.productInfo[sku] === 'undefined') {
          return;
        }

        var variantInfo = drupalSettings.productInfo[sku]['variants'][variant];
        if ($(this).find('.content--item-code .field__value').html() === variantInfo.parent_sku) {
          return;
        }

        if ($(this).attr('data-vmode') === 'full') {
          window.history.pushState(variantInfo, variantInfo.title, variantInfo.url[$('html').attr('lang')]);

          $('.language-switcher-language-url .language-link').each(function () {
            $(this).attr('href', variantInfo.url[$(this).attr('hreflang')])
          });
        }

        $(this).find('.content--item-code .field__value').html(variantInfo.parent_sku);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
