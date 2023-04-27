/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal, drupalSettings) {

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
      $(document).once('product-size-click').on('click', '.configurable-select .select2Option ul a', function () {
        // Return if view mode is modal.
        var viewMode = $(this).closest('.entity--type-node').attr('data-vmode');
        if (Drupal.hasValue(viewMode) && viewMode === 'modal') {
          return;
        }
        // We have different configurable size options available.
        // For eg: VS has band_size and cup_size also.
        var sizeWrapper = $(this).closest('.configurable-select');
        var sizeCode = sizeWrapper.find('select').attr('data-configurable-code');
        // Available size options for size click.
        // To make sure that only size click events are tracked and
        // not other configurable options.
        var availableSizeLabels = drupalSettings.productSizeOptions;
        if (Drupal.hasValue(availableSizeLabels) && availableSizeLabels.hasOwnProperty(sizeCode)) {
          // Set size in '{size_label}: {size_value}' format.
          var sizeLabel = availableSizeLabels[sizeCode];
          var eventLabel = $(this).attr('data-value');
          Drupal.alshayaSeoGtmPushEcommerceEvents({
            eventAction: 'pdp size click',
            eventLabel: `${sizeLabel}: ${eventLabel}`,
          });
        }
      });

      // Push product size click event to GTM.
      // For the products which have size groups available.
      $(document).once('pdp-size-click').on('click', '.group-wrapper .select2Option ul a', function () {
        // Return if view mode is modal.
        var viewMode = $(this).closest('.entity--type-node').attr('data-vmode');
        if (Drupal.hasValue(viewMode) && viewMode === 'modal') {
          return;
        }
        var eventLabel = $(this).attr('data-value');
        var groupLabel = '';
        if ($('.group-anchor-wrapper').length) {
          groupLabel = $('.group-anchor-wrapper').find('a.active').text();
        }
        eventLabel = Drupal.hasValue(groupLabel)
          ? `Size: ${groupLabel}, ${eventLabel}`
          : `Size: ${eventLabel}`;
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp size click',
          eventLabel,
        });
      });

      // Push product size guide click event to GTM.
      // For the products which have size guide available.
      $('.size-guide-link').once('product-size-guide-click').on('click', function () {
        Drupal.alshayaSeoGtmPushSizeGuideEvents('open');
      });

      // Push product color click event to GTM.
      // For default pdp layout only.
      $(document).once('product-color-click').on('click', '.form-item-configurables-color .select2Option ul a', function () {
        // Return if view mode is modal.
        var viewMode = $(this).closest('.entity--type-node').attr('data-vmode');
        if (Drupal.hasValue(viewMode) && viewMode === 'modal') {
          return;
        }
        var color = $(this).attr('data-value');
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp color click',
          eventLabel: color,
        });
      });

      // Push product color click event to GTM.
      // For magazine pdp layout only.
      $(document).once('pdp-color-click').on('click', '.colour-swatch .select2Option ul a', function () {
        // Return if view mode is modal.
        var viewMode = $(this).closest('.entity--type-node').attr('data-vmode');
        if (Drupal.hasValue(viewMode) && viewMode === 'modal') {
          return;
        }
        var color = $(this).attr('data-color-label');
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
        var sharingMedium = $(this).attr('displaytext') ? $(this).attr('displaytext') : '';

        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'share this page',
          eventLabel: sharingMedium,
        });
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
