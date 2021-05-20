import Axios from 'axios';
import Cookies from 'js-cookie';

drupalSettings = window.drupalSettings;

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
 * Calls the update cart middleware API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
const updateCart = (data) => callMiddlewareApi('cart/update', 'POST', JSON.stringify(data));

export {
  callMiddlewareApi,
  isAnonymousUserWithoutCart,
  updateCart,
};
