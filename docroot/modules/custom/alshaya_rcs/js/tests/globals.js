import Drupal from '../../../../../core/misc/drupal.es6';
import jQuery from '../../../../../core/assets/vendor/jquery/jquery';

global['jQuery'] = global['$'] = jQuery;

export const drupalSettings = {
  jest: 1,
  path: {
    pathPrefix: 'en/'
  },
  rcsPhSettings: {
    categoryPathPrefix: 'buy-'
  }
};

export default {
  drupalSettings,
  Drupal,
};
