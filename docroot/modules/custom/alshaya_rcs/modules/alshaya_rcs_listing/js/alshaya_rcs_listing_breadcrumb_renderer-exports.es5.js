// Render function to build the markup of breadcrumb and replace the placeholder
// with API response.
exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  // Proceed only if entity is present.
  if (entity !== null) {
    // Extract breadcrumb from the entity response.
    const { breadcrumbs } = entity;
    // @todo To optimise the multiple API Calls.
    // Get the enrichment data. It's a sync call.
    let enrichmentData = [];
    jQuery.ajax({
      url: Drupal.url('v2/categories'),
      async: false,
      success: function (data) {
        enrichmentData = data;
      }
    });

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
      if (enrichmentData && enrichmentData[breadcrumb.category_url_path]) {
        enrichedDataObj = enrichmentData[breadcrumb.category_url_path];

        // Add no-link class if item is set as not clickable.
        if (!enrichedDataObj.item_clickable) {
          breadcrumb.classes = 'no-link';
        }
        // Proceed only if breadcrumb is not marked as removed.
        hideBreadcrumb = enrichedDataObj.remove_from_breadcrumb
      }
      if (!hideBreadcrumb) {
        breadcrumb.category_url_path = '/' + settings.path.pathPrefix + settings.rcsPhSettings.categoryPathPrefix + breadcrumb.category_url_path;
        breadcrumbHtml += getBreadcrumbMarkup(breadcrumb, innerHtmlObj, settings);
      }
    });

    // Reset the flag value.
    hideBreadcrumb = 0;
    // Add the current item in the breadcrumb.
    if (enrichmentData && enrichmentData[entity.url_path]) {
      hideBreadcrumb = enrichmentData[entity.url_path].remove_from_breadcrumb;
    }
    if (!hideBreadcrumb) {
      breadcrumbHtml += getBreadcrumbMarkup({
        'category_name': entity.name,
        'category_url_path': '/' + settings.path.pathPrefix + settings.rcsPhSettings.categoryPathPrefix + entity.url_path,
      }, innerHtmlObj, settings);
    }

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
  rcsPhReplaceEntityPh(clonedElement[0].outerHTML, 'categories', breadcrumb, settings.path.currentLanguage)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];

      // Apply the replacement on all the elements containing the
      // placeholder. We filter to keep only the child element
      // and not the parent ones.
      $(`:contains('${fieldPh}')`)
        .each(function eachEntityPhReplace() {
          $(clonedElement).html(
            $(clonedElement)
              .html()
              .replace(fieldPh, entityFieldValue)
          );
        });

      //":contains" only returns the elements for which the
      // placeholder is part of the content, it won't return the
      // elements for which the placeholder is part of the
      // attribute values. We are now fetching all the elements
      // which have placeholders in the attributes and we
      // apply the replacement.
      for (const attribute of settings.rcsPhSettings.placeholderAttributes) {
        $(`[${attribute} *= '${fieldPh}']`, clonedElement)
          .each(function eachEntityPhAttributeReplace() {
            $(this).attr(attribute, $(this).attr(attribute).replace(fieldPh, entityFieldValue));
          });
      }
    });

  return clonedElement[0].outerHTML;
}
