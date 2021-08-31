// Breadcrumb renderer for listing pages.

const breadcrumbRenderer = require('../../alshaya_rcs_magento_placeholders/js/alshaya_rcs_magento_placeholders_breadcrumb-exports.es5');

exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  let breadcrumbs = [];

  // Prepare the breadcrumb array.
  Object.keys(entity.breadcrumbs).forEach(function (i) {
    breadcrumbs.push({
      url: entity.breadcrumbs[i].category_url_path,
      text: entity.breadcrumbs[i].category_name,
    });
  });

  // Push the last crumb without a url.
  breadcrumbs.push({
    url: null,
    text: entity.name,
  });

  return breadcrumbRenderer.render(settings, breadcrumbs, innerHtml);
};
