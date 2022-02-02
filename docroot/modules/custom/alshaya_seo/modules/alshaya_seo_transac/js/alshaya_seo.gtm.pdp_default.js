/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal) {

  $(window).on('load', function () {
    // Trigger productDetailView event.
    Drupal.alshayaSeoGtmPushProductDetailView($('.entity--type-node'));
  });
})(jQuery, Drupal);
