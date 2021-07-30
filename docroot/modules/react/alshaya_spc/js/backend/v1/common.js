import Axios from 'axios';
import Cookies from 'js-cookie';
import getStringMessage from '../../utilities/strings';
import { getStorageInfo, removeStorageInfo, setStorageInfo } from '../../utilities/storage';

drupalSettings = window.drupalSettings;
window.commerceBackend = window.commerceBackend || {};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns {bool}
 */
const isAnonymousUserWithoutCart = () => (
  // @TODO: Remove Cookies.get('Drupal.visitor.acq_cart_id') check when we
  // uninstall alshaya_acm module.
  drupalSettings.user.uid === 0
    && !Cookies.get('PHPSESSID')
    && !Cookies.get('Drupal.visitor.acq_cart_id')
);

/**
 * Get the complete path for the middleware API.
 *
 * @param {string} path
 *  The API path.
 *
 * @returns {string}
 *   The complete middware API url.
 */
const i18nMiddleWareUrl = (path) => {
  const langcode = window.drupalSettings.path.currentLanguage;
  return `${window.drupalSettings.alshaya_spc.middleware_url}/${path}?lang=${langcode}`;
};

/**
 * Make an AJAX call to middleware.
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
const callMiddlewareApi = (url, method, data) => {
  const ajaxCallParams = {
    url: i18nMiddleWareUrl(url),
    method,
    headers: {
      'Content-Type': 'application/json',
    },
  };

  if (typeof data !== 'undefined') {
    ajaxCallParams.data = data;
  }

  return Axios(ajaxCallParams);
};

/**
 * Formats the error message as required for cart.
 *
 * @param {int} code
 *   The response code.
 * @param {string} message
 *   The response message.
 */
const returnExistingCartWithError = (code, message) => {
  let cart = window.commerceBackend.getCartDataFromStorage();
  const error = {
    error: true,
    error_code: code,
    error_message: message,
    response_message: [message, 'error'],
  };

  cart = cart ? cart.cart : {};
  cart = Object.assign(cart, error);

  return { data: cart };
};

/**
 * Calls the update cart middleware API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
const updateCart = (data) => callMiddlewareApi('cart/update', 'POST', JSON.stringify(data)).then(
  (response) => {
    // Check if we have tried to call middleware when the commerce backend is
    // set to V2.
    if (
      typeof response.data.error !== 'undefined'
      && response.data.error_code === 612
    ) {
      if (!sessionStorage.getItem('reloadOnBackendSwitch')) {
        // Reload the page only once. The caches are expected to be cleared till
        // then.
        sessionStorage.setItem('reloadOnBackendSwitch', 1);
        window.location.reload();
      }
      return returnExistingCartWithError(
        response.status,
        getStringMessage('backend_obsolete_version'),
      );
    }

    return response;
  },
);

/**
 * Gets the cached cart data.
 *
 * @returns {object|null}
 *   Processed cart data else null.
 */
window.commerceBackend.getCartDataFromStorage = () => getStorageInfo('cart_data');

/**
 * Sets the cart data.
 *
 * @param data
 *   The cart data.
 */
window.commerceBackend.setCartDataInStorage = (data) => {
  if (typeof data.cart.cart_id === 'undefined' || data.cart.cart_id === null) {
    return;
  }
  const cartInfo = { ...data };
  cartInfo.last_update = new Date().getTime();
  setStorageInfo(cartInfo);
};

/**
 * Removes the cart data from storage.
 */
window.commerceBackend.removeCartDataFromStorage = () => {
  removeStorageInfo('cart_data');

  // Remove last selected payment on page load.
  // We use this to ensure we trigger events for payment method
  // selection at-least once and not more than once.
  removeStorageInfo('last_selected_payment');
};

export {
  callMiddlewareApi,
  isAnonymousUserWithoutCart,
  updateCart,
};
