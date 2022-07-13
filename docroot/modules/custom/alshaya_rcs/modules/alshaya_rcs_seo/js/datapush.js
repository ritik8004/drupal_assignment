/**
 * @file
 * Push initial data to data layer.
 */

(function ($, Drupal, drupalSettings, RcsEventManager) {
  'use strict';

  Drupal.behaviors.alshayaRcsSeo = {
    attach: function () {
      $('body').once('alshayaRcsSeo').each(function () {
        var dataLayerContent = drupalSettings.dataLayerContent;
        if (globalThis.rcsPhGetPageType() === null) {
          var event = new CustomEvent('dataLayerContentAlter', {
            detail: {
              data: () => dataLayerContent
            }
          });
          document.dispatchEvent(event);
          window.dataLayer.push(dataLayerContent);
        }
        else {
          // Load initial GTM data after RCS page entity is loaded.
          RcsEventManager.addListener('alshayaPageEntityLoaded', function(e) {
            var event = new CustomEvent('dataLayerContentAlter', {
              detail: {
                data: () => dataLayerContent,
                page_entity : e.detail.entity,
                type : e.detail.pageType,
              }
            });
            document.dispatchEvent(event);
            window.dataLayer.push(dataLayerContent);
          });
        }
      });
    }
  }
})(jQuery, Drupal, drupalSettings, RcsEventManager);
