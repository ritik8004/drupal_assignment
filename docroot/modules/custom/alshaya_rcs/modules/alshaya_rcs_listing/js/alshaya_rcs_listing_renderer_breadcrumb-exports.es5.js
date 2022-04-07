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
    // For root level categories, push name without a url.
    normalized.push({
      url: null,
      text: data.name,
      data_url: data.url_path,
      id: data.id,
    });

    return normalized;
  }

  // Prepare the breadcrumb array.
  Object.keys(data.breadcrumbs).forEach(function (i) {
    normalized.push({
      url: data.breadcrumbs[i].category_url_path,
      text: data.breadcrumbs[i].category_name,
      data_url: data.breadcrumbs[i].category_url_path,
      id: data.breadcrumbs[i].category_id,
    });
  });

  // Push the last crumb without a url.
  normalized.push({
    url: null,
    text: data.name,
    data_url: data.url_path,
    id: data.id,
  });

  return normalized;
};
