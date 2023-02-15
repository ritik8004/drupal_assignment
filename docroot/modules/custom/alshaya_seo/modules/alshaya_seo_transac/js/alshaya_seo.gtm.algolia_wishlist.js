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
          // Don't trigger GTM product click event when color
          // swatch is click.
          if (!$(e.target).closest('.swatches').length) {
            Drupal.alshaya_seo_gtm_push_product_clicks(that, drupalSettings.gtm.currency, $('body').attr('gtm-list-name'), position);
          }
        });
      });

      // Add product impression event with wishlist listing page on page scroll.
      $(window).once('alshaya-seo-gtm-product-wishlist-algolia').on('scroll', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('#my-wishlist'), drupalSettings, event);
      }, 500));
    }
  };
})(jQuery, Drupal, Drupal.debounce, drupalSettings);
