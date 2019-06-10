/**
 * @file
 * JS file for sku gallery format library.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.skuGalleryFormat = {
    attach: function (context, settings) {
      // For sku gallery format view mode of SKU, we display promotion links
      // attached to the sku. This view mode is used on SRP / PLP / Promo pages.
      // On promo page we want to display the promotion as normal text (only for
      // the promotion currently accessed and display other promotions as link).
      // This was the only thing stopping us from using the same cache on all
      // pages so we are doing that in JS.
      $('body.nodetype--acq_promotion .sku-promotion-link').each(function () {
        if ($(this).attr('href') == window.location.pathname) {
          $(this).replaceWith($(this).html());
        }
      });
    }
  };

})(jQuery, Drupal);
