/* eslint no-return-await: "error" */

import Axios from 'axios';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null || typeof cartId === 'undefined') {
    if (drupalSettings.user.uid === 0) {
      return true;
    }
  }
  return false;
};

/**
 * Wrapper to get cart settings.
 *
 * @param {string} key
 *   The key for the configuration.
 * @returns {(number|string!Object!Array)}
 *   Returns the configuration.
 */
const getCartSettings = (key) => window.drupalSettings.cart[key];

/**
 * Get the complete path for the Magento API.
 *
 * @param {string} path
 *  The API path.
 */
const i18nMagentoUrl = (path) => `${getCartSettings('url')}/${getCartSettings('store')}${path}`;

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

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
    params.data = data;
  }

  // @todo error handling as found in MagentoApiWrapper::doRequest()
  return Axios(params);
};

/**
 * Object to serve as static cache for cart data over the course of a request.
 */
let cartData = null;

/**
 * Gets the stored cart data.
 */
const getCartData = () => cartData;

/**
 * Sets the cart data to static memory.
 *
 * @param {object} data
 *   The cart object to set.
 */
const setCartData = (data) => {
  const cartInfo = { ...data };
  cartInfo.last_update = new Date().getTime();
  cartData = cartInfo;
};

/**
 * Unsets the cart data in static memory.
 */
const removeCartData = () => {
  cartData = null;
};

/**
 * Calls the get cart API.
 *
 * @returns {promise}
 *   A promise object which resolves to a cart object or null.
 */
const getCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null) {
    return new Promise((resolve) => resolve(cartId));
  }

  // @todo: Handle error.
  return callMagentoApi(`/rest/V1/guest-carts/${cartId}/getCart`, 'GET', {})
    .then((response) => window.commerceBackend.processCartData(response.data));
};

/**
 * Calls the update cart API and returns the updated cart.
 * @todo Implement this function while working on the checkout page.
 *
 * @param {object} data
 *  The data to send.
 */
const updateCart = async () => null;

export {
  isAnonymousUserWithoutCart,
  callMagentoApi,
  updateCart,
  getCart,
  getCartData,
  setCartData,
  removeCartData,
};
