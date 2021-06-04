import axios from 'axios';

import { cartAvailableInStorage } from './get_cart';
import i18nMiddleWareUrl from './i18n_url';
import { getInfoFromStorage } from './storage';
import dispatchCustomEvent from './events';
import validateCartResponse from './validation_util';

/**
 * Get the middleware update cart endpoint.
 *
 * @returns {string}
 */
export const updateCartApiUrl = () => i18nMiddleWareUrl('cart/update');

/**
 * Get the middleware update cart endpoint.
 *
 * @returns {string}
 */
export const restoreCartApiUrl = () => i18nMiddleWareUrl('cart/restore');

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

  const apiUrl = updateCartApiUrl();

  // Add smart agent location coordinates in cookie.
  if (typeof Drupal.smartAgent !== 'undefined') {
    Drupal.smartAgent.setLocationInCookie();
  }

  return axios.post(apiUrl, {
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

  const apiUrl = updateCartApiUrl();

  // Add smart agent location coordinates in cookie.
  if (typeof Drupal.smartAgent !== 'undefined') {
    Drupal.smartAgent.setLocationInCookie();
  }

  return axios.post(apiUrl, {
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
        const localCart = getInfoFromStorage();
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

export const addPaymentMethodInCart = (action, data) => {
  const apiUrl = updateCartApiUrl();
  // Add smart agent location coordinates in cookie.
  if (typeof Drupal.smartAgent !== 'undefined') {
    Drupal.smartAgent.setLocationInCookie();
  }
  return axios.post(apiUrl, {
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
};
