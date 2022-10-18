/**
 * @file
 * This file contains most of the code for the configuration page.
 */

(function alshayaRcsSharethis($, drupalSettings) {
  'use strict';
  Drupal.behaviors.shareThis = {
    attach: function alshayaRcsShareThisBehavior(context) {
      // Check if we are displaying sharethis in the page.
      if ($('.sharethis-container').length === 0) {
        return;
      }
      // Check if the PDP rendering is complete.
      var node = $('.entity--type-node', context).not('[data-sku *= "#"]');
      var $context = $(context);
      if ($context && $context.hasClass('entity--type-node')){
        node = $context;
      }
      if (node.length === 0) {
        return;
      }
      var skuBaseForm = $('.sku-base-form', node);
      if (skuBaseForm.length === 0) {
        return;
      }
      // Start loading sharethis button js.
      Drupal.loadShareThis(false);
      // Execute the code to start sharethis. Wait for a second so that the
      // sharethis JS is loaded.
      var shareThisExecution = setInterval(function executeSharethisJs() {
        if (typeof stLight !== 'undefined') {
          stLight.options(drupalSettings.sharethis);
        }
        else {
          // Wait until sharethis js is loaded.
          return;
        }
        stButtons.locateElements();
        clearInterval(shareThisExecution);
      }, 1000);

      shareThisExecution();
    }
  };
})(jQuery, drupalSettings);
