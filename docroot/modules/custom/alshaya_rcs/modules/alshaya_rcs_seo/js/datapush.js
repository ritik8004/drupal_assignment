/**
 * @file
 * Push initial data to data layer.
 */

(function (drupalSettings) {
  window.dataLayer = window.dataLayer || [];
  var dataLayerAttachment = drupalSettings.dataLayerAttachment;
  if (globalThis.rcsPhGetPageType() === null) {
    var alterInitialDataLayerData = new CustomEvent('alterInitialDataLayerData', {detail: { data: () => dataLayerAttachment }});
    document.dispatchEvent(alterInitialDataLayerData);
    window.dataLayer.push(dataLayerAttachment);
  }
  else {
    // Load initial GTM data after RCS page entity is loaded.
    RcsEventManager.addListener('alshayaPageEntityLoaded', function(e) {
      var alterInitialDataLayerData = new CustomEvent('alterInitialDataLayerData', {
        detail: {
          data: () => dataLayerAttachment,
          page_entity : e.detail.entity,
          type :  e.detail.pageType,
        }
      });
      document.dispatchEvent(alterInitialDataLayerData);
      window.dataLayer.push(dataLayerAttachment);
    });
  }
})(drupalSettings);
