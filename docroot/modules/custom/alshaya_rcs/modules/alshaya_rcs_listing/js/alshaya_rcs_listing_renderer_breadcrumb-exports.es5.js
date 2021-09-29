const rcsPhBreadcrumbRenderer = require('../../alshaya_rcs_magento_placeholders/js/alshaya_rcs_magento_placeholders_breadcrumb-exports.es5');

/**
 * Breadcrumb renderer for listing pages.
 *
 * @param settings
 *    Drupal settings.
 * @param entity
 *    The entity.
 * @param innerHtml
 *    The html.
 * @returns
 *    Return the placeholder replaced breadcrumb markup.
 */
exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  const breadcrumbs = this.normalize(entity);
  return rcsPhBreadcrumbRenderer.render(settings, breadcrumbs, innerHtml);
};

/**
 * Normalize the original data.
 *
 * @param data
 *    The original data.
 */
exports.normalize = function normalize(
  data,
) {
  let normalized = [];

  // Check for empty data.
  if (!data || Object.keys(data).length < 1) {
    return [];
  }

  if (!Array.isArray(data.breadcrumbs) || data.breadcrumbs.length < 1) {
    // For root level categories, push name with URL as it will be required for
    // enrichment.
    normalized.push({
      url: data.url_path,
      text: data.name,
    });

    return normalized;
  }

  // Prepare the breadcrumb array.
  Object.keys(data.breadcrumbs).forEach(function (i) {
    normalized.push({
      url: data.breadcrumbs[i].category_url_path,
      text: data.breadcrumbs[i].category_name,
    });
  });

  // Push the last crumb with url as enrichment is based on url_path.
  // By default the last element click is restricted by CSS.
  normalized.push({
    url: data.url_path,
    text: data.name,
  });

  return normalized;
};
