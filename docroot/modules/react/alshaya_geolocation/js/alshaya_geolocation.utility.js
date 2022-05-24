window.alshayaGeolocation = window.alshayaGeolocation || {};

(function alshayaGeolocationUtility(drupalSettings) {
  /**
   * Returns the store labels.
   *
   * @returns {object}
   *   Store labels values.
   */
  window.alshayaGeolocation.getStoreLabelsPdp = function getStoreLabelsPdp() {
    return drupalSettings.storeLabels;
  }
})(drupalSettings);
