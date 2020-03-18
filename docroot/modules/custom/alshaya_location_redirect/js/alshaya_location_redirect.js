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

  // todo check this mapping
  // Mapping of sites to markets.
  var mapping = {
    'aeo' : {
      'kw': '',
      'sa' : '',
      'ae' : '',
      'eg' : '',
    },
    'bb' : {
      'me': ''
    },
    'bbw' : {
      'kw': '',
      'sa' : '',
      'ae' : ''
    },
    'd' : {},
    'fl' : {},
    'hm': {
      'kw' : 'https://kw.hm.com',
      'ae' : 'https://ae.hm.com',
      'sa' : 'https://sa.hm.com',
      'eg' : 'https://eg.hm.com'
    },
    'jus': {
      'kw': '',
      'sa' : '',
      'ae' : ''
    },
    'kz': {
      'kw': '',
      'sa' : '',
      'ae' : ''
    },
    'mc' : {
      'kw' : '',
      'sa' : '',
      'ae' : ''
    },
    'pb' : {
      'kw': '',
      'sa' : '',
      'ae' : ''
    },
    've' : {
      'kw': ''
    },
    'vs' : {
      'kw': {},
      'ae' : {},
      'sa' : {}
    },
    'we' : {}
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
    // todo call cloudflare worker and get country code for user
    user_location = 'kw';
    // todo check response and bail out here if call failed to avoid redirect loop.

    // if stored item does not exist, set it.
    localStorage.setItem(storage_key, user_location);

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
