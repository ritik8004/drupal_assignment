window.commerceBackend = window.commerceBackend || {};

(function rcsSizeGuideUtility(Drupal) {
  /**
   * Get rcs size guide settings for pdp.
   *
   * @returns {Object}
   *   Processed size guide object.
   */
  window.commerceBackend.getSizeGuideSettings = function getRcsSizeGuideSettings() {
    const { alshayaRcs } = drupalSettings;
    if (Drupal.hasValue(alshayaRcs) && Drupal.hasValue(alshayaRcs.sizeGuide)) {
      return alshayaRcs.sizeGuide;
    }
    return null;
  }
})(Drupal);
