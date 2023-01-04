window.commerceBackend = window.commerceBackend || {};

(function pdpReactSizeGuideUtility(Drupal) {
  /**
   * Get size guide settings for pdp v2.
   *
   * @returns {Object}
   *   Processed size guide object.
   */
  window.commerceBackend.getSizeGuideSettings = () => {
    // Get size guide from drupal settings for v2 architecture.
    const { isSizeGuideEnabled, sizeGuide } = drupalSettings;
    if (isSizeGuideEnabled && Drupal.hasValue(sizeGuide)) {
      return sizeGuide;
    }
    return null;
  };
})(Drupal);
