/**
 * Fetches data from the remote source and returns to the callback.
 *
 * @param {string} url
 *   The of the remote source.
 * @param {string} method
 *   The request method.
 * @param {object} options
 *   The request options like data, headers etc.
 * @param {string} callback
 *   The function to call on successful response.
 */
exports.invokeApi = async function (request) {
  const headers = {};

  if (typeof request.headers !== 'undefined') {
    request.headers.forEach(function (header) {
      headers[header[0]] = header[1];
    });
  }

  var mainApiResponse = null;

  const mainApi = jQuery.ajax({
    url: drupalSettings.rcs.commerceBackend.baseUrl + request.uri,
    method: request.method,
    headers,
    data: request.data,
    // This will help to prevent the spinner from stopping on ajax complete in
    // cart_notification.js.
    rcs: true,
    success: function (response) {
      mainApiResponse = response;
      return response;
    },
    error: function () {
      console.log('Could not fetch data!');
    }
  });

  const eventData = {
    request,
    promises: [mainApi],
    extraData: {},
  }

  // Allow the custom code to initiate other AJAX requests in parallel
  // and make the rendering blocked till all of them are finished.
  RcsEventManager.fire('invokingApi', eventData);

  return jQuery.when(...eventData.promises).then(
    function jQueryWhenThen() {
      // Return the main api response as is here,
      // we don't care about other responses.
      return mainApiResponse;
    }
  )
};

/**
 * Fetches data from remote source and returns to the callback synchronously.
 *
 * @param {string} url
 *   The of the remote source.
 * @param {string} method
 *   The request method.
 * @param {object} options
 *   The request options like data, headers etc.
 * @param {string} callback
 *   The function to call on successful response.
 */
 exports.invokeApiSynchronous = function (request) {
  const headers = {};
  let result = null;

  if (typeof request.headers !== 'undefined') {
    request.headers.forEach(function (header) {
      headers[header[0]] = header[1];
    });
  }

  jQuery.ajax({
    url: drupalSettings.rcs.commerceBackend.baseUrl + request.uri,
    method: request.method,
    headers,
    async: false,
    data: request.data,
    // This will help to prevent the spinner from stopping on ajax complete in
    // cart_notification.js.
    rcs: true,
    success: function (response) {
      result = response;
    },
    error: function () {
      console.log('Could not fetch data!');
    }
  });

  return result;
};
