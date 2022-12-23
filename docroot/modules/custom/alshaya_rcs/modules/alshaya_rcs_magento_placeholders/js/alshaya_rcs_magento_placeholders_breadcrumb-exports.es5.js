// Render function to build the markup of breadcrumb and replace the placeholder
// with API response.
exports.render = function render(
  settings,
  breadcrumbs,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  var currentPath = window.location.href;
  var isViewAll = 0;
  if (currentPath.indexOf('/view-all') != -1) {
    breadcrumbs.map(function (bdata) {
      if (bdata.data_url.indexOf('view-all') != -1) {
        isViewAll = 1;
      }
    });
    if (!isViewAll) {
      let viewAllObj = { 'data_url' : '', 'id' :'', 'text' : 'View All', 'url' : '' };
      breadcrumbs.push(viewAllObj);
    }
  }

  // Proceed only if entity is present.
  if (breadcrumbs !== null) {
    // Get the enrichment data. It's a sync call.
    let enrichmentData = globalThis.rcsGetEnrichedCategories();

    let breadcrumbHtml = '';

    // Check if Home link is already present, if Yes then extract it.
    let homeEl = '';
    if (innerHtmlObj.find('li').length > 1) {
      homeEl = innerHtmlObj.find('li').first()[0].outerHTML;
      innerHtmlObj.find('li').first().remove();
    }

    // Iterate through each breadcrumb item and generate the markup.
    breadcrumbs != null && breadcrumbs.forEach(function eachBreadcrumb(breadcrumb) {
      // Get the enrichment data from the settings.
      let enrichedDataObj = {};
      let hideBreadcrumb = 0;
      if (enrichmentData && enrichmentData[breadcrumb.data_url]) {
        enrichedDataObj = enrichmentData[breadcrumb.data_url];

        // Add no-link class if item is set as not clickable.
        if (!enrichedDataObj.item_clickable) {
          breadcrumb.classes = 'no-link';
        }
        // Proceed only if breadcrumb is not marked as removed.
        hideBreadcrumb = enrichedDataObj.remove_from_breadcrumb
      }

      if (!hideBreadcrumb) {
        // Perform URL update before applying URL enrichment.
        breadcrumb.url = breadcrumb.url ? Drupal.url(`${breadcrumb.url}/`) : '';
        // Override the link based on enrichment path attribute.
        if (enrichedDataObj && typeof enrichedDataObj.path !== 'undefined') {
          breadcrumb.url = enrichedDataObj.path;
        }
        // Added whitespace after trailing slash to match with V2 markup.
        breadcrumbHtml += getBreadcrumbMarkup(breadcrumb, innerHtmlObj, settings) + ' ';
      }
    });

    // Remove the placeholders markup.
    innerHtmlObj.find('li').remove();
    // Update with the resultant markups.
    innerHtmlObj.find('ol').append(homeEl + ' ' + breadcrumbHtml);
  }
  return innerHtmlObj.html();
}

/**
 * Utility function to get the normalized breadcrumb items.
 *
 * @param {array} normalized
 *   An array containing the normalized breadcrumb items.
 * @param {array} breadcrumbs
 *   The breadcrumb array.
 * @param {object} keys
 *   An object containing the breadcrumb keys.
 *
 * @returns {array}
 *   The updated normalized breadcrumb array.
 */
exports.getNormalizedBreadcrumbs = function getNormalizedBreadcrumbs(normalized, breadcrumbs, keys) {
  breadcrumbs.forEach(function (item) {
    normalized.push({
      url: item.category_url_path,
      text: item[keys.breadcrumbTermNameKey],
      data_url: item.category_url_path,
      id: item.category_id,
    });
  });

  return normalized;
};

/**
 *
 * @param {object} breadcrumb
 * @param {string} innerHtmlObj
 * @param {object} settings
 * @returns
 *   Return the placeholder replaced breadcrumb markup.
 */
const getBreadcrumbMarkup = function (breadcrumb, innerHtmlObj, settings) {
  // Clone the breadcrumb placeholder element.
  let clonedElement = innerHtmlObj.find('li').clone();
  // Identify all the field placeholders and get the replacement
  // value. Parse the html to find all occurrences at apply the
  // replacement.
  let breadcrumbItemHtml = clonedElement[0].outerHTML;
  rcsPhReplaceEntityPh(breadcrumbItemHtml, 'breadcrumb', breadcrumb, settings.path.currentLanguage)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];

      // Apply the replacement on all the elements containing the
      // placeholder.
      breadcrumbItemHtml = globalThis.rcsReplaceAll(breadcrumbItemHtml, fieldPh, entityFieldValue);
    });

  return breadcrumbItemHtml;
}
