import Drupal from '../../../../../../core/misc/drupal.es6';
// Bridge to make Drupal Underscore available while running tests.
global._ = require('underscore');

export const drupalSettings = {
  jest: 1,
  cart: {
    url: '/rest/kwt_en',
    siteInfo: {
      site_code: 'vs',
      country_code: 'kw',
    },
    addressFields: {
      "default": {
        "kw": [
          "area",
          "address_apartment_segment",
        ]
      }
    },
    exceptionMessages: {
      "This product is out of stock.": "OOS",
    },
  },
  path: {
    currentLanguage: 'en',
  },
  user: {
    uid: 0,
  },
  userDetails: {
    customerId: 0,
  },
};

// Define a mock implementation here otherwise will get error on running the
// test about the function not being found.
// This is most likely because this function is included by Drupal and is not a
// part of react.
window.commerceBackend.getProductStatus = function () {}

export default {
  drupalSettings,
  Drupal,
};
