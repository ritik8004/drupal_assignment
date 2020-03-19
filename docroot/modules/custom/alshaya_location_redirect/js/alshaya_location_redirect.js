/**
 * @file
 * Detect user location, and potentially redirect to an appropriate market.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  // Whether to perform a redirect.
  var redirect = false;

  // The local storage key.
  var storage_key = 'alshaya_location';

  // Check if user location already exists in an existing local storage item.
  var user_location = localStorage.getItem(storage_key);

  // Mapping of sites to markets.
  var mapping = {
    'bbw' : {
      'kw': 'https://www.bathandbodyworks.com.kw',
      'sa' : 'https://www.bathandbodyworks.com.sa',
      'ae' : 'https://www.bathandbodyworks.ae'
    },
    'fl' : {
      'kw' : 'https://www.footlocker.com.kw',
      'ae' : 'https://www.footlocker.ae',
      'sa' : 'https://www.footlocker.com.sa',
    },
    'hm': {
      'kw' : 'https://kw.hm.com',
      'ae' : 'https://ae.hm.com',
      'sa' : 'https://sa.hm.com',
      'eg' : 'https://eg.hm.com'
    },
    'mc' : {
      'kw' : 'https://www.mothercare.com.kw',
      'sa' : 'https://www.mothercare.com.sa',
      'ae' : 'https://www.mothercare.ae'
    },
    'pb' : {
      'kw' : 'https://www.potterybarn.com.kw',
      'sa' : 'https://www.potterybarn.com.sa',
      'ae' : 'https://www.potterybarn.ae'
    },
    'vs' : {
      'ae' : 'www.victoriassecret.ae',
    },
  };

  if (drupalSettings.alshaya_location_redirect.site) {
    var site = drupalSettings.alshaya_location_redirect.site;
  }
  else {
    return;
  }

  if (drupalSettings.alshaya_location_redirect.market) {
    var current_market = drupalSettings.alshaya_location_redirect.market;
  }
  else {
    return;
  }

  // Determine if we already have a value for the user location.
  if (user_location) {
    // If the user location already exists, decide whether or not to redirect
    // the user if that location doesn't match the current market.
    if (user_location !== current_market) {
      redirect = true;
    }
  }
  else {
    var worker_url = drupalSettings.alshaya_location_redirect.worker_url;
    user_location = 'xx';

    // Call cloudflare worker to get user country.
    $.getJSON(worker_url, function (data){
      $.each( data, function( key, val ) {
        user_location = val;
      });
    });

    // if stored item does not exist, set it.
    localStorage.setItem(storage_key, user_location);

    if (user_location === 'xx') {
      // Did not receive a valid market to redirect to.
      return;
    }

    if (user_location !== current_market) {
      redirect = true;
    }
  }

  if (redirect && mapping.hasOwnProperty(site) && mapping[site].hasOwnProperty(user_location)) {
    var alternate_url = mapping[site][user_location];

    // Perform the redirect.
    window.location = alternate_url;
  }

})(jQuery, Drupal, window.drupalSettings);
