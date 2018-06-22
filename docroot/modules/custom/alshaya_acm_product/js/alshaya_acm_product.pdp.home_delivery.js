/**
 * @file
 * Store Finder - PDP.
 */


(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.pdpHomeDelivery = {
    attach: function (context, settings) {

      $('#pdp-home-delivery', context).once('initiate-hd').each(function () {
        // Check if we have to show the block as disabled. Since accordion classes
        // are added in JS, this is handled in JS.
        if ($(this).data('state') === 'disabled') {
          $('#pdp-home-delivery.home-delivery .c-accordion_content').addClass('hidden-important');
          $('#pdp-home-delivery.home-delivery').accordion('option', 'disabled', true);
        }
      });
    }
  };

})(jQuery, Drupal);