import Axios from 'axios';
import Cookies from 'js-cookie';

drupalSettings = window.drupalSettings;
window.commerceBackend = {};

/**
 * Get the complete path for the middleware API.
 *
 * @param {string} path
 *  The API path.
 */
const i18nMiddleWareUrl = (path) => {
  const langcode = window.drupalSettings.path.currentLanguage;
  return `${window.drupalSettings.alshaya_spc.middleware_url}/${path}?lang=${langcode}`;
};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => (
  drupalSettings.user.uid === 0
    && !Cookies.get('PHPSESSID')
    && !Cookies.get('Drupal.visitor.acq_cart_id')
);

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
 * Calls the cart get API.
 */
window.commerceBackend.getCart = () => callMiddlewareApi('cart/get', 'GET');

/**
 * Get cart data for checkout.
 */
window.commerceBackend.getCartForCheckout = () => callMiddlewareApi('cart/checkout', 'GET');

/**
 * Calls the cart restore API.
 */
window.commerceBackend.restoreCart = () => callMiddlewareApi('cart/restore', 'GET');

/**
 * Calls the cart update API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 */
window.commerceBackend.updateCart = (data) => callMiddlewareApi('cart/update', 'POST', JSON.stringify(data));
