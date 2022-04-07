import Drupal from '../../../../../core/misc/drupal.es6';
import jQuery from '../../../../../core/assets/vendor/jquery/jquery';

global['jQuery'] = global['$'] = jQuery;

// Mock jquery.once.
$.fn.once = jest.fn().mockImplementation(() => $());

export const drupalSettings = {
  jest: 1
};

export default {
  drupalSettings,
  Drupal,
};
