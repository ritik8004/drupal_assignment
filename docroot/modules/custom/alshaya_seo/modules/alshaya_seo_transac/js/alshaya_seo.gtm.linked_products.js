/**
 * @file
 * JS code to integrate with GTM for Related Product section.
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.seoGoogleTagManagerLinkedProducts = {
    attach: function (context, settings) {
      var impressions = [];
      var body = $('body');
      var gtmPageType = body.attr('gtm-container');
      var listName = body.attr('gtm-list-name');
      var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]:not(".impression-processed"):visible', context);
      var currencyCode = body.attr('gtm-currency');
      if ((gtmPageType === 'product detail page') || (gtmPageType === 'cart page')) {
        var count_pdp_items = 1;
        if (!drupalSettings.hasOwnProperty('impressions_position')) {
          drupalSettings.impressions_position = [];
        }

        productLinkSelector.each(function () {
          // Fetch attributes for this product.
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';

          var pdpListName = '';
          var upSellCrossSellSelector = $(this).closest('.view-product-slider').parent('.views-element-container').parent();
          if (!$(this).closest('.owl-item').hasClass('cloned') && !upSellCrossSellSelector.hasClass('mobile-only-block')) {
            // Check whether the product is in US or CS region & update list accordingly.
            if (listName.indexOf('placeholder') > -1) {
              if (upSellCrossSellSelector.hasClass('horizontal-crossell')) {
                pdpListName = listName.replace('placeholder', 'CS');
              }
              else if (upSellCrossSellSelector.hasClass('horizontal-upell')) {
                pdpListName = listName.replace('placeholder', 'US');
              }
              else if (upSellCrossSellSelector.hasClass('horizontal-related')) {
                pdpListName = listName.replace('placeholder', 'RELATED');
              }
            }

            impression.list = pdpListName;
            impression.position = count_pdp_items;
            impressions.push(impression);
            drupalSettings.impressions_position[$(this).attr('data-nid') + '-' + pdpListName] = count_pdp_items;
            count_pdp_items++;
          }
        });

        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, impressions);
      }
    }
  };

})(jQuery, Drupal);
