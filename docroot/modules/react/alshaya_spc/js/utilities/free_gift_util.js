import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from './checkout_util';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Add free gift to cart.
 */
export const addFreeGift = async (freeGiftLink) => {
  const freeGiftMainSku = freeGiftLink.getAttribute('data-variant-sku');
  const coupon = freeGiftLink.getAttribute('data-coupon');
  const type = freeGiftLink.getAttribute('data-sku-type');
  const promoRuleId = freeGiftLink.getAttribute('data-promo-rule-id');
  let postData = {};

  if (type === 'simple') {
    postData = {
      promo: coupon,
      sku: freeGiftMainSku,
      configurable_values: [],
      variant: null,
      type,
      langcode: drupalSettings.path.currentLanguage,
      promoRuleId,
    };
  } else {
    const form = freeGiftLink.closest('form');
    const currentSelectedVariant = form
      .querySelector('[name="selected_variant_sku"]')
      .getAttribute('value');

    const configurableValues = await window.commerceBackend.processFreeGiftConfigurables(
      freeGiftMainSku,
      form,
    );

    postData = {
      promo: coupon,
      sku: freeGiftMainSku,
      configurable_values: configurableValues,
      variant: currentSelectedVariant,
      type,
      langcode: drupalSettings.path.currentLanguage,
      promoRuleId,
    };
  }
  window.commerceBackend.addFreeGift(postData).then((cartresponse) => {
    if (Object.keys(cartresponse.data).length !== 0) {
      // Refreshing mini-cart.
      const miniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => cartresponse.data } });
      document.dispatchEvent(miniCartEvent);

      // Refreshing cart components..
      const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => cartresponse.data } });
      document.dispatchEvent(refreshCartEvent);

      // Closing the modal window.
      const closeModal = document.querySelector('.ui-dialog-titlebar-close');
      if (hasValue(closeModal)) {
        closeModal.click();
      }
      removeFullScreenLoader();
    }
  });
};

/**
 * Open free gift product detail modal.
 */
export const openFreeGiftModal = (e) => {
  const freeGiftLink = e.detail.data();
  if (freeGiftLink !== null) {
    addFreeGift(freeGiftLink);
    showFullScreenLoader();
  }
};

/**
 * Open free gift listing modal.
 */
export const selectFreeGiftModal = (e) => {
  const selectFreeGiftLink = e.detail.data();
  addFreeGift(selectFreeGiftLink);
  showFullScreenLoader();
};

/**
 * Get id of cart free gift modal link.
 */
export const getCartFreeGiftModalId = (skuCode) => {
  const id = skuCode
    ? `spc-free-gift-${skuCode.replace(/\s+/g, '').replace(/,/g, '-')}`
    : 'spc-free-gift';

  return id;
};

/**
 * Add class to body and trigger free gift modal.
 */
export const openCartFreeGiftModal = (sku) => {
  const body = document.querySelector('body');
  body.classList.add('free-gifts-modal-overlay');
  // Render free gift modal on cart page.
  window.commerceBackend.startFreeGiftModalProcess(
    document.getElementById(getCartFreeGiftModalId(sku)).dataset.sku.split(','),
    false,
    document.getElementsByClassName('coupon-code')[0].textContent,
  );
};

/**
 * Select and add free gift item.
 */
export const selectFreeGift = (codeValue, sku, skuType, promoType) => {
  if (codeValue !== undefined) {
    // Open free gift modal for collection free gifts.
    if (promoType === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
      const body = document.querySelector('body');
      body.classList.add('free-gifts-modal-overlay');
      window.commerceBackend.startFreeGiftModalProcess(
        document.getElementById(getCartFreeGiftModalId(sku)).dataset.sku.split(','),
        false,
        document.getElementsByClassName('coupon-code')[0].textContent,
      );
    } else if ((promoType === 'FREE_GIFT_SUB_TYPE_ALL_SKUS') && (skuType === 'configurable')) {
      openCartFreeGiftModal(sku);
    } else {
      document.getElementById('promo-code').value = codeValue.trim();
      document.getElementById('promo-action-button').click();
    }
  }
};
