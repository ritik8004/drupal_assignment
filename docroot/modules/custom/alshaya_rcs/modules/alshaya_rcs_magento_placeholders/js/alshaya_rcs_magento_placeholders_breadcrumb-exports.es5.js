// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.

// Render function to build the markup of breadcrumb and replace the placeholder
// with API response.
exports.render = function render(
  settings,
  breadcrumbs,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  // Proceed only if entity is present.
  if (breadcrumbs !== null) {
    // Get the enrichment data. It's a sync call.
    let enrichmentData = rcsGetEnrichedCategories();

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
      if (enrichmentData && enrichmentData[breadcrumb.url]) {
        enrichedDataObj = enrichmentData[breadcrumb.url];

        // Add no-link class if item is set as not clickable.
        if (!enrichedDataObj.item_clickable) {
          breadcrumb.classes = 'no-link';
        }
        // Proceed only if breadcrumb is not marked as removed.
        hideBreadcrumb = enrichedDataObj.remove_from_breadcrumb
      }

      if (!hideBreadcrumb) {
        // Perform URL update before applying URL enrichment.
        breadcrumb.url = Drupal.url(`${breadcrumb.url}/`);
        // Override the link based on enrichment path attribute.
        if (enrichedDataObj && typeof enrichedDataObj.path !== 'undefined') {
          breadcrumb.url = enrichedDataObj.path;
        }
        breadcrumbHtml += getBreadcrumbMarkup(breadcrumb, innerHtmlObj, settings);
      }
    });

    // Remove the placeholders markup.
    innerHtmlObj.find('li').remove();
    // Update with the resultant markups.
    innerHtmlObj.find('ol').append(homeEl + breadcrumbHtml);
  }
  return innerHtmlObj.html();
}

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
      breadcrumbItemHtml = rcsReplaceAll(breadcrumbItemHtml, fieldPh, entityFieldValue);
    });

  return breadcrumbItemHtml;
}
