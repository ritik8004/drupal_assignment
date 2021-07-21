import Drupal from '../../../../../../core/misc/drupal.es6';

export const drupalSettings = {
  cart: {
    url: '/rest/kw_en',
    site_country_code: {
      site_code: 'vs',
      country_code: 'kw',
    },
    address_fields: {
      "default": {
        "kw": [
          "area",
          "address_apartment_segment",
        ]
      }
    },
    exception_messages: {
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
