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

export default {
  drupalSettings,
  Drupal,
};
