/**
 * @file
 * Common functions.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.removeURLParameter = function (url, parameter) {
    var urlparts = url.split('?');
    if (urlparts.length >= 2) {
      var prefix = encodeURIComponent(parameter) + '=';
      var pars = urlparts[1].split(/[&;]/g);
      for (var i = pars.length; i-- > 0;) {
        if (pars[i].lastIndexOf(prefix, 0) !== -1) {
          pars.splice(i, 1);
        }
      }
      return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
    }

    return url;
  };

  /**
   * Helper function to fetch value for a query string.
   *
   * @param variable
   *
   * @returns {string}
   */
  Drupal.getQueryVariable = function (variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
      var pair = vars[i].split('=');
      if (decodeURIComponent(pair[0]) === variable) {
        return decodeURIComponent(pair[1]);
      }
    }
    return '';
  };

  $.fn.reloadPage = function () {
    window.location.reload();
  };

  $.fn.hideLoader = function () {
    $('.ajax-progress, .ajax-progress-throbber').remove();
  };

})(jQuery, Drupal);
