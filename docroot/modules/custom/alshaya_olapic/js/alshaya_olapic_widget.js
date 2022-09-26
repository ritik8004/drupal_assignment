/**
 * @file
 * Olapic script to add keys to show widgets.
 */
(function ($, Drupal, drupalSettings, window) {

  var isOlapicInitialised = false;
  var olapicInitInterval;

  function doRender(options) {
    window.olapic.prepareWidget(options, {
      renderNow: true,
      force: true, // optional - overwrites the widget index on load
    });
  }

  function initOlapic() {
    if (window.OlapicSDK) {
      // clear initialised interval which was added during page load.
      clearInterval(olapicInitInterval);
      OlapicSDK.conf.set("apikey", drupalSettings.olapic_keys.data_apikey);
      if (drupalSettings.olapic_keys.development_mode == 1) {
        OlapicSDK.conf.set("mode", "development");
      }
      if (!window.olapic) {
        window.olapic = new OlapicSDK.Olapic();
      }
      isOlapicInitialised = true;
      initOlapicWidgets(document);
    }
  }

  function initOlapicWidgets(context) {
    $("div[id^='olapic-']", context)
      .once("init-olapic")
      .each(function () {
        var $this = $(this);
        doRender({
          id: $this.data("instance"), //required
          wrapper: $this.attr("id"),
          lang: drupalSettings.olapic_keys.lang, //optional - only used when using translation engine
        });
      });
  }

  Drupal.behaviors.alshayaOlapic = {
    attach: function (context) {
      if (isOlapicInitialised) {
        initOlapicWidgets(context);
      }
    },
  };

  // Added setInterval to allow time for Olapic libraries to execute.
  if (drupalSettings.olapic_keys && drupalSettings.olapic_keys.data_apikey) {
    olapicInitInterval = setInterval(initOlapic, 1000);
  }
})(jQuery, Drupal, drupalSettings, window);
