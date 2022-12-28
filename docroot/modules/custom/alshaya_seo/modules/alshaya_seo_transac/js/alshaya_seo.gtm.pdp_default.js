/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal) {

  var productDetailViewTriggered = false;
  Drupal.behaviors.alshayaSeoGtmPdpBehavior = {
    attach: function (context, settings) {
      var node = jQuery('.entity--type-node[data-vmode="full"]').not('[data-sku *= "#"]');
      if (!productDetailViewTriggered && node.length > 0) {
        productDetailViewTriggered = true;
        // Trigger productDetailView event.
        Drupal.alshayaSeoGtmPushProductDetailView(node);
      }

      // Push details click event to GTM.
      $('div.pdp-overlay-details').once('product-details-click').on('click', function () {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp clicks',
          eventLabel: 'details button',
        });
      });

      // Push home delivery click event to GTM.
      // Trigger only when accordion header is clicked.
      $('#pdp-home-delivery').once('home-delivery-click').on('click', '.ui-accordion-header', function () {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp clicks',
          eventLabel: 'home delivery',
        });
      });

      // Push cnc click event to GTM.
      $('#pdp-store-click-collect-list').once('cnc-click').on('click', '.ui-accordion-header', function () {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp clicks',
          eventLabel: 'click and collect',
        });
      });

      // Push share this page open event to GTM.
      // Need this for magazine layout only, for default layout we don't
      // have share this icon.
      $('.modal-share-this').once('share-this-open').on('click', '.share-icon', function () {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'share this page',
          eventLabel: 'open',
        });
      });

      // Push share this page click event to GTM.
      $('.sharethis-wrapper').once('share-this').on('click', 'span', function () {
        // Set sharing medium based on the element clicked.
        var sharingMedium = $(this).attr('displaytext') ? $(this).attr('displaytext') : '';

        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'share this page',
          eventLabel: sharingMedium,
        });
      });
    }
  }
})(jQuery, Drupal);
