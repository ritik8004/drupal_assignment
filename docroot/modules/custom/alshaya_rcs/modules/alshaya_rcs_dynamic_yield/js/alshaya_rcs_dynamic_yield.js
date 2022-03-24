/**
 * @file
 * Push initial data to dynamic yield.
 */

(function ($, Drupal) {

  window.DY = window.DY || [];
  var initialDyData = window.DY;
  if (rcsPhGetPageType() === null) {
    var alterInitialDYData = new CustomEvent('alterInitialDynamicYield', { detail: { data: initialDyData } });
    document.dispatchEvent(alterInitialDYData);
    window.DY = initialDyData;
  }
  else {
    // Load initial dynamic yeild data after RCS page entity is loaded.
    RcsEventManager.addListener('alshayaPageEntityLoaded', function (e) {
      var alterInitialDyData = new CustomEvent('alterInitialDynamicYield', {
        detail: {
          data: initialDyData,
          page_entity: e.detail.entity,
          type: e.detail.pageType,
        }
      });
      document.dispatchEvent(alterInitialDyData);
      window.DY = initialDyData;
    });
  }

  // Override the dynamic yield script injection.
  $.fn.DYscriptInjection = function () {
    if ($('body').hasClass('rcs-loaded')
      && $('body').data('dyloaded') === undefined) {
      $.fn.DYscript();
    }
  }

})(jQuery, Drupal);
