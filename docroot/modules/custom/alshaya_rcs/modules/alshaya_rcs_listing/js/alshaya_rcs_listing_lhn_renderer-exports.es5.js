// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.
exports.render = function render(
  settings,
  inputs,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);

  // Extract the clickable and unclickable elements.
  let clickable = '';
  let unclickable = '';
  if (innerHtmlObj.find('li').length > 1) {
    clickable = innerHtmlObj.find('li').first().html();
    unclickable = innerHtmlObj.find('li').first().next().html();
  }

  // Proceed only if the elements are present.
  if (clickable && unclickable) {
    // Remove the placeholder li elements.
    innerHtmlObj.find('li').remove();
    innerHtmlObj.find('ul').append(buildLhnHtml('', inputs, clickable, unclickable, settings));
  }

  return innerHtmlObj.html();
}

const buildLhnHtml = function (itemHtml, items, clickable, unclickable, settings) {
  if (!items) {
    return itemHtml;
  }

  items.forEach(item => {
    itemHtml += '<li>';
    // @todo Add check for clickable and unclickable based on magento response.
    // Replace placeholders with response value.
    itemHtml += replaceLhnPlaceHolders(item, clickable, settings);

    if (item.children !== undefined && item.children !== null) {
      itemHtml += '<ul>';
      itemHtml = buildLhnHtml(itemHtml, item.children, clickable, unclickable, settings);
      itemHtml += '</ul>';
    }
    itemHtml += '</li>';
  });

  return itemHtml;
}

const replaceLhnPlaceHolders = function(item, itemHtml, settings) {
  const clonedElement = jQuery('<li>' + itemHtml + '</li>');
  // Change URL based on current language and category prefix.
  item.url_path = '/' + settings.path.pathPrefix + settings.rcsPhSettings.categoryPathPrefix + item.url_path;

  // Identify all the field placeholders and get the replacement
  // value. Parse the html to find all occurrences at apply the
  // replacement.
  rcsPhReplaceEntityPh(itemHtml, 'lhn', item, settings.path.currentLanguage)
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

  return clonedElement.html();
}
