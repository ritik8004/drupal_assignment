/* eslint no-return-await: "error" */

import Axios from 'axios';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartData = window.Drupal.alshayaSpc.getCartData();
  if (cartData === null || typeof cartData === 'undefined' || typeof cartData.cart_id === 'undefined') {
    if (drupalSettings.user.uid === 0) {
      return true;
    }
  }
  return false;
};

/**
 * Get the complete path for the Magento API.
 *
 * @param {string} path
 *  The API path.
 */
const i18nMagentoUrl = (path) => `${window.drupalSettings.cart.url}/${window.drupalSettings.cart.store}${path}`;

/**
 * Make an AJAX call to Magento API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} data
 *   The object to send for POST request.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const callMagentoApi = (url, method, data) => {
  const params = {
    url: i18nMagentoUrl(url),
    method,
    headers: {
      'Content-Type': 'application/json',
    },
  };

  if (typeof data !== 'undefined' && Object.keys(data).length > 0) {
    params.data = data;
  }

  // @todo error handling as found in MagentoApiWrapper::doRequest()
  return Axios(params);
};

/**
 * Calls the update cart API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
const updateCart = async (data) => {
  let cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    cartId = await window.commerceBackend.createCart();
  }
  if (cartId === null) {
    return new Promise((resolve) => resolve(cartId));
  }

  const itemData = {
    cartItem: {
      sku: data.variant_sku,
      qty: data.quantity,
      quote_id: cartId,
    },
  };

  const response = await callMagentoApi(`/rest/V1/guest-carts/${cartId}/items`, 'POST', itemData);
  if (response.data.error === true) {
    if (typeof response.data.error_message === 'undefined') {
      response.data.error_message = 'Error adding item to the cart.';
    }
    const error = {
      data: response.data,
    };
    return new Promise((resolve) => resolve(error));
  }

  return window.commerceBackend.getCart()
    .then((cartData) => new Promise((resolve) => resolve(cartData)));
};

export {
  isAnonymousUserWithoutCart,
  callMagentoApi,
  updateCart,
};
