/**
 * @file push initial data to data layer.
 */

(function ($, Drupal, drupalSettings) {
  window.dataLayer = window.dataLayer || [];
  var dataLayerAttachment = drupalSettings.dataLayerAttachment;
  if (rcsPhGetPageType() === null) {
    var alterInitialDataLayerData = new CustomEvent('alterInitialDataLayerData', {detail: { data: () => dataLayerAttachment }});
    document.dispatchEvent(alterInitialDataLayerData);
    window.dataLayer.push(dataLayerAttachment);
  }
  else {
    RcsEventManager.addListener('alshayaPageEntityLoaded', (e) => {
      var alterInitialDataLayerData = new CustomEvent('alterInitialDataLayerData', {detail: { data: () => dataLayerAttachment, page_entity : e.detail.entity, type :  rcsPhGetPageType()}});
      document.dispatchEvent(alterInitialDataLayerData);
      window.dataLayer.push(dataLayerAttachment);
    });
  }

})(jQuery, Drupal, drupalSettings);
