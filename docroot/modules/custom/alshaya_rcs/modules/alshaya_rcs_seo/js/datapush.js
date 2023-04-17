/**
 * @file
 * Push initial data to data layer.
 */

(function ($, Drupal, drupalSettings, RcsEventManager) {
  'use strict';

  Drupal.behaviors.alshayaRcsSeo = {
    attach: function () {
      $('body').once('alshayaRcsSeo').each(function () {
        try {
          var dataLayerContent = drupalSettings.dataLayerContent;
          if (globalThis.rcsPhGetPageType() === null) {
            if (Drupal.hasValue(dataLayerContent.event) && dataLayerContent.event == 'purchaseSuccess') {
              Drupal.alshayaLogger('notice', 'Purchase success event before content alter: @datalayer.', {
                '@datalayer': dataLayerContent,
              });
            }
            var event = new CustomEvent('dataLayerContentAlter', {
              detail: {
                data: () => dataLayerContent
              }
            });
            document.dispatchEvent(event);
            if (Drupal.hasValue(dataLayerContent.event) && dataLayerContent.event == 'purchaseSuccess') {
              Drupal.alshayaLogger('notice', 'Purchase success event after content alter: @datalayer.', {
                '@datalayer': dataLayerContent,
              });
            }
            window.dataLayer.push(dataLayerContent);
            if (Drupal.hasValue(dataLayerContent.event) && dataLayerContent.event == 'purchaseSuccess') {
              Drupal.alshayaLogger('notice', 'Purchase success event after data push: @datalayer.', {
                '@datalayer': dataLayerContent,
              });
            }
          }
          else {
            // Load initial GTM data after RCS page entity is loaded.
            RcsEventManager.addListener('alshayaPageEntityLoaded', function(e) {
              if (Drupal.hasValue(dataLayerContent.event) && dataLayerContent.event == 'purchaseSuccess') {
                Drupal.alshayaLogger('notice', 'Purchase success event before content alter: @datalayer.', {
                  '@datalayer': dataLayerContent,
                });
              }
              var event = new CustomEvent('dataLayerContentAlter', {
                detail: {
                  data: () => dataLayerContent,
                  page_entity : e.detail.entity,
                  type : e.detail.pageType,
                }
              });
              document.dispatchEvent(event);
              if (Drupal.hasValue(dataLayerContent.event) && dataLayerContent.event == 'purchaseSuccess') {
                Drupal.alshayaLogger('notice', 'Purchase success event after content alter: @datalayer.', {
                  '@datalayer': dataLayerContent,
                });
              }
              window.dataLayer.push(dataLayerContent);
              if (Drupal.hasValue(dataLayerContent.event) && dataLayerContent.event == 'purchaseSuccess') {
                Drupal.alshayaLogger('notice', 'Purchase success event after data push: @datalayer.', {
                  '@datalayer': dataLayerContent,
                });
              }
            });
          }
        }
        catch (e) {
          Drupal.alshayaLogger('error', 'Error while pushing to datalayer: @error. Data: @data', {
            '@error': e,
            '@data': typeof dataLayerContent != 'undefined' ? dataLayerContent : '',
          });
        }
      });
    }
  }
})(jQuery, Drupal, drupalSettings, RcsEventManager);
