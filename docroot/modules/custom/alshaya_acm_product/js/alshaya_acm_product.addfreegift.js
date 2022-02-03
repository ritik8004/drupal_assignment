/**
 * @file
 * Alshaya Add Free Gift JS.
 */

(function ($, Drupal) {

  Drupal.behaviors.AddFreeGift = {
    attach: function (context) {

      $('.free-gift-detail-modal button.select-free-gift').on('click', function (e) {
        e.preventDefault();
        var productDetailFreeGiftModal = new CustomEvent('openFreeGiftModalEvent', {bubbles: true, detail: { data: () => this }});
        document.dispatchEvent(productDetailFreeGiftModal);
      })

      $('.free-gift-listing-modal .select-free-gift').on('click', function (e) {
        e.preventDefault();
        var selectFreeGiftModal = new CustomEvent('selectFreeGiftModalEvent', {bubbles: true, detail: { data: () => this }});
        document.dispatchEvent(selectFreeGiftModal);
      })
    }
  };

})(jQuery, Drupal);
