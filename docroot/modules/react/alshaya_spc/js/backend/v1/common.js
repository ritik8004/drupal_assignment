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
 * Formats the error message as required for cart.
 *
 * @param {int} code
 *   The response code.
 * @param {string} message
 *   The response message.
 */
const returnExistingCartWithError = (code, message) => ({
  data: {
    error: true,
    error_code: code,
    error_message: message,
    response_message: [message, 'error'],
  },
});

/**
 * Calls the update cart middleware API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
const updateCart = (data) => callMiddlewareApi('cart/update', 'POST', JSON.stringify(data)).catch(
  (error) => {
    // Check if we have tried to call middleware when the commerce backend is
    // set to V2.
    if (
      error.response.status === 403
        && error.response.data === 'Trying to acccess V1 when version is V2.'
        && !sessionStorage.getItem('reloadOnBackendSwitch')
    ) {
      // Reload the page only once. The caches are expected to be cleared till
      // then.
      sessionStorage.setItem('reloadOnBackendSwitch', 1);
      window.location.reload();
    }

    return returnExistingCartWithError(error.response.status, error.response.data);
  },
);

export {
  callMiddlewareApi,
  isAnonymousUserWithoutCart,
  updateCart,
};
