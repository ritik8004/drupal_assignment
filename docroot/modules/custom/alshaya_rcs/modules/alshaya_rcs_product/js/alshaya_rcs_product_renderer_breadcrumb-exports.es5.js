const rcsPhBreadcrumb = require('../../alshaya_rcs_magento_placeholders/js/alshaya_rcs_magento_placeholders_breadcrumb-exports.es5');

/**
 * Breadcrumb renderer for product pages.
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
  return rcsPhBreadcrumb.render(settings, breadcrumbs, innerHtml);
};

/**
 * Normalize the original data.
 *
 * @param data
 *    The original data.
 */
exports.normalize = function normalize(
  data,
  keys = { nameKey: 'name', breadcrumbTermNameKey: 'category_name' }
) {
  if (!Array.isArray(data.categories) || data.categories.length < 1) {
    return [];
  }

  let normalized = [];

  // Get categories from data.
  let { categories } = data;

  // Get category flagged as `category_ids_in_admin`.
  categories = categories.filter((e) => {
    return data.category_ids_in_admin.includes(e.id.toString());
  });

  // Get the category based on the breadcrumb category id from the categories
  // array.
  let breadcrumbCategoryId = parseInt(data.breadcrumb_category_id, 10);
  let category = categories.filter((e) => {
    return e.id == breadcrumbCategoryId;
  });

  if (category.length > 0) {
    // Filter function returns an array. So getting item at first position.
    category = category.shift();
    normalized = rcsPhBreadcrumb.getNormalizedBreadcrumbs(normalized, category.breadcrumbs, keys);

    // Push the last part of the normalized.
    normalized.push({
      url: category.url_path,
      text: category[keys.nameKey],
      data_url: category.url_path,
      id: category.id,
    });
  }

  // Push the last crumb without a url.
  normalized.push({
    url: null,
    text: data[keys.nameKey],
  });

  return normalized;
};
