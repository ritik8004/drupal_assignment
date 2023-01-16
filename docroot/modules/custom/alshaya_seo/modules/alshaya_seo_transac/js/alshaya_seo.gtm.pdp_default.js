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

      // Push product size click event to GTM.
      // For default and magazine pdp layouts.
      $(document).once('product-size-click').on('click', 'div.form-item-configurables-size .select2Option ul a', function () {
        let size = $(this).attr('data-value');
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp size click',
          eventLabel: size,
        });
      });

      // Push product color click event to GTM.
      // For default pdp layout only.
      $(document).once('product-color-click').on('click', '.form-item-configurables-color .select2Option ul a', function () {
        let color = $(this).attr('data-value');
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp color click',
          eventLabel: color,
        });
      });

      // Push product color click event to GTM.
      // For magazine pdp layout only.
      $(document).once('product-color-click').on('click', '.colour-swatch .select2Option ul a', function () {
        let color = $(this).attr('data-color-label');
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp color click',
          eventLabel: color,
        });
      });

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
        let sharingMedium = $(this).attr('displaytext') ? $(this).attr('displaytext') : '';

        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'share this page',
          eventLabel: sharingMedium,
        });
      });
    }
  }
})(jQuery, Drupal);
