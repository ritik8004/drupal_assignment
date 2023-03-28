/**
 * @file
 * RCS Free gift PDP js file.
 */

(function ($, Drupal, drupalSettings, RcsEventManager) {
  window.commerceBackend = window.commerceBackend || {};

  /**
   * Renders free gift for the given product.
   *
   * @param {object} product
   *   The main product object.
   */
  function renderFreeGiftPdp(product) {
    var freeGiftWrapper = jQuery('.free-gift-promotions');
    var freeGiftHtml = globalThis.rcsPhRenderingEngine.computePhFilters(product, 'promotion_free_gift');
    // Render the HTML in the free gift wrapper.
    freeGiftWrapper.html(freeGiftHtml);
    freeGiftWrapper.addClass('rcs-loaded');
    globalThis.rcsPhApplyDrupalJs(document);
  }

  /**
   * Event listener of page entity to get the free gift product info.
   */
  RcsEventManager.addListener('alshayaPageEntityLoaded', async function pageEntityLoaded(e) {
    // For magazine V2 layout, we process free gift api response data and pass
    // it to react for render. Check alshaya_rcs_pdp_magazine_v2.js code.
    if (drupalSettings.alshayaRcs.pdpLayout === 'pdp-magazine_v2') {
      return;
    }
    var mainProduct = e.detail.entity;
    // Get the list of all the available Free gifts.
    var freeGiftPromotion = mainProduct.free_gift_promotion;
    // We support displaying only one free gift promotion for now.
    if (freeGiftPromotion.length > 0 && freeGiftPromotion[0].total_items > 0) {
      var giftItemList = freeGiftPromotion[0].gifts;
      var freeGiftProduct = null;
      for (var i = 0; i < giftItemList.length; i++) {
        // Fetch first valid free gift data.
        freeGiftProduct = window.commerceBackend.fetchValidatedFreeGift(giftItemList[i].sku);
        if (Drupal.hasValue(freeGiftProduct)) {
          // If a valid free gift found, break. AS we will only cache 1 free gift data.
          // For multiple free gifts, we will load the free gift product info during modal view.
          // To save PDP render time.
          break;
        } else {
          // If its not a valid free gift sku, delete it from response.
          // And continue looking for next valid free gift sku.
          // As, if we keep the invalid sku, it will render in data-sku attribute
          // in html and when we open free gift modal, it will try to fetch data
          // for that invalid free gift sku.
          // Check fetchValidatedFreeGift() for more details on invalid sku.
          delete giftItemList[i];
        }
      }

      mainProduct.free_gift_promotion[0].gifts = giftItemList.flat();
      // Render if at least one valid free gift found.
      if (mainProduct.free_gift_promotion[0].gifts.length) {
        // Render the free gift item.
        renderFreeGiftPdp(mainProduct);
      }
    }
  });

  /**
   * Drupal behaviour to attach for opening free gift modal on PDP.
   */
  Drupal.behaviors.rcsFreeGiftsPdp = {
    attach: function (context, settings) {
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
