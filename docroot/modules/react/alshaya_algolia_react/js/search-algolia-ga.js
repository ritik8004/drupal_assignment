/**
 * @file
 * JS to add ga event on algolia search page.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.setAlgoliaSearchGA = {
    attach: function (context) {
      var hash = window.location.hash;
      if (hash) {
        Drupal.sendPageviewEvent(hash);
      }
      $("input.react-autosuggest__input").on('change', function() { 
        var newHash = window.location.hash;
        if (hash !== newHash) {
          Drupal.sendPageviewEvent(newHash);
          hash = newHash;
        }
      }); 
    }
  };

  Drupal.sendPageviewEvent = function (newHash) {
    dataLayer.push({
      event: 'VirtualPageview',
      virtualPageURL: location.pathname + '?keywords=' + newHash.split('=')[1],
    });
  }

}(jQuery, Drupal, dataLayer));
