/**
 * @file
 * RCS Free gift for Cart js file.
 */

window.commerceBackend = window.commerceBackend || {};

(function alshayaRcsFreeGiftCart($, Drupal, drupalSettings) {

  /**
   * Drupal behaviour to attach for opening free gift modal on Cart page.
   */
  Drupal.behaviors.rcsFreeGiftsCart = {
    attach: function rcsFreeGiftsCart(context, settings) {
      // Modal view for the free gift on Cart page.
      $('.gift-message a, a.free-gift-modal').on('click', function openModalHandler(e) {
          // Stop redirection.
          e.preventDefault();
          // Start opening free gift modal.
          window.commerceBackend.startFreeGiftModalProcess(
            $(this).data('sku').split(','),
            $(this).data('back-to-collection'),
            $('.coupon-code').length ? $('.coupon-code')[0].textContent : null,
          );
        }
      );
    }
  };

})(jQuery, Drupal, drupalSettings);
