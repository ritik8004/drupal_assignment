/**
 * @file push initial data to data layer.
 */

(function (drupalSettings) {
  window.dataLayer = window.dataLayer || [];
  var dataLayerAttachment = drupalSettings.dataLayerAttachment;
  if (rcsPhGetPageType() === null) {
    var alterInitialDataLayerData = new CustomEvent('alterInitialDataLayerData', {detail: { data: () => dataLayerAttachment }});
    document.dispatchEvent(alterInitialDataLayerData);
    window.dataLayer.push(dataLayerAttachment);
  }
  else {
    RcsEventManager.addListener('alshayaPageEntityLoaded', function(e) {
      var alterInitialDataLayerData = new CustomEvent('alterInitialDataLayerData', {detail: { data: () => dataLayerAttachment, page_entity : e.detail.entity, type :  e.detail.pageType}});
      document.dispatchEvent(alterInitialDataLayerData);
      window.dataLayer.push(dataLayerAttachment);
    });
  }
})(drupalSettings);
