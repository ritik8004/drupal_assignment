import axios from 'axios';

import { cartAvailableInStorage } from './get_cart';
import { i18nMiddleWareUrl } from './i18n_url';
import { getInfoFromStorage } from './storage';
import { dispatchCustomEvent } from './events';

/**
 * Get the middleware update cart endpoint.
 *
 * @returns {string}
 */
export function updateCartApiUrl() {
  return i18nMiddleWareUrl('update-cart');
}

/**
 * Get the middleware update cart endpoint.
 *
 * @returns {string}
 */
export function restoreCartApiUrl() {
  return i18nMiddleWareUrl('restore-cart');
}

/**
 * Apply/Remove the promo code.
 *
 * @param action
 * @param promoCode
 * @returns {boolean}
 */
export const applyRemovePromo = function (action, promoCode) {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const apiUrl = updateCartApiUrl();

  return axios.post(apiUrl, {
    action,
    promo: promoCode,
    cart_id: cart,
  })
    .then((response) => response.data, (error) => {
    // Processing of error here.
    });
};

export const updateCartItemData = function (action, sku, quantity) {
  let cart = cartAvailableInStorage();
  // If cart not available.
  if (cart === false
    || cart === null
    || cart === 'empty') {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const apiUrl = updateCartApiUrl();

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
          && localCart.cart.items.hasOwnProperty(sku)) {
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
    });
};

export const addPaymentMethodInCart = (action, data) => {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const apiUrl = updateCartApiUrl();
  return axios.post(apiUrl, {
    action,
    payment_info: data,
    cart_id: cart,
  }).then((response) => response.data, (error) => {
    // Processing of error here.
    console.error(error);
  });
};
