/**
 * @file
 * Olapic script to add keys to show widgets.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.alshayaOlapic = {
    attach: function (context) {
        onOlapicLoad();

    }
  };
})(jQuery, Drupal, drupalSettings);

function doRender(options){
  console.log(options);
  window.olapic.prepareWidget(options, {
      'renderNow' : true,
      'force' : true // optional - overwrites the widget index on load
  });
};
function onOlapicLoad() {
  if (typeof drupalSettings.olapic_keys.data_apikey != "undefined") {
    OlapicSDK.conf.set('apikey', drupalSettings.olapic_keys.data_apikey);
    language = drupalSettings.olapic_keys.lang;
    if(drupalSettings.olapic_keys.development_mode == 1){
      OlapicSDK.conf.set('mode', 'development');
    }
  }
  window.olapic = window.olapic || new OlapicSDK.Olapic( function (o) {
      window.olapic = o;
      $("div[id^='olapic-']").each(function () {
        doRender({
       'id': $(this).attr("data-instance"), //required
       'wrapper': $(this).attr("id"),
       'lang':language, //optional - only used when using translation engine
        });
    });

  });
};
