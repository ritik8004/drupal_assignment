/**
 * @file
 * Inject the DY static and dynamic script.
 */

(function ($, drupalSettings) {

  $.fn.DYscript = function() {
    // Add DY dynamic and static script in header.
    var script = document.createElement('script');
    script.src = '//cdn-eu.dynamicyield.com/api/' + drupalSettings.dynamicYield.sourceId + '/api_dynamic.js';
    document.head.appendChild(script);
    // DY script for static script.
    script = document.createElement('script');
    script.src = '//cdn-eu.dynamicyield.com/api/' + drupalSettings.dynamicYield.sourceId + '/api_static.js';
    document.head.appendChild(script);
    // Add the dyloaded data to make sure that scripts are not getting
    // added multiple times.
    $('body').data('dyloaded', true);
  }

  // Define the fresh scriptInjection if it's not already defined.
  if (!($.fn.DYscriptInjection)) {
    $.fn.DYscriptInjection = function () {
      if ($('body').data('dyloaded') === undefined) {
        $.fn.DYscript();
      }
    }
  }

  Drupal.behaviors.dynamicYield = {
    attach: function() {
      // Inject the script on load.
      $.fn.DYscriptInjection();
    }
  }

})(jQuery, drupalSettings);
