(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.alshayaSpc = Drupal.alshayaSpc || {};

  /**
   * Get the complete path for the middleware API.
   *
   * @param {string} path
   *  The API path.
   */
  const i18nMiddleWareUrl = function (path) {
    const langcode = window.drupalSettings.path.currentLanguage;
    return window.drupalSettings.alshaya_spc.middleware_url + '/' + path + '?lang=' + langcode;
  }

  /**
   * Check if user is anonymous and without cart.
   *
   * @returns bool
   */
  Drupal.alshayaSpc.isAnonymousUserWithoutCart = function () {
    const cookies = document.cookie.split('; ');
    const cartExists = cookies.find(function (row) {
      return row.startsWith('PHPSESSID')
    });

    return drupalSettings.user.uid === 0 && typeof cartExists === 'undefined';
  }

  /**
   * Make an AJAX call to middleware.
   *
   * @param {string} url
   *   The url to send the request to.
   * @param {string} method
   *   The request method.
   * @param {object} data
   *   The object to send for POST request.
   */
  const callMiddlewareApi = function(url, method, data) {
    const ajaxCallParams = {
      url: i18nMiddleWareUrl(url),
      method: method,
      headers: {
        'Content-Type': 'application/json',
      }
    }

    if (typeof data !== 'undefined') {
      ajaxCallParams.data = data;
    }

    return $.ajax(ajaxCallParams)
    .then(
      function (response) {
        // Return the data in the expected format.
        return {data: response};
      },
      function (error) {
        // Return a promise with the error message so that it can be catched.
        return new Promise(function (resolve, reject) {
          reject({message: 'Request failed with status code ' + error.status});
        });
      }
    );
  }

  /**
   * Calls the cart get API.
   */
  Drupal.alshayaSpc.getCart = function() {
    return callMiddlewareApi('cart/get', 'GET');
  }

  /**
   * Get cart data for checkout.
   */
  Drupal.alshayaSpc.getCartForCheckout = function() {
    return callMiddlewareApi('cart/checkout', 'GET');
  }

  /**
   * Calls the cart restore API.
   */
  Drupal.alshayaSpc.restoreCart = function() {
    return callMiddlewareApi('cart/restore', 'GET');
  }

  /**
   * Calls the cart update API.
   *
   * @param {object} data
   *   The data object to send in the API call.
   */
  Drupal.alshayaSpc.updateCart = function(data) {
    return callMiddlewareApi('cart/update', 'POST', JSON.stringify(data));
  }

})(jQuery, Drupal, drupalSettings);
