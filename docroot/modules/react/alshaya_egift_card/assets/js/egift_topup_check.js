/**
 * @file
 * Removes topupQuote of egift conditionally.
 */

(function (Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.alshayaEgiftTopup = {
    attach: function (context, settings) {
      // Check if user is landing on a page other than checkout and topup quote
      // is available in the local storage, then remove it from storage as
      // topup flow should be covered in a single flow.
      if (drupalSettings.path.currentPath !== 'checkout') {
        const topUpQuote = Drupal.getItemFromLocalStorage('topupQuote');
        if (topUpQuote !== null) {
          Drupal.removeItemFromLocalStorage('topupQuote');
        }
      }
    },
  };
})(Drupal, drupalSettings);
