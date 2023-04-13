/**
 * @file
 * RCS Free gift PDP js file for Classic and Magazine.
 */

window.commerceBackend = window.commerceBackend || {};

(function alshayaRcsFreeGiftPdpClassicAndMagazine($, Drupal, drupalSettings, RcsEventManager) {

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
    var mainProduct = e.detail.entity;
    // Get the list of all the available Free gifts.
    var freeGiftPromotion = mainProduct.free_gift_promotion;
    // We support displaying only one free gift promotion for now.
    if (freeGiftPromotion.length && freeGiftPromotion[0].total_items) {
      var giftItemList = freeGiftPromotion[0].gifts;
      var freeGiftProduct = null;
      for (var i = 0; i < giftItemList.length; i++) {
        // Fetch first valid free gift data.
        freeGiftProduct = window.commerceBackend.fetchValidFreeGift(giftItemList[i].sku);
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
          // Check fetchValidFreeGift() for more details on invalid sku.
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

})(jQuery, Drupal, drupalSettings, RcsEventManager);
