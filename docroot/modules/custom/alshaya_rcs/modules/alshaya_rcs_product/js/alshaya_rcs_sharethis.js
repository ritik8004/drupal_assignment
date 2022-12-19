/**
 * @file
 * This file contains most of the code for the configuration page.
 */

(function alshayaRcsSharethis($, drupalSettings) {
  'use strict';

  var shareThisLoaded = false;

  RcsEventManager.addListener('alshayaPageEntityLoaded', function alshayaRcsSharethisPageEntityLoaded (e) {
    if (!drupalSettings.sharethis || !drupalSettings.sharethis.contentRendered) {
      return;
    }
    rcsPhReplaceEntityPh(drupalSettings.sharethis.contentRendered, 'product', e.detail.entity, drupalSettings.path.currentLanguage)
      .forEach(function eachReplacement(r) {
        const fieldPh = r[0];
        const entityFieldValue = r[1];

        // Apply the replacement on all the elements containing the
        // placeholder.
        drupalSettings.sharethis.contentRendered = globalThis.rcsReplaceAll(drupalSettings.sharethis.contentRendered, fieldPh, entityFieldValue);
      });
  });

  Drupal.behaviors.shareThis = {
    attach: function alshayaRcsShareThisBehavior(context) {
      // Check if we are displaying sharethis in the page and do not re-execute.
      if (shareThisLoaded || $('.sharethis-container, .sharethis-wrapper', context).length === 0) {
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
      // Now we can load sharethis.
      shareThisLoaded = true;
      // Start loading sharethis button js.
      Drupal.loadShareThis(false);
      // Execute the code to start sharethis. Wait for a second so that the
      // sharethis JS is loaded.
      var shareThisExecution = setInterval(function executeSharethisJs() {
        if (typeof stLight === 'undefined' || typeof stButtons === 'undefined') {
          // Wait until sharethis js is loaded.
          return;
        }
        else {
          stLight.options(drupalSettings.sharethis);
          stButtons.locateElements();
        }
        clearInterval(shareThisExecution);
      }, 1000);
    }
  };
})(jQuery, drupalSettings);
