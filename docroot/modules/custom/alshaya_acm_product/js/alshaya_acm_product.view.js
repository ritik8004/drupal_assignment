(function (Drupal) {
  /**
   * Returns product key for product view mode.
   *
   * @param {string} viewMode
   *   Product view mode.
   * @returns {string}
   *   Returns product view mode key.
   */
  Drupal.getProductKeyForProductViewMode = function (viewMode) {
    var productKey = (viewMode === 'matchback' || viewMode === 'matchback_mobile')
      ? viewMode
      : 'productInfo';

    return productKey;
  };
})(Drupal);
