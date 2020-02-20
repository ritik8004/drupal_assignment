import axios from 'axios';

import {removeCartFromStorage} from './storage';
import { updateCartApiUrl } from './update_cart';
import { cartAvailableInStorage } from './get_cart';

/**
 * Get shipping methods.
 *
 * @param cart_id
 * @param data
 * @returns {boolean}
 */
export const getShippingMethods = function (cart_id, data) {
  var middleware_url = window.drupalSettings.alshaya_spc.middleware_url;

  return axios.post(middleware_url + '/cart/shipping-methods' , {
    data: data,
    cart_id: cart_id
  })
    .then((response) => {
      return response.data;
  }, (error) => {
    // Processing of error here.
  });
}

/**
 * Get payment methods.
 *
 * @param cart_id
 * @returns {boolean}
 */
export const getPaymentMethods = function (cart_id) {
  var middleware_url = window.drupalSettings.alshaya_spc.middleware_url;

  return axios.get(middleware_url + '/cart/' + cart_id + '/payment-methods')
    .then((response) => {
      return response.data;
  }, (error) => {
    // Processing of error here.
  });
}

/**
 * Place order.
 *
 * @param cart_id
 * @param payment_method
 * @returns {boolean}
 */
export const placeOrder = function (cart_id , payment_method) {
  var middleware_url = window.drupalSettings.alshaya_spc.middleware_url;

  let data = {
    'paymentMethod': {
      'method': payment_method
    }
  };
  return axios.post(middleware_url + '/cart/place-order', {
    'data': data,
    'cart_id': cart_id
  })
    .then((response) => {
      // Remove cart info from storage.
      removeCartFromStorage();
  }, (error) => {
    // Processing of error here.
  });
}

export const addShippingInCart = function (action, data) {
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
      shipping_info: data,
      cart_id: cart,
    })
    .then((response) => {
      return response.data;
    }, (error) => {
      // Processing of error here.
    });
}
