/**
 * Rcs Event listener for swatch.
 */
(function main(RcsEventManager) {
  RcsEventManager.addListener('alshayaRcsAlterSwatch', function alshayaHmRcsAlterSwatch (e) {
    // Override color label.
    // For HM specifically, color label is taken from attribute of type string.
    // For other brands it needs to be read from customAttributeMetadata.
    e.detail.colorOptionsList.display_label = e.detail.variant.product.color_label;
  });
})(RcsEventManager);

