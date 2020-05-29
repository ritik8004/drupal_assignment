/**
 * @file
 * JS code to integrate with GTM for Product into sliders.
 */

(function ($, Drupal, debounce) {
  'use strict';
  var listClassName = '';

  Drupal.behaviors.seoGoogleTagManagerProductSliderList = {
    attach: function (context, settings) {
      $(window).once('product-carousel-load-scroll').on('load scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_process_carousel(context, settings, event);
      }, 500));

      $(document).once('product-slider-prev-next').on('click', '.view-product-slider .slick-prev, .view-product-slider .slick-next', function (event) {
        Drupal.alshaya_seo_gtm_process_carousel(context, settings, event);
      });
    }
  };

  /**
   * Helper function to carousel_list_class.
   *
   * @param context
   *
   * @param settings
   */
  Drupal.alshaya_seo_gtm_process_carousel = function (context, settings, event) {
    var body = $('body');
    var gtmPageType = body.attr('gtm-container');
    var listName = body.attr('gtm-list-name');

    if ((gtmPageType === 'product detail page') || (gtmPageType === 'cart page')) {
      // Check whether the product is in US or CS region & update list accordingly.
      if (listName.indexOf('placeholder') > -1) {
        if ($('div').hasClass('horizontal-crossell') && (!$(context).find('.horizontal-crossell').hasClass('mobile-only-block'))) {
          var crossellSelecter = $('.horizontal-crossell .view-product-slider');
          if ((!crossellSelecter.closest('.owl-item').hasClass('cloned'))) {
            Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_push_carousel_product_impression, crossellSelecter, settings, event);
          }
        }

        if ($('div').hasClass('horizontal-upell') && (!$(context).find('.horizontal-upell').hasClass('mobile-only-block'))) {
          var upellSelecter = $('.horizontal-upell .view-product-slider');
          if (!upellSelecter.closest('.owl-item').hasClass('cloned')) {
            Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_push_carousel_product_impression, upellSelecter, settings, event);
          }
        }

        if ($('div').hasClass('horizontal-related') && !$(context).find('.horizontal-related').hasClass('mobile-only-block')) {
          var relatedSelecter = $('.horizontal-related .view-product-slider');
          if (!relatedSelecter.closest('.owl-item').hasClass('cloned')) {
            Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_push_carousel_product_impression, relatedSelecter, settings, event);
          }
        }
      }
    }
    else {
      Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_push_carousel_product_impression, $('.view-product-slider'), settings, event);
    }
  }

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param context
   *
   * @param settings
   */
  Drupal.alshaya_seo_gtm_push_carousel_product_impression = function (context, eventType, currentQueueSize) {
    var impressions = [];
    var body = $('body');
    var currencyCode = body.attr('gtm-currency');
    var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]:not(".impression-processed"):visible', context);
    var productLinkProcessedSelector = $('.impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var listName = body.attr('gtm-list-name');
    var gtmPageType = body.attr('gtm-container');
    // Send impression for each product added on page (page 1 or X).
    var count = productLinkProcessedSelector.length + 1;

    var upSellCrossSellSelector = $('.view-product-slider').parent('.views-element-container').parent();

    var pdpListName = listName;

    if (listClassName === 'horizontal-crossell') {
      pdpListName = listName.replace('placeholder', 'CS');
    }
    else if (listClassName === 'horizontal-upell') {
      pdpListName = listName.replace('placeholder', 'US');
    }
    else if (listClassName === 'horizontal-related') {
      pdpListName = listName.replace('placeholder', 'RELATED');
    }

    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        // 40 is passed as the second argument as in product sliders we can see
        // that much of the top portion of the slider images is white in color
        // and hence user needs to scroll more to view the product and that is
        // when we trigger the GTM event.
        if ($(this).isElementInViewPort(0, 40)) {
          $(this).addClass('impression-processed');
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = pdpListName;
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
