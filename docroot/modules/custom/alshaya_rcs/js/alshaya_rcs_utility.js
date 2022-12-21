/**
 * Global variable which will contain acq_product related data/methods among
 * other things.
 */
window.commerceBackend = window.commerceBackend || {};

// Link between RCS errors and Datadog.
(function main(Drupal, RcsEventManager) {
  RcsEventManager.addListener('error', (e) => {
    Drupal.alshayaLogger(e.level, e.message, e.context);
  });

  /**
   * Utility function to get the normalized breadcrumb items.
   *
   * @param {array} normalized
   *   An array containing the normalized breadcrumb items.
   * @param {object} deepestCategory
   *   The deepest category object.
   * @param {object} keys
   *   An object containing the breadcrumb keys.
   *
   * @returns {array}
   *   The updated normalized breadcrumb array.
   */
  window.commerceBackend.getNormalizedBreadcrumbs = function (normalized, deepestCategory, keys) {
    Object.keys(deepestCategory.breadcrumbs).forEach(function (i) {
      normalized.push({
        url: deepestCategory.breadcrumbs[i].category_url_path,
        text: deepestCategory.breadcrumbs[i][keys.breadcrumbTermNameKey],
        data_url: deepestCategory.breadcrumbs[i].category_url_path,
        id: deepestCategory.breadcrumbs[i].category_id,
      });
    });

    return normalized;
  }
})(Drupal, RcsEventManager);
