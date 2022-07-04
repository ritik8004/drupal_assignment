/**
 * @file
 * Inject the DY static and dynamic script.
 */

(function ($, Drupal, drupalSettings) {
  /**
   * Checks if the provided input has some value assigned to it.
   *
   * @param {mixed} value
   *   The value to check.
   * @returns {Boolean}
   *   True if value is there else false.
   */
  function hasValue(value) {
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

  /**
   * To attach the dynamic yield script manually.
   */
  Drupal.attachDyScriptToHead = function() {
    if ($('body').once('dyloaded').length) {
      // Add DY dynamic and static script in header.
      var script = document.createElement('script');
      if (hasValue(drupalSettings.dynamicYield)
        && hasValue(drupalSettings.dynamicYield.sourceId)) {
        script.src = '//cdn-eu.dynamicyield.com/api/' + drupalSettings.dynamicYield.sourceId + '/api_dynamic.js';
        document.head.appendChild(script);
        // DY script for static script.
        script = document.createElement('script');
        script.src = '//cdn-eu.dynamicyield.com/api/' + drupalSettings.dynamicYield.sourceId + '/api_static.js';
        document.head.appendChild(script);
      }
    }
  }

})(jQuery, Drupal, drupalSettings);
