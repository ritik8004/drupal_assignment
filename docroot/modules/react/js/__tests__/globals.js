import Drupal from '../../../../core/misc/drupal.es6';
import jQuery from '../../../../core/assets/vendor/jquery/jquery';

global['jQuery'] = global['$'] = jQuery;

export default {
  Drupal,
};
