/**
 * @file
 * JS code to integrate with GTM for Product into sliders.
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.seoGoogleTagManagerProductList = {
    attach: function (context, settings) {
      // Don't allow for aloglia search.
      if(window.location.href.match(/#query/g)) {
        return;
      }

      $(document).once('bind-slick-slider').on('load scroll', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression_slider(context, settings);
      });

      $(document).once('bind-slick-slider-nav').on('click', '.view-product-slider .slick-prev, .view-product-slider .slick-next', function () {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression_slider(context, settings);
      } );
    }
  };

  /**
   * Helper function to push productImpression to GTM for sliders.
   *
   * @param customerType
   */
  Drupal.alshaya_seo_gtm_prepare_and_push_product_impression_slider = function (context, settings) {
    var impressions = [];
    var body = $('body');
    var currencyCode = body.attr('gtm-currency');
    var productLinkSelector = $('main [gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var productLinkProcessedSelector = $('main .impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var listName = body.attr('gtm-list-name');
    // Send impression for each product added on viewport.
    var count = productLinkProcessedSelector.length + 1;

    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        if (!$(this).hasClass('impression-processed') && $(this).is(':visible') && $(this).isElementInViewPortHorizontally(0)) {
          $(this).addClass('impression-processed');
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = listName;
          impression.position = count;
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
          count++;
        }
      });
      if (impressions.length > 0) {
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, impressions);
      }
    }
  };
})(jQuery, Drupal);
