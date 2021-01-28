/**
 * @file
 * Attaches entity-type selection behaviors to the widget form.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.alshayaOlapic = {
    attach: function (context) {
      if (typeof drupalSettings.olapic_keys != "undefined") {
        $('script[data-olapic="olapic_specific_widget"]').attr( 'data-apikey',drupalSettings.olapic_keys.data_apikey);
        $('script[data-olapic="olapic_specific_widget"]').attr( 'data-instance',drupalSettings.olapic_keys.data_instance);
        $('script[data-olapic="olapic_specific_widget"]').attr( 'data-lang',drupalSettings.olapic_keys.data_lang);

      }
    }
  };
})(jQuery, Drupal, drupalSettings);
