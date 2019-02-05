/**
 * @file
 * JS file for sku gallery format library.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.skuGalleryFormat = {
    attach: function (context, settings) {
      $('body.nodetype--acq_promotion .sku-promotion-link').each(function () {
        if ($(this).attr('href') == window.location.pathname) {
          $(this).replaceWith($(this).html());
        }
      });
    }
  };

})(jQuery, Drupal);
