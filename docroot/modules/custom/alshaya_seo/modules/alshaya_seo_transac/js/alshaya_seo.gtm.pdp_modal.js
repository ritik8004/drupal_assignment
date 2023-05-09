/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaSeoGtmPdpModalBehavior = {
    attach: function (context, settings) {
      // Get the product opened in popup.
      var node = $('.entity--type-node[data-vmode="modal"]').not('[data-sku *= "#"]');
      if (node.length > 0) {
        // Trigger productDetailView event.
        Drupal.alshayaSeoGtmPushProductDetailView(node);
      }

      // Push product size click event to GTM.
      // For default and magazine pdp layouts.
      $(document).once('product-modal-size-click').on('click', '.configurable-select .select2Option ul a', function () {
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
            product_view_type: 'recommendations_popup',
          });
        }
      });

      // Push product size click event to GTM.
      // For the products which have size groups available.
      $(document).once('pdp-modal-size-click').on('click', '.group-wrapper .select2Option ul a', function () {
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
          product_view_type: 'recommendations_popup',
        });
      });

      // Push product color click event to GTM.
      // For default pdp layout only.
      $(document).once('product-modal-color-click').on('click', '.form-item-configurables-color .select2Option ul a', function () {
        var color = $(this).attr('data-value');
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp color click',
          eventLabel: color,
          product_view_type: 'recommendations_popup',
        });
      });

      // Push product color click event to GTM.
      // For magazine pdp layout only for HNM brand.
      $(document).once('pdp-modal-color-click').on('click', '.form-item-configurables-article-castor-id .select2Option ul a', function () {
        var color = $(this).attr('data-color-label');
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'pdp color click',
          eventLabel: color,
          product_view_type: 'recommendations_popup',
        });
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
