/**
 * @file
 * JS code to integrate with GTM for Product into sliders.
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.seoGoogleTagManagerProductSliderList = {
    attach: function (context, settings) {
      $(window).on('load scroll', function (event) {
        var listClassName = Drupal.alshaya_seo_gtm_get_carousel_list_class(context, settings);
        if (listClassName) {
          Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression($('.' + listClassName +' .view-product-slider'), settings);
        }
        else {
          Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression($('.view-product-slider'), settings);
        }
      });

      $(document).once('product-slider-prev-next').on('click', '.view-product-slider .slick-prev, .view-product-slider .slick-next', function () {
        var listClassName = Drupal.alshaya_seo_gtm_get_carousel_list_class(context, settings);
        if (listClassName) {
          Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression($('.' + listClassName +' .view-product-slider'), settings);
        }
        else {
          Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression($('.view-product-slider'), settings);
        }
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
  Drupal.alshaya_seo_gtm_get_carousel_list_class = function (context, settings) {
    var body = $('body');
    var gtmPageType = body.attr('gtm-container');
    var upSellCrossSellSelector = $('.view-product-slider').parent('.views-element-container').parent();
    var listName = body.attr('gtm-list-name');
    var listClassName = '';
    if ((gtmPageType === 'product detail page') || (gtmPageType === 'cart page')) {
      if (!$(this).closest('.owl-item').hasClass('cloned') && !upSellCrossSellSelector.hasClass('mobile-only-block')) {
        // Check whether the product is in US or CS region & update list accordingly.
        if (listName.indexOf('placeholder') > -1) {
          if (upSellCrossSellSelector.hasClass('horizontal-crossell')) {
            listClassName = 'horizontal-crossell';
          }
          else if (upSellCrossSellSelector.hasClass('horizontal-upell')) {
            listClassName = 'horizontal-upell';
          }
          else if (upSellCrossSellSelector.hasClass('horizontal-related')) {
            listClassName = 'horizontal-related';
          }
        }
      }
    }
    return listClassName;
  }

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param context
   *
   * @param settings
   */
  Drupal.alshaya_seo_gtm_prepare_and_push_carousel_product_impression = function (context, settings) {
    var impressions = [];
    var body = $('body');
    var currencyCode = body.attr('gtm-currency');
    var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var productLinkProcessedSelector = $('.impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var listName = body.attr('gtm-list-name');
    var gtmPageType = body.attr('gtm-container');
    // Send impression for each product added on page (page 1 or X).
    var count = productLinkProcessedSelector.length + 1;

    var upSellCrossSellSelector = $('.view-product-slider').parent('.views-element-container').parent();

    var pdpListName = listName;

    if (Drupal.alshaya_seo_gtm_get_carousel_list_class(context, settings) == 'horizontal-crossell') {
      pdpListName = listName.replace('placeholder', 'CS');
    }
    else if (Drupal.alshaya_seo_gtm_get_carousel_list_class(context, settings) == 'horizontal-upell') {
      pdpListName = listName.replace('placeholder', 'US');
    }
    else if (Drupal.alshaya_seo_gtm_get_carousel_list_class(context, settings) == 'horizontal-related') {
      pdpListName = listName.replace('placeholder', 'RELATED');
    }

    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        var offset = 0;
        if (!$(this).hasClass('impression-processed') && $(this).is(':visible') && $(this).isElementInViewPort(offset)) {
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

})(jQuery, Drupal);
