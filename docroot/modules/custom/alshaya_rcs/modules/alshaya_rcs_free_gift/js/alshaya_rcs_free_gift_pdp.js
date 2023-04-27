/**
 * @file
 * RCS Free gift PDP js file for all layouts.
 */

window.commerceBackend = window.commerceBackend || {};

(function alshayaRcsFreeGiftPdp($, Drupal, drupalSettings, RcsEventManager) {

  /**
   * Event listener of page entity to redirect to 404 for free gift PDPs.
   */
  RcsEventManager.addListener('alshayaPageEntityLoaded', async function pdpPageEntityLoaded(e) {
    var mainProduct = e.detail.entity;
    // For free gift products we don't display PDP. Redirecting to 404.
    if (Drupal.hasValue(window.commerceBackend.isFreeGiftSku) && window.commerceBackend.isFreeGiftSku(mainProduct)) {
      var rcs404 = `${drupalSettings.rcs['404Page']}?referer=${globalThis.rcsWindowLocation().pathname}`;
      document.body.classList.add('hidden');
      return globalThis.rcsRedirectToPage(rcs404);
    }
  });

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

})(jQuery, Drupal, drupalSettings, RcsEventManager);
