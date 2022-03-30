/**
 * @file
 * Push initial data to data layer.
 */

(function ($) {
  'use strict';

  Drupal.behaviors.alshayaRcsSeo = {
    attach: function () {
      $('body').once('alshayaRcsSeo').each(function () {
        var dataLayerAttachment = drupalSettings.dataLayerAttachment;
        if (globalThis.rcsPhGetPageType() === null) {
          var event = new CustomEvent('dataLayerDataAlter', {
            detail: {
              data: () => dataLayerAttachment }
          });
          document.dispatchEvent(event);
          window.dataLayer.push(dataLayerAttachment);
        }
        else {
          // Load initial GTM data after RCS page entity is loaded.
          RcsEventManager.addListener('alshayaPageEntityLoaded', function(e) {
            var event = new CustomEvent('dataLayerDataAlter', {
              detail: {
                data: () => dataLayerAttachment,
                page_entity : e.detail.entity,
                type : e.detail.pageType,
              }
            });
            document.dispatchEvent(event);
            window.dataLayer.push(dataLayerAttachment);
          });
        }
      });
    }
  }
})(jQuery);
