/**
 * Global object containin the rcs_magento APIs.
 */
window.rcsBackend = window.rcsBackend || {};

(function ($) {
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
  window.rcsBackend.fetchData = function (url, method, options, callback) {
    const headers = {};

    if (typeof options !== 'undefined') {
      if (typeof options.headers !== 'undefined') {
        options.headers.forEach(function (header) {
          headers[header[0]] = header[1];
        });
      }
    }

    $.ajax({
      url: url,
      method,
      headers,
      data: options.data,
      success: function (response) {
        callback(response);
      },
      error: function () {
        console.log('Could not fetch data!');
      }
    });
  }
})(jQuery);
