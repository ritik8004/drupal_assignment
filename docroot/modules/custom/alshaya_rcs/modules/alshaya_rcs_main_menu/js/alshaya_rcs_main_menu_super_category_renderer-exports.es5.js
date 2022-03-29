exports.render = function render(
  settings,
  inputs,
  innerHtml
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  let superCategoryHtml = '';

  if (inputs.length !== 0) {
    // Get the enrichment data. It's a sync call.
    // Check if static storage is having value, If 'YES' then use that else call
    // the API.
    let enrichmentData = globalThis.rcsGetEnrichedCategories();

    // Sort the remaining category menu items by position in asc order.
    inputs.sort(function (a, b) {
      return parseInt(a.position) - parseInt(b.position);
    });

    // Get the L1 menu list element.
    const menuListLevel1Ele = innerHtmlObj.find('.menu__list.menu--one__list');

    // Add active class based on the current active page else make first
    // element as default active.
    // Get the active super category.
    let activeSuperCategory = globalThis.rcsWindowLocation().pathname.split('/')[2];
    // Check if the current page is a valid super category or not.
    let validSuperCategory = false;
    inputs.forEach((item) => {
      if (activeSuperCategory == item.url_path) {
        validSuperCategory = true;
        item.classes = 'active';
      }
    });
    if (!validSuperCategory && inputs[0].url_path.length) {
      // If there are no active super category then make first item as default.
      inputs[0].classes = 'active';
    }
    // Go through level 1 items which we are considering as super category for
    // Victoria secrets.
    inputs.forEach((input, key) => {
      superCategoryHtml += replaceSuperCategoryPlaceHolders(
        input,
        menuListLevel1Ele.html(),
        settings,
        enrichmentData && enrichmentData[input.url_path] ? [enrichmentData[input.url_path]] : [],
        key == 0 ? true : false,
      );
    });
    // Remove the placeholder markup.
    innerHtmlObj.find('li').remove();

    innerHtmlObj.find('ul.menu--one__list').append(superCategoryHtml);
  }
  return innerHtmlObj.html();
};

/**
 * Replace the placeholders with the Super Category items.
 *
 * @param {object} item
 *   The individual category item object.
 * @param {string} itemHtml
 *   HTML snippit with placeholders for the item.
 * @param {object} settings
 *   The drupal settings object.
 * @param {object} enrichment
 *   Enriched data object for the current item.
 * @param {boolean} first
 *   Current item is the first item of the super category menu or not.
 * @returns
 *   {string} Single Super Category item HTML with proper data.
 */
const replaceSuperCategoryPlaceHolders = function (item, itemHtml, settings, enrichment, first) {
  // Update the item attributes with proper data.
  if (first) {
    // Change URL path if it's the first item.
    item.url_path = `/${settings.path.pathPrefix}`;
  } else {
    item.url_path = `/${settings.path.pathPrefix}${item.url_path}/`;
  }
  item.classes += ` base-${makeSafeForCss(item.name)}`
  // Modify item properties based on enrichment data.
  let logo_active_image, logo_inactive_image = '';
  if (enrichment.length > 0
    && enrichment[0].hasOwnProperty('icon')
    && enrichment[0].icon.hasOwnProperty('logo_active_image')
    && enrichment[0].icon.hasOwnProperty('logo_inactive_image')) {
      // Use enriched images if present else use default.
      logo_active_image = enrichment[0].icon.logo_active_image;
      logo_inactive_image = enrichment[0].icon.logo_inactive_image;
  }
  else {
    // Load default images.
    const defaultImages = getSuperCategoryDefaultImages(item, settings);
    logo_active_image = defaultImages.logo_active_image;
    logo_inactive_image = defaultImages.logo_inactive_image;
  }

  item.inactive_image = logo_active_image;
  item.image = item.classes.indexOf('active') != -1 ? logo_active_image : logo_inactive_image;

  // Identify all the field placeholders and get the replacement
  // value. Parse the html to find all occurrences at apply the
  // replacement.
  rcsPhReplaceEntityPh(itemHtml, 'super_category', item, settings.path.currentLanguage)
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

/**
 * Provides super category default images.
 *
 * @param {object} item
 *   The individual category item object.
 * @param {object} settings
 *   The drupal settings object.
 * @return returns an object containing the default super category images.
 */
const getSuperCategoryDefaultImages = function (item, settings) {
  const termName = makeSafeForCss(item.name);
  const { theme } = settings;
  return {
    'logo_active_image': `/${theme.path}/imgs/logos/super-category/${termName}-active.svg`,
    'logo_inactive_image': `/${theme.path}/imgs/logos/super-category/${termName}.svg`,
  }
}

/**
 * Provides term name as safe css indentifier.
 *
 * @param {string} name
 *   The term name which needs to be filtered out.
 * @returns return a string safe for css indentifier.
 */
const makeSafeForCss = function (name) {
  return name.replace(/[^a-z0-9]/g, function(s) {
      var c = s.charCodeAt(0);
      if (c == 32) return '-';
      if (c >= 65 && c <= 90) return s.toLowerCase();
      return ('000' + c.toString(16)).slice(-4);
  });
}
