import Drupal from '../../../../../../core/misc/drupal.es6';

export const drupalSettings = {
  cart: {
    url: 'v1',
    store: 'en_gb',
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
