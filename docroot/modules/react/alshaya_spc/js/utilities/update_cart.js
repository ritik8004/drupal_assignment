import axios from 'axios';

import {cartAvailableInStorage} from './get_cart';
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
  var cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const api_url = updateCartApiUrl();

  return axios.post(api_url, {
    action: action,
    promo: promo_code,
    cart_id: cart
  })
    .then((response) => {
      return response.data;
  }, (error) => {
    // Processing of error here.
  });
}

export const updateCartItemData = function (action, sku, quantity) {
  var cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const api_url = updateCartApiUrl();

  return axios.post(api_url, {
    action: action,
    sku: sku,
    cart_id: cart,
    quantity: quantity
  })
    .then((response) => {
    return response.data;
  }, (error) => {
    // Processing of error here.
  });
}

export const addPaymentMethodInCart = function (action, data) {
  var cart = cartAvailableInStorage();
  if (cart === false) {
    return null;
  }

  if (!Number.isInteger(cart)) {
    cart = cart.cart_id;
  }

  const api_url = updateCartApiUrl();
  return axios.post(api_url, {
    action: action,
    payment_info: data,
    cart_id: cart,
  })
    .then((response) => {
    return response.data;
  }, (error) => {
    // Processing of error here.
  });
}
