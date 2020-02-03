/**
 * @file
 * JS to add ga event on algolia search page.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.setAlgoliaSearchGA = {
    attach: function (context) {
      var hashValue = Drupal.getHashValue();
      $('input.react-autosuggest__input').once('AlgoliaSearchGA').on('change', function() {
        var newHashValue = Drupal.sendPageviewEvent(hashValue);
        hashValue = newHashValue;
      }); 
    }
  };

  Drupal.sendPageviewEvent = function (hashValue) {
    var newHashValue = Drupal.getHashValue();
    if (hashValue !== newHashValue) {
      dataLayer.push({
        event: 'VirtualPageview',
        virtualPageURL: location.pathname + '?keywords=' + newHashValue,
      });
    }
    return newHashValue;
  }

}(jQuery, Drupal, dataLayer));
