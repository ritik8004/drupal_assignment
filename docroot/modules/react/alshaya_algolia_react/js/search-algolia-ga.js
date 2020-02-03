/**
 * @file
 * JS to add ga event on algolia search page.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.setAlgoliaSearchGA = {
    attach: function (context) {
      var hashValue = Drupal.getHashValue('query');
      Drupal.sendPageviewEvent('');
      $('input.react-autosuggest__input').once('AlgoliaSearchGA').on('change', function() {
        var newHashValue = Drupal.sendPageviewEvent(hashValue);
        hashValue = newHashValue;
      }); 
    }
  };

  Drupal.sendPageviewEvent = function (hashValue) {
    var newHashValue = Drupal.getHashValue('query');
    if (newHashValue) {
      if (hashValue !== newHashValue) {
        dataLayer.push({
          event: 'VirtualPageview',
          virtualPageURL: location.pathname + '?keywords=' + newHashValue,
        });
      }
    }
    return newHashValue;
  }

}(jQuery, Drupal, dataLayer));
