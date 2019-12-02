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
  Drupal.behaviors.alshayaColorSplitGroupConfigurable = {
    attach: function (context, settings) {
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

        // Updating the parent sku for selected variant.
        // @see alshaya_acm_product_form_sku_base_form_alter().
        $(this).find('.selected-parent-sku').val(variantInfo.parent_sku);

        // Avoid processing again and again for variants of same color.
        if ($(node).find('.content--item-code .field__value').html() === variantInfo.parent_sku) {
          return;
        }

        if ($(node).attr('data-vmode') === 'full') {
          if (window.location.pathname !== variantInfo.url[$('html').attr('lang')]) {
            var url = variantInfo.url[$('html').attr('lang')] + location.search;
            url = Drupal.removeURLParameter(url, 'selected');
            window.history.replaceState(variantInfo, variantInfo.title, url);
          }

          $('.language-switcher-language-url .language-link').each(function () {
            $(this).attr('href', variantInfo.url[$(this).attr('hreflang')]);
          });

          if (typeof variantInfo.promotions !== 'undefined') {
            var reattachedPromotions = $('.promotions-full-view-mode', node).html(variantInfo.promotions);

            // Attach Drupal Behaviors to add event listeners back.
            Drupal.alshayaPromotions.initializeDynamicPromotions(reattachedPromotions);
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
