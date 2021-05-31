/* eslint no-return-await: "error" */

import Axios from 'axios';
import { logger } from './utility';
import { cartErrorCodes, getDefaultErrorMessage } from './error';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartData = window.Drupal.alshayaSpc.getCartData();
  if (cartData === null || typeof cartData === 'undefined' || typeof cartData.cart_id === 'undefined') {
    if (window.drupalSettings.user.uid === 0) {
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
 * Handle errors and messages.
 *
 * @param {Promise} response
 *   The response from the API.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const handleResponse = (response) => {
  // In case we don't receive any response data.
  if (typeof response.data === 'undefined' || response.data.length === 0) {
    logger.error(`Error while doing MDC api. Response result is empty. Status code: ${response.status}`);

    const error = {
      data: {
        error: true,
        error_code: 500,
        error_message: getDefaultErrorMessage(),
      },
    };
    return new Promise((resolve) => resolve(error));
  }

  // Treat each status code.
  if (response.status >= 500) {
    // Server error responses.
    response.data.error = true;
    response.data.error_code = 600;
    response.data.error_message = 'Back-end system is down';
    //
  } else if (response.status === 404) {
    // Client error responses.
    response.data.error = true;
    response.data.error_code = 404;
    response.data.error_message = response.data.message;
    //
  } else if (response.status !== 200) {
    // All other responses.
    response.data.error = true;
    if (typeof response.data.message !== 'undefined') {
      response.data.error_message = response.data.message;
      const errorCode = (typeof response.data.error_code !== 'undefined') ? response.data.error_code : '-';
      logger.error(`Error while doing MDC api call. Error message: ${response.data.message}, Code: ${errorCode}, Response code: ${response.status}.`);

      if (response.status === 400 && typeof response.data.error_code !== 'undefined' && response.data.error_code === cartErrorCodes.cartCheckoutQuantityMismatch) {
        response.data.error_code = cartErrorCodes.cartCheckoutQuantityMismatch;
      } else {
        response.data.error_code = 500;
      }
    }
  } else if (typeof response.data.messages !== 'undefined' && typeof response.data.messages.error !== 'undefined') {
    const error = response.data.messages.error.shift();
    //
    delete (response.data.messages);
    response.data.error = true;
    response.data.error_code = error.code;
    response.data.error_message = error.message;
    //
    logger.error(`Error while doing MDC api call. Error message: ${error.message}`);
  }

  return response;
};

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
      'Alshaya-Channel': 'web',
    },
  };

  if (typeof data !== 'undefined' && Object.keys(data).length > 0) {
    params.data = data;
  }

  return Axios(params)
    .then((response) => handleResponse(response))
    .catch((error) => {
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        return handleResponse(error.response);
      }
      if (error.request) {
        // The request was made but no response was received
        return handleResponse(error.request);
      }
      // Something happened in setting up the request that triggered an Error
      return logger.error(error.message);
    });
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
  let response = null;
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

  response = await callMagentoApi(`/rest/V1/guest-carts/${cartId}/items`, 'POST', itemData);
  if (response.data.error === true) {
    if (response.data.error_code === 404) {
      // 400 errors happens when we try to post to invalid cart id.
      const postString = JSON.stringify(itemData);
      logger.error(`Error updating cart. Cart Id ${cartId}. Post string ${postString}`);
      // Remove the cart from storage.
      localStorage.removeItem('cart_id');
      // Create a new cart.
      await window.commerceBackend.createCart();
    }

    const error = {
      data: {
        error: response.data.error,
        error_code: response.data.error_code,
        error_message: getDefaultErrorMessage(),
      },
    };
    return new Promise((resolve) => resolve(error));
  }

  return window.commerceBackend.getCart();
};

export {
  isAnonymousUserWithoutCart,
  callMagentoApi,
  updateCart,
};
