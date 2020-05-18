/**
 * @file
 * Alshaya Social auth popup.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.dynamic_yield = {
    attach: function (context, settings) {
      // .product-quick-view-link will have to be used in the HTML for the
      // modal to open.
      $('.product-quick-view-link').once('modal-open').click(function () {
        if ($(window).width() < 768) {
          return;
        }
        event.preventDefault();
        Drupal.ajax({
          url: Drupal.url($(this).attr('data-url-quick-view').replace('/' + drupalSettings.path.pathPrefix, '')),
          progress: { type: 'fullscreen' },
          dialogType: $(this).attr('data-dialog-type'),
          dialog: {dialogClass: 'dynamic-yield-recommendations'}
        })
        .execute();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
