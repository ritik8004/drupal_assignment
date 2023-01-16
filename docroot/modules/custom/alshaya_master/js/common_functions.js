/**
 * @file
 * Common functions.
 */

(function ($, Drupal, drupalSettings) {

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

  Drupal.hasValue = function (value) {
    if (typeof value === 'undefined') {
      return false;
    }

    if (value === null) {
      return false;
    }

    if (Object.prototype.hasOwnProperty.call(value, 'length') && value.length === 0) {
      return false;
    }

    if (value.constructor === Object && Object.keys(value).length === 0) {
      return false;
    }

    return Boolean(value);
  };

  $.fn.reloadPage = function () {
    window.location.reload();
  };

  $.fn.hideLoader = function () {
    $('.ajax-progress, .ajax-progress-throbber').remove();
  };

  /**
   * Wrapper for logger for vanilla JS files.
   * @see logger.js.
   *
   * @param level
   *   The level which is also the method we call on the object.
   * @param message
   *   The message to log.
   * @param context
   *   The context.
   */
  Drupal.alshayaLogger = function (level, message, context) {
    if (typeof Drupal.logViaDataDog !== 'undefined') {
      Drupal.logViaDataDog(level, message, context);
    }
    else {
      console.debug(level, Drupal.formatString(message, context));
    }
  };

  /**
   * Prepares a string for use as a CSS identifier (element, class, or ID name).
   *
   * This is the JS implementation of \Drupal\Component\Utility\Html::getClass().
   * Link below shows the syntax for valid CSS identifiers (including element
   * names, classes, and IDs in selectors).
   *
   * @param {string} identifier
   *   The string to clean.
   *
   * @returns {string}
   *   The cleaned string which can be used as css class/id.
   *
   * @see http://www.w3.org/TR/CSS21/syndata.html#characters
   */
  Drupal.cleanCssIdentifier = function (identifier) {
    let cleanedIdentifier = identifier;

    // In order to keep '__' to stay '__' we first replace it with a different
    // placeholder after checking that it is not defined as a filter.
    cleanedIdentifier = cleanedIdentifier
                          .replaceAll('__', '##')
                          .replaceAll(' ', '-')
                          .replaceAll('_', '-')
                          .replaceAll('/', '-')
                          .replaceAll('[', '-')
                          .replaceAll(']', '')
                          .replaceAll('##', '__');

    // Valid characters in a CSS identifier are:
    // - the hyphen (U+002D)
    // - a-z (U+0030 - U+0039)
    // - A-Z (U+0041 - U+005A)
    // - the underscore (U+005F)
    // - 0-9 (U+0061 - U+007A)
    // - ISO 10646 characters U+00A1 and higher
    // We strip out any character not in the above list.
    cleanedIdentifier = cleanedIdentifier.replaceAll(/[^\u{002D}\u{0030}-\u{0039}\u{0041}-\u{005A}\u{005F}\u{0061}-\u{007A}\u{00A1}-\u{FFFF}]/gu, '');

    // Identifiers cannot start with a digit, two hyphens, or a hyphen followed by a digit.
    cleanedIdentifier = cleanedIdentifier.replace(/^[0-9]/, '_').replace(/^(-[0-9])|^(--)/, '__');

    return cleanedIdentifier.toLowerCase();
  }

  /**
   * Helper function to check if user is authenticated.
   *
   * @return {boolean}
   *   Return true if user is authenticated else false.
   */
  Drupal.isUserAuthenticated = function () {
    if (Drupal.hasValue(drupalSettings.userDetails)
      && Drupal.hasValue(drupalSettings.userDetails.customerId)) {
      return true;
    }

    return false;
  };

})(jQuery, Drupal, drupalSettings);
