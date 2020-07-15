/**
 * @file
 * JS code to integrate with GTM for Product into sliders.
 */

(function ($, Drupal, debounce) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManagerProductSliderList = {
    attach: function (context, settings) {
      $(window).once('product-carousel-scroll').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_push_carousel_product_impression, $('.view-product-slider'), settings, event);
      }, 500));

      $(document).once('product-slider-prev-next').on('click', '.view-product-slider .slick-prev, .view-product-slider .slick-next', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_push_carousel_product_impression, $('.view-product-slider'), settings, event);
      });
    }
  };

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param context
   *
   * @param settings
   */
  Drupal.alshaya_seo_gtm_push_carousel_product_impression = function (context, event) {
    var impressions = [];
    var body = $('body');
    var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]:not(".impression-processed"):visible', context);
    var productLinkProcessedSelector = $('.impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var listName = body.attr('gtm-list-name');
    var gtmPageType = body.attr('gtm-container');
    // Send impression for each product added on page (page 1 or X).
    var count = productLinkProcessedSelector.length + 1;
    var recommendationsParentSelector = $('.view-product-slider').parent('.views-element-container').parent();
    var listClassName = '';

    if ((gtmPageType === 'product detail page') || (gtmPageType === 'cart page')) {
      // Check whether the product is in US or CS region & update list accordingly.
      if (listName.indexOf('placeholder') > -1) {
        var classNames = ['horizontal-crossell', 'horizontal-upell', 'horizontal-related'];
        for (let i = 0; i < classNames.length; i++) {
          var className = classNames[i];
          if (recommendationsParentSelector.hasClass(className) && !$(context).find(className).hasClass('mobile-only-block')) {
            var elementSelector = $('.' + className + ' .view-product-slider');
            if ((!elementSelector.closest('.owl-item').hasClass('cloned'))) {
              listClassName = className;
            }
          }
        }
      }
    }

    if (listClassName === 'horizontal-crossell') {
      listName = listName.replace('placeholder', 'CS');
    }
    else if (listClassName === 'horizontal-upell') {
      listName = listName.replace('placeholder', 'US');
    }
    else if (listClassName === 'horizontal-related') {
      listName = listName.replace('placeholder', 'RELATED');
    }

    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        // 40 is passed as the second argument as in product sliders we can see
        // that much of the top portion of the slider images is white in color
        // and hence user needs to scroll more to view the product and that is
        // when we trigger the GTM event.
        if ($(this).isElementInViewPort(0, 40, true)) {
          $(this).addClass('impression-processed');
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = (productRecommendationsSuffix + listName).toLowerCase();
          impression.position = count;
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
          count++;
        }
      });
    }

    return impressions;
  };

})(jQuery, Drupal, Drupal.debounce);
