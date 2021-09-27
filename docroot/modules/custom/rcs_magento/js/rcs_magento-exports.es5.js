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

  return jQuery.ajax({
    url: drupalSettings.alshayaRcs.commerceBackend.baseUrl + '/' + request.uri,
    method: request.method,
    headers,
    data: request.data,
    success: function (response) {
      return response;
    },
    error: function () {
      console.log('Could not fetch data!');
    }
  });
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
 exports.invokeApiAsync = function (request) {
  const headers = {};
  let result = null;

  if (typeof request.headers !== 'undefined') {
    request.headers.forEach(function (header) {
      headers[header[0]] = header[1];
    });
  }

  jQuery.ajax({
    url: drupalSettings.alshayaRcs.commerceBackend.baseUrl + '/' + request.uri,
    method: request.method,
    headers,
    async: false,
    data: request.data,
    success: function (response) {
      result = response;
    },
    error: function () {
      console.log('Could not fetch data!');
    }
  });

  return result;
};
