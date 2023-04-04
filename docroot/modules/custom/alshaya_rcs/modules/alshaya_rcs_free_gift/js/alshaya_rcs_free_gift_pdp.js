/**
 * @file
 * RCS Free gift PDP js file for all layouts.
 */

window.commerceBackend = window.commerceBackend || {};

(function alshayaRcsFreeGiftPdp($, Drupal, drupalSettings) {
  /**
   * Drupal behaviour to attach for opening free gift modal on PDP.
   */
  Drupal.behaviors.rcsFreeGiftsPdp = {
    attach: function rcsFreeGiftsPdp(context, settings) {
      // Modal view for the free gift on PDP.
      $('.free-gift-promotions .free-gift-wrapper .free-gift-message a, a.free-gift-modal')
        .once('free-gift-processed-pdp')
        .on('click',
          function openModalHandler(e) {
            // Stop redirection.
            e.preventDefault();
            // Start opening free gift modal.
            window.commerceBackend.startFreeGiftModalProcess(
              $(this).data('sku').split(','),
              $(this).data('back-to-collection')
            );
          }
        );
    }
  };

})(jQuery, Drupal, drupalSettings);
