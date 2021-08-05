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

    let breadcrumbHtml = '';

    // Check if Home link is already present, if Yes then extract it.
    let homeEl = '';
    if (innerHtmlObj.find('li').length > 1) {
      homeEl = innerHtmlObj.find('li').first()[0].outerHTML;
      innerHtmlObj.find('li').first().remove();
    }

    // Iterate through each breadcrumb item and generate the markup.
    breadcrumbs != null && breadcrumbs.forEach(function eachBreadcrumb(breadcrumb) {
      // Attach the language prefix and category pathprefix.
      breadcrumb.category_url_path = '/' + settings.path.pathPrefix + settings.rcsPhSettings.categoryPathPrefix + breadcrumb.category_url_path;
      // @todo To add a check here based on field_remove_term_in_breadcrumb.
      breadcrumbHtml += getBreadcrumbMarkup(breadcrumb, innerHtmlObj, settings);
    });

    // Add the current item in the breadcrumb.
    breadcrumbHtml += getBreadcrumbMarkup({
      'category_name': entity.name,
      'category_url_path': '/' + settings.path.pathPrefix + settings.rcsPhSettings.categoryPathPrefix + entity.url_path,
    }, innerHtmlObj, settings);

    // Remove the placeholders markup.
    innerHtmlObj.find('li').remove();
    // Update with the resultant markups.
    innerHtmlObj.find('ol').append(homeEl + breadcrumbHtml);
  }
  return innerHtmlObj.html();
}

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
