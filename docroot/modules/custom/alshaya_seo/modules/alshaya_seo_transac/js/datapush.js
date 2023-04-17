/**
 * @file push initial data to data layer.
 */

 (function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.dataPush = {
    attach: function () {
      $('body').once('datapush').each(function () {
        try {
          var dataLayerContent = drupalSettings.dataLayerContent;
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
        catch (e) {
          Drupal.alshayaLogger('error', 'Error while pushing to datalayer: @error. Data: @data', {
            '@error': e,
            '@data': typeof dataLayerContent != 'undefined' ? dataLayerContent : '',
          });
        }
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
