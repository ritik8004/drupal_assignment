/**
 * @file
 * JS code to integrate with GTM for Product into sliders.
 */

(function ($, Drupal, debounce) {

  Drupal.alshayaSeoGtmProductSlider = Drupal.alshayaSeoGtmProductSlider || {};

  Drupal.behaviors.alshayaSeoGtmProductSlider = {
    attach: function (context, settings) {
      /**
       * Product click handler for product sliders on homepage and PDP.
       */
      $('.view-product-slider article > a', context).once('product-clicked').on('click', function () {
        var that = $(this).closest('article');
        var subListName = Drupal.alshayaSeoGtmProductSlider.getRecommendationListName($(this));
        // Get the position of the item in the carousel.
        var position = parseInt($(this).closest('.views-row').data('list-item-position'));
        Drupal.alshaya_seo_gtm_push_product_clicks(that, drupalSettings.gtm.currency, subListName, position);
      });
    }
  }

  Drupal.behaviors.seoGoogleTagManagerProductSliderList = {
    attach: function (context, settings) {
      $(window).once('product-carousel-scroll').on('scroll touchmove', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_carousel_product_impression, $('.view-product-slider'), settings, event);
      }, 500));

      $(window).once('product-carousel-pagehide').on('pagehide', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_carousel_product_impression, $('.view-product-slider'), settings, event);
      });

      $(document).once('product-slider-prev-next').on('click', '.view-product-slider .slick-prev, .view-product-slider .slick-next', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_carousel_product_impression, $('.view-product-slider'), settings, event);
      });
    }
  };

  /**
   * Get the list name for the recommended product.
   *
   * @param object element
   *   The jquery object of the recommended product.
   *
   * @return string
   *    The list name.
   */
  Drupal.alshayaSeoGtmProductSlider.getRecommendationListName = function (element) {
    var label = element.closest('.views-element-container').siblings('.subtitle').text();
    var listName = $('body').attr('gtm-list-name');

    if (listName.indexOf('placeholder') > -1) {
      return productRecommendationsSuffix + listName.replace('placeholder', label).toLowerCase();
    }
    else {
      return (productRecommendationsSuffix + listName + '-' + label).toLowerCase();
    }
  }

  /**
   * Helper function to prepare productImpressions.
   *
   * @param context
   *
   * @param event
   */
  Drupal.alshaya_seo_gtm_prepare_carousel_product_impression = function (context, event) {
    var impressions = [];
    var body = $('body');
    // We need to also check that the item is not in a slick clone.
    var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]:not(".impression-processed, .slick-cloned article"):visible', context);
    var listName = body.attr('gtm-list-name');

    if (productLinkSelector.length > 0) {
      var finalListName = '';
      var previousLabel = '';
      productLinkSelector.each(function () {
        // 40 is passed as the second argument as in product sliders we can see
        // that much of the top portion of the slider images is white in color
        // and hence user needs to scroll more to view the product and that is
        // when we trigger the GTM event.
        if ($(this).isCarouselElementInViewPort(0, 40)) {
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));

          // Find the carousel title.
          var label = $(this).closest('.views-element-container').siblings('.subtitle').text();
          if ((previousLabel === '') || (previousLabel !== label)) {
            // Find out the no. of elements in the current carousel that have
            // been processed. This is executed for the first carousel that is
            // viewed by the user(previousLabel === '').
            // When user views another carousel before expiration of the timer
            // then the carousel label should be different and hence we again
            // get the new count of processed items for that carousel.
            var productLinkProcessedSelector = $(this).closest('.views-element-container').find('.impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
            previousLabel = label;
          }

          if (listName.search('placeholder') > 0) {
            finalListName = listName.replace('placeholder', label);
          }
          else {
            finalListName = listName + '-' + label;
          }
          impression.list = Drupal.alshayaSeoGtmProductSlider.getRecommendationListName($(this));

          impression.position = parseInt($(this).closest('.views-row').data('list-item-position'));
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
          $(this).addClass('impression-processed');
        }
      });
    }

    return impressions;
  };

})(jQuery, Drupal, Drupal.debounce);
