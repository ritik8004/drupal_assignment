/**
 * @file
 * Detect user location, and potentially redirect to an appropriate country.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  // Whether to perform a redirect.
  var redirect = false;

  // The local storage key.
  var storage_key = 'alshaya_location';

  // Check if user location already exists in an existing local storage item.
  var user_location = localStorage.getItem(storage_key);

  // Mapping of brands to countries..
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
      'ae' : 'https://www.victoriassecret.ae',
    },
  };

  if (drupalSettings.alshaya_location_redirect.brand) {
    var brand = drupalSettings.alshaya_location_redirect.brand;
  }
  else {
    return;
  }

  if (drupalSettings.alshaya_location_redirect.country) {
    var current_country = drupalSettings.alshaya_location_redirect.country;
  }
  else {
    return;
  }

  // Determine if we already have a value for the user location.
  if (user_location) {
    // If the user location already exists, decide whether or not to redirect
    // the user if that location doesn't match the current country.
    if (user_location !== current_country) {
      redirect = true;
    }
  }
  else {
    var worker_url = drupalSettings.alshaya_location_redirect.worker_url;
    user_location = 'xx';

    $.ajax({
      type: 'GET',
      url: worker_url,
      dataType: 'json',
      success: function(data) {
        $.each( data, function(key, val) {
          user_location = val;
        });
      },
      data: {},
      async: false
    });

    // Add the user location to local storage.
    localStorage.setItem(storage_key, user_location);

    if (user_location === 'xx') {
      // Did not receive a valid country to redirect to.
      return;
    }

    if (user_location !== current_country) {
      redirect = true;
    }
  }

  if (redirect && mapping.hasOwnProperty(brand) && mapping[brand].hasOwnProperty(user_location)) {
    var alternate_url = mapping[brand][user_location];

    // Perform the redirect.
    window.location = alternate_url;
  }

})(jQuery, Drupal, window.drupalSettings);
