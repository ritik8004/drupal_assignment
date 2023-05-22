/**
 * @file
 * JS code to integrate wishlist with GTM for Algolia.
 */

(function ($, Drupal, debounce, drupalSettings) {
  Drupal.behaviors.algoliaWishlist = {
    attach: function (context, settings) {
      // Add product impression event with wishlist listing page.
      $('#my-wishlist').once('seoGoogleTagManager').on('wishlist-results-updated', function (event, results) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('#my-wishlist'), drupalSettings, event);

        // Capture the product click GTM event from wishlist listing page.
        $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', $('#my-wishlist')).once('product-list-clicked').on('click', function (e) {
          var that = $(this);
          var position = parseInt($(this).attr('data-insights-position'));
          // Product Click GTM event should not be triggered
          // when adding/removing from cart, when color swatch or
          // add to cart button is clicked and when adding/removing
          // product from wishlist.
          if (!$(e.target).closest('.swatches').length
            && !$(e.target).closest('.addtobag-button-container').length
            && !$(e.target).closest('.wishlist-button-wrapper').length) {
            Drupal.alshaya_seo_gtm_push_product_clicks(that, drupalSettings.gtm.currency, $('body').attr('gtm-list-name'), position);
          }
        });
      });

      // Add product impression event with wishlist listing page on page scroll.
      $(window).once('alshaya-seo-gtm-product-wishlist-algolia').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('#my-wishlist'), drupalSettings, event);
      }, 500));

      // Push article swatch arrow click events to GTM.
      $('#my-wishlist').once('bind-swatch-slider-click').on('click', '.article-swatch-wrapper button.slick-arrow', function () {
        // Get clicked arrow for eventLabel.
        var eventLabel = $(this).hasClass('slick-prev') ? 'left' : 'right';
        Drupal.alshayaSeoGtmPushSwatchSliderClick(eventLabel);
      });
    }
  };
})(jQuery, Drupal, Drupal.debounce, drupalSettings);
