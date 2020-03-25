/**
 * @file
 * JS code to integrate with GTM for Product into sliders.
 */

(function ($, Drupal, debounce) {
  'use strict';
  Drupal.behaviors.seoGoogleTagManagerProductSliderList = {
    attach: function (context, settings) {
      $(window).once('product-carousel-load-scroll').on('load scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_process_carousel(context, settings);
      }, 500));

      $(document).once('product-slider-prev-next').on('click', '.view-product-slider .slick-prev, .view-product-slider .slick-next', function () {
        Drupal.alshaya_seo_gtm_process_carousel(context, settings);
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
  Drupal.alshaya_seo_gtm_process_carousel = function (context, settings) {
    var body = $('body');
    var gtmPageType = body.attr('gtm-container');
    var listName = body.attr('gtm-list-name');
    var listClassName = '';

    if ((gtmPageType === 'product detail page') || (gtmPageType === 'cart page')) {
      // Check whether the product is in US or CS region & update list accordingly.
      if (listName.indexOf('placeholder') > -1) {
        if ($('div').hasClass('horizontal-crossell') && (!$(context).find('.horizontal-crossell').hasClass('mobile-only-block'))) {
          var crossellSelecter = $('.horizontal-crossell .view-product-slider');
          if ((!crossellSelecter.closest('.owl-item').hasClass('cloned'))) {
            Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression(crossellSelecter, settings, 'horizontal-crossell');
          }
        }

        if ($('div').hasClass('horizontal-upell') && (!$(context).find('.horizontal-upell').hasClass('mobile-only-block'))) {
          var upellSelecter = $('.horizontal-upell .view-product-slider');
          if (!upellSelecter.closest('.owl-item').hasClass('cloned')) {
            Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression(upellSelecter, settings, 'horizontal-upell');
          }
        }

        if ($('div').hasClass('horizontal-related') && !$(context).find('.horizontal-related').hasClass('mobile-only-block')) {
          var relatedSelecter = $('.horizontal-related .view-product-slider');
          if (!relatedSelecter.closest('.owl-item').hasClass('cloned')) {
            Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression(relatedSelecter, settings, 'horizontal-related');
          }
        }
      }
    }
    else {
      Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression($('.view-product-slider'), settings, listClassName);
    }
  }

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param context
   *
   * @param settings
   */
  Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression = function (context, settings, listClassName) {
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
        if ($(this).isElementInViewPort(0)) {
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
      if (impressions.length > 0) {
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, impressions);
      }
    }
  };

})(jQuery, Drupal, Drupal.debounce);
