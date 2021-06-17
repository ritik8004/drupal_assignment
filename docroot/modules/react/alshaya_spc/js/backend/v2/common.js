import Axios from 'axios';
import qs from 'qs';
import { logger } from './utility';
import { cartErrorCodes, getDefaultErrorMessage } from './error';

window.commerceBackend = window.commerceBackend || {};

/**
 * Gets the cart ID for existing cart.
 *
 * @returns {string}
 *   The cart id.
 */
window.commerceBackend.getCartId = () => {
  const cartId = localStorage.getItem('cart_id');
  if (typeof cartId === 'string' || typeof cartId === 'number') {
    return cartId;
  }
  return null;
};

// Contains the raw unprocessed cart data.
let rawCartData = null;

/**
 * Stores the raw cart data object into the storage.
 *
 * @param {object} data
 *   The raw cart data object.
 */
window.commerceBackend.setRawCartDataInStorage = (data) => {
  rawCartData = data;
};

/**
 * Fetches the raw cart data object from the static storage.
 */
window.commerceBackend.getRawCartDataFromStorage = () => rawCartData;

/**
 * Object to serve as static cache for processed cart data over the course of a
 * request.
 */
let staticCartData = null;

/**
 * Gets the cart data.
 *
 * @returns {object|null}
 *   Processed cart data else null.
 */
window.commerceBackend.getCartDataFromStorage = () => staticCartData;

/**
 * Sets the cart data to storage.
 *
 * @param data
 *   The cart data.
 */
window.commerceBackend.setCartDataInStorage = (data) => {
  const cartInfo = { ...data };
  cartInfo.last_update = new Date().getTime();
  staticCartData = cartInfo;
};

/**
 * Unsets the stored cart data.
 */
window.commerceBackend.removeCartDataFromStorage = () => {
  staticCartData = null;
};

/**
 * Global constants.
 */

// Magento method, to set for 2d vault (tokenized card) transaction.
// @See CHECKOUT_COM_VAULT_METHOD in \App\Service\CheckoutCom\APIWrapper
const checkoutComVaultMethod = () => 'checkout_com_cc_vault';

// Magento method, to append for UAPAPI vault (tokenized card) transaction.
// @See CHECKOUT_COM_UPAPI_VAULT_METHOD in \App\Service\CheckoutCom\APIWrapper
const checkoutComUpapiVaultMethod = () => 'checkout_com_upapi_vault';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null || typeof cartId === 'undefined') {
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
 * @param {Promise} apiResponse
 *   The response from the API.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const handleResponse = (apiResponse) => {
  // Deep clone the response object.
  const response = JSON.parse(JSON.stringify(apiResponse));
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
 *   The data to send with the request.
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

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
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
 * Make an AJAX call to Drupal API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {string} data
 *   The data to send with the request.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const callDrupalApi = (url, method, data) => {
  const headers = {};
  const params = {
    url: `/${window.drupalSettings.path.currentLanguage}${url}`,
    method,
    data,
  };

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
    Object.keys(data).forEach((optionName) => {
      if (optionName === 'form_params') {
        headers['Content-Type'] = 'application/x-www-form-urlencoded';
        params.data = qs.stringify(data[optionName]);
      }
    });
  }

  return Axios(params);
};

/**
 * Formats the error message as required for cart.
 *
 * @param {int} code
 *   The response code.
 * @param {string} message
 *   The response message.
 */
const getFormattedError = (code, message) => ({
  error: true,
  error_code: code,
  error_message: message,
  response_message: [message, 'error'],
});

export {
  isAnonymousUserWithoutCart,
  callDrupalApi,
  callMagentoApi,
  checkoutComUpapiVaultMethod,
  checkoutComVaultMethod,
  getCartSettings,
  getFormattedError,
};
