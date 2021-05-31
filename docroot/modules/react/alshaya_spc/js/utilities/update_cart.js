import { cartAvailableInStorage } from './get_cart';
import dispatchCustomEvent from './events';
import validateCartResponse from './validation_util';

/**
 * Apply/Remove the promo code.
 *
 * @param action
 * @param promoCode
 * @returns {boolean}
 */
export const applyRemovePromo = (action, promoCode) => {
  let cart = cartAvailableInStorage();
  if (cart === false
    || cart === null
    || cart === 'empty') {
    window.location.href = Drupal.url('cart');
    return null;
  }

  // Remove any promo coupons errors on promo
  // coupon application success.
  const promoError = document.querySelector('#promo-message.error');
  if (promoError !== null) {
    promoError.outerHTML = '<div id="promo-message" />';
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  return window.commerceBackend.applyRemovePromo({
    action,
    promo: promoCode,
    cart_id: cart,
  })
    .then((response) => {
      validateCartResponse(response.data);
      return response.data;
    }, (error) => {
      // Processing of error here.
      Drupal.logJavascriptError('apply-remove-promo', error, GTM_CONSTANTS.CART_ERRORS);
    });
};

export const updateCartItemData = (action, sku, quantity) => {
  let cart = cartAvailableInStorage();
  // If cart not available.
  if (cart === false
    || cart === null
    || cart === 'empty') {
    window.location.href = Drupal.url('cart');
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  return window.commerceBackend.updateCartItemData({
    action,
    sku,
    cart_id: cart,
    quantity,
  })
    .then((response) => {
      // If no error, trigger event for GTM.
      if (response.data.error === undefined) {
        // We fetch from local storage and do some checkes.
        // We are doing this because on delete operation,
        // sku is removed from storage and thus we need
        // data before storage update.
        const localCart = window.commerceBackend.getCartData();
        if (localCart.cart !== undefined
          && localCart.cart.items !== undefined
          && Object.prototype.hasOwnProperty.call(localCart.cart.items, sku)) {
          const data = {
            qty: quantity,
            item: localCart.cart.items[sku],
          };
          dispatchCustomEvent('updateCartItemData', {
            data,
          });
        }
      }

      return response.data;
    }, (error) => {
      // Processing of error here.
      Drupal.logJavascriptError('update-cart-item-data', error, GTM_CONSTANTS.CART_ERRORS);
    });
};

export const addPaymentMethodInCart = (action, data) => window.commerceBackend.addPaymentMethod({
  action,
  payment_info: data,
}).then((response) => {
  if (!validateCartResponse(response.data)) {
    if (typeof response.data.message !== 'undefined') {
      Drupal.logJavascriptError(action, response.message, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }
    return null;
  }
  return response.data;
}, (error) => {
  // Processing of error here.
  Drupal.logJavascriptError('add-payment-method-in-cart', error, GTM_CONSTANTS.PAYMENT_ERRORS);
});
