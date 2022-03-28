/**
 * @file
 * Inject the DY static and dynamic script.
 */

(function ($, Drupal, drupalSettings) {

  /**
   * To attach the dynamic yield script manually.
   */
  Drupal.attachDyScriptToHead = function() {
    if ($('body').once('dyloaded').length) {
      // Add DY dynamic and static script in header.
      var script = document.createElement('script');
      script.src = '//cdn-eu.dynamicyield.com/api/' + drupalSettings.dynamicYield.sourceId + '/api_dynamic.js';
      document.head.appendChild(script);
      // DY script for static script.
      script = document.createElement('script');
      script.src = '//cdn-eu.dynamicyield.com/api/' + drupalSettings.dynamicYield.sourceId + '/api_static.js';
      document.head.appendChild(script);
    }
  }

})(jQuery, Drupal, drupalSettings);
