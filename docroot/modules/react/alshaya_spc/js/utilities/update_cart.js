import axios from 'axios';

import { cartAvailableInStorage } from './get_cart';
import { i18nMiddleWareUrl } from './i18n_url';

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
 * @param promo_code
 * @returns {boolean}
 */
export const applyRemovePromo = function (action, promo_code) {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const api_url = updateCartApiUrl();

  return axios.post(api_url, {
    action,
    promo: promo_code,
    cart_id: cart,
  })
    .then((response) => response.data, (error) => {
    // Processing of error here.
    });
};

export const updateCartItemData = function (action, sku, quantity) {
  let cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  // Dispatch event with sku details before localStorage update.
  if (cart.items.hasOwnProperty(sku)) {
    const data = {
      qty: quantity,
      item: cart.items[sku],
    };
    const event = new CustomEvent('updateCartItemData', { bubbles: true, detail: { data } });
    document.dispatchEvent(event);
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const api_url = updateCartApiUrl();

  return axios.post(api_url, {
    action,
    sku,
    cart_id: cart,
    quantity,
  })
    .then((response) => response.data, (error) => {
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

  const api_url = updateCartApiUrl();
  return axios.post(api_url, {
    action,
    payment_info: data,
    cart_id: cart,
  }).then((response) => response.data, (error) => {
    // Processing of error here.
    console.error(error);
  });
};
