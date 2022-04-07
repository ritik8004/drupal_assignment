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
    const firstLevelTermUrl = globalThis.rcsWindowLocation().pathname.match(`\/${settings.path.currentLanguage}\/(.*?)\/(.*?)$`);
    if (firstLevelTermUrl) {
      inputs = inputs.filter((input) => {
        return input.url_path == firstLevelTermUrl[1];
      });

      // Retrive the item from Level 3 as the response that we get from MDC starts
      // from level 2.
      // @todo Supercategory special case needs to verfied.
      let tempInputs = [];
      inputs.length && inputs[0].children && inputs[0].children.forEach((input, key) => {
        tempInputs[key] = input;
      });

      // Get the enrichment data. It's a sync call.
      let enrichmentData = globalThis.rcsGetEnrichedCategories();
      innerHtmlObj.find('ul').append(buildLhnHtml('', tempInputs, clickable, unclickable, settings, enrichmentData));
    }
  }

  return innerHtmlObj.html();
}

/**
 * Provides the LHN block HTML.
 *
 * @param {string} itemHtml
 *   The HTML snippit of LHN.
 * @param {object} items
 *   The object of LHN items.
 * @param {string} clickable
 *   HTML snippit of clickable item.
 * @param {string} unclickable
 *   HTML snippit of unclickable item.
 * @param {object} settings
 *   The drupal settings object.
 * @param {object} enrichmentData
 *   Enriched data object.
 * @returns
 *   {string} Full rendered HTML for the LHN block.
 */
const buildLhnHtml = function (itemHtml, items, clickable, unclickable, settings, enrichmentData) {
  if (!items || !items.length) {
    return itemHtml;
  }

  items.forEach(item => {
    if (typeof item != "undefined") {
      itemHtml += '<li>';
      // Check based on enrichment, if clickable is set or not.
      let html = clickable;
      let enrichmentDataObj = {};
      if (enrichmentData && enrichmentData[item.url_path]) {
        enrichmentDataObj = enrichmentData[item.url_path];
        // Change HTML based on enrichment attribute of clickable.
        if (!enrichmentDataObj.item_clickable) {
          html = unclickable;
        }
      }
      // Replace placeholders with response value and only do this if
      // show_in_lhn is set as true.
      if (item.show_in_lhn) {
        // Override the link based on enrichment path attribute.
        item.url_path = typeof enrichmentDataObj.path !== 'undefined' ?
          enrichmentDataObj.path : Drupal.url(`${item.url_path}/`);

        itemHtml += replaceLhnPlaceHolders(item, html, settings);
      }

      if (typeof item.children != "undefined" && item.children !== null) {
        itemHtml += '<ul>';
        itemHtml = buildLhnHtml(itemHtml, item.children, clickable, unclickable, settings, enrichmentData);
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
 *   The individual category item object.
 * @param {string} itemHtml
 *   HTML snippit with placeholders for the item.
 * @param {object} settings
 *   The drupal settings object.
 * @returns
 *   {string} Single LHN item HTML with proper data.
 */
const replaceLhnPlaceHolders = function (item, itemHtml, settings) {
  // lower the level as the response that we get from MDC starts from level 2.
  item.level -= 1;

  // Add active class based on current path.
  if (globalThis.rcsWindowLocation().pathname == item.url_path) {
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
      itemHtml = globalThis.rcsReplaceAll(itemHtml, fieldPh, entityFieldValue);
    });

  return itemHtml;
}
