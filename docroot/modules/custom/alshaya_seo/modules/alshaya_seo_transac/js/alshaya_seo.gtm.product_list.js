/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal, debounce) {

  Drupal.behaviors.seoGoogleTagManagerProductList = {
    attach: function (context, settings) {
      // Trigger incase of page load & filter selected from PLP.
      $(window).once('alshaya-seo-gtm-product-list').on('scroll load', debounce(function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('.view-alshaya-product-list'), settings, event);
      }, 500));
      $(window).once('alshaya-seo-gtm-product-list-pagehide').on('pagehide', function (event) {
        Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(Drupal.alshaya_seo_gtm_prepare_impressions, $('.view-alshaya-product-list'), settings, event);
      });

      // Attach click handler to product list elements. Products get loaded by
      // AJAX also hence this is placed inside attach behaviors.
      $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context).once('product-list-clicked').on('click', function () {
        var that = $(this);
        var position = parseInt($(this).attr('list-item-position'));
        Drupal.alshaya_seo_gtm_push_product_clicks(that, drupalSettings.gtm.currency, $('body').attr('gtm-list-name'), position);
      });
    }
  };
})(jQuery, Drupal, Drupal.debounce);
