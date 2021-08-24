// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.

// Render function to prepare the markup for LHN Block and replace placeholders
// with API Response.
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
    // @todo Handle special base where we separate URL by - instead of /.
    const firstLevelTermUrl = rcsWindowLocation().pathname.match(/shop-(.*?)\/(.*?)$/);
    inputs = inputs.filter((input) => {
      return input.url_path == firstLevelTermUrl[1];
    });
    // Retrive the item from Level 3 as the response that we get from MDC starts
    // from level 2.
    // @todo Supercategory special case needs to verfied.
    let tempInputs = [];
    inputs.forEach((input, key) => {
      if (input.children[0] !== undefined && input.children[0] !== null) {
        tempInputs[key] = input.children[0];
      }
    });

    innerHtmlObj.find('ul').append(buildLhnHtml('', tempInputs, clickable, unclickable, settings));
  }

  return innerHtmlObj.html();
}

/**
 * Provides the LHN block HTML.
 *
 * @param {string} itemHtml
 * @param {object} items
 * @param {string} clickable
 * @param {string} unclickable
 * @param {object} settings
 * @returns
 *   {string} Full rendered HTML for the LHN block.
 */
const buildLhnHtml = function (itemHtml, items, clickable, unclickable, settings) {
  if (!items) {
    return itemHtml;
  }

  items.forEach(item => {
    if (typeof item != "undefined") {
      itemHtml += '<li>';
      // @todo Add check for clickable and unclickable based on magento response.
      // Replace placeholders with response value.
      itemHtml += replaceLhnPlaceHolders(item, clickable, settings);

      if (typeof item.children != "undefined" && item.children !== null) {
        itemHtml += '<ul>';
        itemHtml = buildLhnHtml(itemHtml, item.children, clickable, unclickable, settings);
        itemHtml += '</ul>';
      }
      itemHtml += '</li>';
    }
  });

  return itemHtml;
}

/**
 * Replace the placeholders with the LHN block items.
 *
 * @param {object} item
 * @param {string} itemHtml
 * @param {object} settings
 * @returns
 *   {string} Single LHN item HTML with proper data.
 */
const replaceLhnPlaceHolders = function (item, itemHtml, settings) {
  // Change URL based on current language and category prefix.
  item.url_path = `/${settings.path.pathPrefix}${settings.rcsPhSettings.categoryPathPrefix}${item.url_path}/`;
  // lower the level as the response that we get from MDC starts from level 2.
  item.level -= 1;

  // Add active class based on current path.
  if (rcsWindowLocation().pathname == item.url_path) {
    item.active = 'active';
  }
  const clonedElement = jQuery('<li>' + itemHtml + '</li>');
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
      itemHtml = rcsReplaceAll(itemHtml, fieldPh, entityFieldValue);
    });

  return itemHtml;
}
