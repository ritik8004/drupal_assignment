/**
 * @file
 * JS code to integrate with GTM for Product into product list.
 */

(function ($, Drupal) {
  'use strict';

  var productDetailViewTriggered = false;
  Drupal.behaviors.alshayaSeoGtmPdpBehavior = {
    attach: function (context, settings) {
      var node = jQuery('.entity--type-node.node--view-mode-full').not('[data-sku *= "#"]');
      if (!productDetailViewTriggered && node.length > 0) {
        productDetailViewTriggered = true;
        // Trigger productDetailView event.
        Drupal.alshayaSeoGtmPushProductDetailView(node);
      }
    }
  }
})(jQuery, Drupal);
