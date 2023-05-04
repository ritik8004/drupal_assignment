/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal) {

  Drupal.alshayaSeoPdpMagazineV2Gtm = Drupal.alshayaSeoPdpMagazineV2Gtm || {};

  // Trigger product impressions on scroll.
  $(document).once('gtm-events').on('scroll', debounce(function (event) {
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoPdpMagazineV2Gtm.prepareProductImpressions, $('.magv2-pdp-crossell-upsell-wrapper'), drupalSettings, event);
  }, 500));

  // Trigger product impressions on click of slider next/prev buttons.
  // Debouncing here adds some delay which helps to cover up for the time
  // which is taken be the slider movement animation.
  $(document).once('product-slider-prev-next').on('click', '.magv2-pdp-crossell-upsell-wrapper .slider-nav .slider-prev, .magv2-pdp-crossell-upsell-wrapper .slider-nav .slider-next', debounce(function (event) {
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshayaSeoPdpMagazineV2Gtm.prepareProductImpressions, $('.magv2-pdp-crossell-upsell-wrapper'), drupalSettings, event);
  }, 500));

  // Product click handler for product slider.
  $(document).once('product-clicked').on('click', '.magv2-pdp-crossell-upsell-wrapper .magv2-pdp-crossell-upsell-image-wrapper', function () {
    var subListName = Drupal.alshayaSeoPdpMagazineV2Gtm.getRecommendationListName($(this));
    // // Get the position of the item in the carousel.
    var position = parseInt($(this).attr('list-position'));
    Drupal.alshaya_seo_gtm_push_product_clicks($(this), drupalSettings.gtm.currency, subListName, position);
  });

  // Push home delivery click event to GTM.
  // Trigger only when accordion header is clicked.
  $(document).once('home-delivery-click').on('click', '.pdp-express-delivery-wrapper .express-delivery-title-wrapper', function () {
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'pdp clicks',
      eventLabel: 'home delivery',
    });
  });

  // Push cnc click event to GTM.
  $(document).once('cnc-click').on('click', '.magv2-pdp-click-and-collect-wrapper .magv2-click-collect-title-wrapper', function () {
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'pdp clicks',
      eventLabel: 'click and collect',
    });
  });

  // Push share this page click event to GTM.
  $(document).once('share-this').on('click', '.pdp-share-panel span, .pdp-share-panel button', function () {
    // Set sharing medium based on the element clicked.
    var sharingMedium = $(this).attr('displaytext') ? $(this).attr('displaytext') : '';
    if ($(this).hasClass('copy-button')) {
      sharingMedium = 'copy link';
    }
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'share this page',
      eventLabel: sharingMedium,
    });
  });

  /**
   * Prepares the impression list to send to Product Impressions event.
   *
   * @param {object} context
   *   The jQuery HTML wrapper object for which contains the list of items.
   * @param {string} eventType
   *   The type of event, eg. click, scroll etc.
   *
   * @return array
   *   Array of impressions.
   */
  Drupal.alshayaSeoPdpMagazineV2Gtm.prepareProductImpressions = function (context, eventType) {
    var listName = Drupal.alshayaSeoPdpMagazineV2Gtm.getRecommendationListName(context);
    var impressions = [];

    context.find('.magv2-pdp-crossell-upsell-image-wrapper:not(".impression-processed")').each(function () {
      var condition = true;
      condition = $(this).closest('.slick-slide').isElementInViewPort(0, 0);
      if (condition) {
        var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
        impression.list = listName;
        impression.position = parseInt($(this).attr('list-position'));
        // Keep variant empty for impression pages. Populated only post add to
        // cart action.
        impression.variant = '';
        impressions.push(impression);
        $(this).addClass('impression-processed');
      }
    });

    return impressions;
  }

    /**
   * Get the list name for the recommended product.
   *
   * @param object element
   *   The jquery object of the recommended product.
   *
   * @return string
   *    The list name.
   */
  Drupal.alshayaSeoPdpMagazineV2Gtm.getRecommendationListName = function (element) {
    var label = element.closest('.magv2-pdp-crossell-upsell-wrapper').find('.magv2-pdp-crossell-upsell-label').text();
    var listName = $('body').attr('gtm-list-name');

    return (productRecommendationsSuffix + listName.replace('placeholder', label)).toLowerCase();
  }

})(jQuery, Drupal);
