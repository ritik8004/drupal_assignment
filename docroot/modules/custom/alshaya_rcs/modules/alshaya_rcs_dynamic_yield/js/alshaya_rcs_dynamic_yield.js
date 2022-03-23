/**
 * @file
 * Push initial data to dynamic yield.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  window.DY = window.DY || [];
  var initialDyData = window.DY;
  if (rcsPhGetPageType() === null) {
    var alterInitialDYData = new CustomEvent('alterInitialDynamicYield', { detail: { data: initialDyData } });
    document.dispatchEvent(alterInitialDYData);
    window.DY = initialDyData;
  }
  else {
    // Load initial dynamic yeild data after RCS page entity is loaded.
    RcsEventManager.addListener('alshayaPageEntityLoaded', function (e) {
      var alterInitialDyData = new CustomEvent('alterInitialDynamicYield', {
        detail: {
          data: initialDyData,
          page_entity: e.detail.entity,
          type: e.detail.pageType,
        }
      });
      document.dispatchEvent(alterInitialDyData);
      window.DY = initialDyData;
    });
  }

  Drupal.behaviors.alshayaRcsDynamicYield = {
    attach: function () {
      if ($('body').hasClass('rcs-loaded')
        && $('body').data('dyloaded') === undefined
        && drupalSettings.dynamicYield.sourceId) {
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
    }
  };

})(jQuery, Drupal, drupalSettings);
