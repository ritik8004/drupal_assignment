/**
 * @file
 * Push initial data to dynamic yield.
 */

(function (Drupal, RcsEventManager) {

  window.DY = window.DY || {};
  var initialDyData = window.DY;
  if (rcsPhGetPageType() === null) {
    var alterInitialDYData = new CustomEvent('alterInitialDynamicYield', { detail: { data: initialDyData } });
    document.dispatchEvent(alterInitialDYData);
    window.DY = initialDyData;
    // Attach the DY script.
    Drupal.attachDyScriptToHead();
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
      // Attach the DY script.
      Drupal.attachDyScriptToHead();
    });
  }

})(Drupal, RcsEventManager);
