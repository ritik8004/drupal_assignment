exports.render = function render(
  settings,
  inputs,
  innerHtml,
  navigationType
) {
  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);
  if (inputs.length !== 0) {
    // Get the enrichment data. It's a sync call.
    // Check if static storage is having value, If 'YES' then use that else call
    // the API.
    let enrichmentData = globalThis.rcsGetEnrichedCategories();
    let menuListLevel1Ele = navigationType === 'shop_by_block'
      ? innerHtmlObj.find('div.c-footer-menu')
      : innerHtmlObj.find('.menu__list.menu--one__list');

    // Filter category menu items if include_in_menu flag true.
    inputs = filterAvailableItems(inputs);

    // Sort the remaining category menu items by position in asc order.
    inputs.sort(function (a, b) {
      return parseInt(a.position) - parseInt(b.position);
    });

    let menuHtml = '';
    // Get the active super category.
    const isSuperCategoryEnabled = (typeof settings.superCategory) != "undefined";
    // Proceed only if superCategory is enabled.
    if (isSuperCategoryEnabled) {
      let activeSuperCategory = globalThis.rcsWindowLocation().pathname.split('/')[2];
      // Check if the active super category is valid or not.
      let validSuperCategory = false;
      inputs.forEach((item) => {
        if (activeSuperCategory == item.url_path) {
          validSuperCategory = true;
        }
      });
      if (!validSuperCategory && inputs[0].url_path.length) {
        // If there are no active super category then make first item as default.
        activeSuperCategory = inputs[0].url_path;
      }
      // Filter out the items that doesn't belong to the active super category.
      if (isSuperCategoryEnabled) {
        inputs = inputs.filter((item) => {
          return activeSuperCategory == item.url_path;
        });
      }
    }
    // Iterate over each L1 item and get the inner markup
    // prepared recursively.
    inputs.forEach(function eachCategory(level1) {
      // Change item level only if super category is enabled.
      if (isSuperCategoryEnabled) {
        // Return from here if L2 element is empty and if the child element is
        // not associated with the current active super category.
        if (!level1.children) {
          return;
        }
        level1 = level1.children[0];
      }

      menuHtml = navigationType === 'shop_by_block'
        ? menuHtml + getShopByMarkup(
          level1,
          1,
          innerHtmlObj,
          settings,
          enrichmentData,
          isSuperCategoryEnabled,
        )
        : menuHtml + getMenuMarkup(
          level1,
          1,
          innerHtmlObj,
          settings,
          enrichmentData,
          isSuperCategoryEnabled,
        );
    });

    // Remove the placeholders markup.
    if (navigationType === 'shop_by_block') {
      menuListLevel1Ele.find('div.c-footer-menu__tab').remove();
    } else {
      menuListLevel1Ele.find('li').remove();
    }

    // Update with the resultant markups.
    menuListLevel1Ele.append(menuHtml);
  }
  return innerHtmlObj.html();
};

/**
 *
 * @param {object} levelObj
 * @param {integer} level
 * @param {string} phHtmlObj
 * @param {object} settings
 * @param {object} enrichmentData
 * @param {boolean} isSuperCategoryEnabled
 *
 * @returns
 *  {string} Generated menu markup for given level.
 */
const getMenuMarkup = function (levelObj, level, phHtmlObj, settings, enrichmentData, isSuperCategoryEnabled) {
  // We support max depth by L4.
  if (level > parseInt(drupalSettings.alshayaRcs.navigationMenu.menuMaxDepth)) {
    return;
  }

  // Build menu item path prefix.
  const menuPathPrefixFull = `${settings.path.pathPrefix}`;
  const levelObjOrgUrlPath = levelObj.url_path;
  // Append category prefix in L2 if super category is enabled.
  if (isSuperCategoryEnabled) {
      let urlItems = levelObj.url_path.split('/');
      if (urlItems.length > 1) {
        urlItems[1] = `${settings.rcsPhSettings.categoryPathPrefix}${urlItems[1]}`;
      }
      levelObj.url_path = `/${settings.path.pathPrefix}${urlItems.join('/')}/`;
  } else {
    levelObj.url_path = `/${menuPathPrefixFull}${levelObjOrgUrlPath}/`;
  }

  const levelIdentifier = `level-${level}`;
  const ifChildren = levelObj.children && levelObj.children.length > 0;

  // Clone the default clickable placeholder element from the given html.
  var clonePhEle = phHtmlObj.find(`li.${levelIdentifier}.clickable`).clone();

  let enrichedDataObj = {};
  // Get the enrichment data from the settings.
  if (enrichmentData && enrichmentData[levelObjOrgUrlPath]) {
    enrichedDataObj = enrichmentData[levelObjOrgUrlPath];

    // Override label from Drupal.
    levelObj.name = enrichedDataObj.name;

    // Check if term font and background color are available.
    if (enrichedDataObj.font_color) {
      clonePhEle.find('div.menu__link-wrapper a, div.menu__link-wrapper div').css("color", enrichedDataObj.font_color);
    }
    if (enrichedDataObj.background_color) {
      clonePhEle.find('div.menu__link-wrapper a, div.menu__link-wrapper div').css("background-color", enrichedDataObj.background_color);
    }

    // Hide on desktop / mobile.
    if (!enrichedDataObj.include_in_desktop) {
      clonePhEle.addClass('hide-on-desktop');
    }
    if (!enrichedDataObj.include_in_mobile_tablet) {
      clonePhEle.addClass('hide-on-mobile');
    }

    // Move level item to right column. Only for L2 items.
    if (enrichedDataObj.move_to_right && level === 2) {
      clonePhEle.addClass('move-to-right');
    }

    // Override the path if exists.
    // @todo: Need to handle the super category case.
    if (typeof enrichedDataObj.path !== 'undefined') {
      levelObj.url_path = enrichedDataObj.path;
    }

    // Attach image icon with label.
    if (typeof enrichedDataObj.icon !== 'undefined') {
      levelObj.icon_url = enrichedDataObj.icon.icon_url;
    }

    // Override the clickable and non-clickable property.
    if (!enrichedDataObj.item_clickable) {
      clonePhEle = phHtmlObj.find(`li.${levelIdentifier}.non-clickable`).clone();
      levelObj.name1 = levelObj.name;
    }
  }

  // Remove icon markup if no icon or add
  // wrapper class if available.
  if (typeof levelObj.icon_url === 'undefined') {
    clonePhEle.find('span.icon').remove();
  } else {
    clonePhEle.addClass('with-icon');
  }

  // If menu has no children further, return with actual markup.
  if (!ifChildren) {
    clonePhEle.find('ul').remove();
    return navRcsReplacePh(clonePhEle, levelObj, 'menuItem');
  }

  // If menu has children further.
  let levelHtml = '';
  clonePhEle.find('ul li, div.column').remove();

  // Build menu column for L1 dynamically, if a default layout.
  if (level === 1
    && drupalSettings.alshayaRcs.navigationMenu.menuLayout !== 'menu_inline_display') {
    const max_nb_col = drupalSettings.alshayaRcs.navigationMenu.maxNbCol;
    let ideal_max_col_length = drupalSettings.alshayaRcs.navigationMenu.idealMaxColLength;
    let reprocess = false;
    let col = 0;

    do {
      let colTotal = 0;
      let isNewColumn = false;

      col = 0;
      reprocess = false;
      levelHtml = `<div class="column ${col}">`;

      levelObj.children.every(function eachCategory(rcLevelObj) {
        let l2_cost = (rcLevelObj.children)
          ? rcLevelObj.children.length + 2
          : 2;

        // If we are detecting a longer column than the expected size
        // we iterate with new max.
        if (l2_cost > ideal_max_col_length) {
          ideal_max_col_length = l2_cost;
          reprocess = true;
          return false;
        }

        if ((colTotal + l2_cost) > ideal_max_col_length) {
          col++;
          colTotal = 0;
          isNewColumn = true;
        }

        // If we have too many columns we try with more items per column.
        if (col >= max_nb_col) {
          ideal_max_col_length++;
          return false;
        }

        if (isNewColumn) {
          levelHtml += `</div><div class="column ${col}">`;
          isNewColumn = false;
        }

        levelHtml += getMenuMarkup(
          rcLevelObj,
          (parseInt(level) + 1),
          phHtmlObj,
          settings,
          enrichmentData,
          isSuperCategoryEnabled,
        );

        colTotal += l2_cost;

        return true;
      });
    } while (reprocess || (col >= max_nb_col));

    levelHtml += '</div>';
  } else {
    // For deep level items or if not a menu inline layout.
    levelObj.children.forEach(function eachCategory(rcLevelObj) {
      levelHtml += getMenuMarkup(
        rcLevelObj,
        (parseInt(level) + 1),
        phHtmlObj,
        settings,
        enrichmentData,
        isSuperCategoryEnabled,
      );
    });
  }

  clonePhEle.find('ul > div:first-child').append(levelHtml);
  // Override the path if exists.
  // @todo: Need to handle the super category case.
  if (typeof enrichedDataObj.highlight_paragraphs !== 'undefined'
    && level === 1) {
    clonePhEle.find('ul div.term-image__wrapper').append(enrichedDataObj.highlight_paragraphs.markup);
    if (enrichedDataObj.highlight_paragraphs.text_link_para) {
      clonePhEle.find('ul div.term-image__wrapper').addClass('text-link-para');
    }
  }
  return navRcsReplacePh(clonePhEle, levelObj, 'menuItem');
};

/**
 *
 * @param {object} levelObj
 * @param {integer} level
 * @param {string} phHtmlObj
 * @param {object} settings
 * @param {object} enrichmentData
 * @param {boolean} isSuperCategoryEnabled
 *
 * @returns
 *  {string} Generated shop by markup for given level.
 */
const getShopByMarkup = function (levelObj, level, phHtmlObj, settings, enrichmentData, isSuperCategoryEnabled) {
  // We support max depth by L4.
  if (level > parseInt(drupalSettings.alshayaRcs.navigationMenu.menuMaxDepth)) {
    return;
  }

  // Build menu item path prefix.
  const menuPathPrefixFull = `${settings.path.pathPrefix}${settings.rcsPhSettings.categoryPathPrefix}`;
  // @todo remove this when API return the correct path.
  const levelObjOrgUrlPath = levelObj.url_path;
  // Append category prefix in L2 if super category is enabled.
  if (isSuperCategoryEnabled) {
    let urlItems = levelObj.url_path.split('/');
    if (urlItems.length > 1) {
      urlItems[1] = `${settings.rcsPhSettings.categoryPathPrefix}${urlItems[1]}`;
    }
    levelObj.url_path = `/${settings.path.pathPrefix}${urlItems.join('/')}/`;
  } else {
    levelObj.url_path = `/${menuPathPrefixFull}${levelObjOrgUrlPath}/`;
  }

  const levelIdentifier = `c-footer-menu__tab`;
  // Clone the default clickable placeholder element from the given html.
  var clonePhEle = phHtmlObj.find(`div.${levelIdentifier}`).clone();

  let enrichedDataObj = {};
  // Get the enrichment data from the settings.
  if (enrichmentData && enrichmentData[levelObjOrgUrlPath]) {
    enrichedDataObj = enrichmentData[levelObjOrgUrlPath];
    // Override label from Drupal.
    levelObj.name = enrichedDataObj.name;

    // Exclude terms with overridden target link.
    if (typeof enrichedDataObj.path !== 'undefined') {
      return '';
    }
  }

  return navRcsReplacePh(clonePhEle, levelObj, 'shopbymenuItem');
};

/**
 * It will take the element, replace the navigation placeholders
 * in that and return the output Html.
 */
const navRcsReplacePh = function (phElement, entity, markupId) {
  const langcode = drupalSettings.path.currentLanguage;
  // Identify all the field placeholders and get the replacement
  // value. Parse the html to find all occurrences at apply the
  // replacement.
  let menuItemHtml = phElement[0].outerHTML;
  rcsPhReplaceEntityPh(menuItemHtml, markupId, entity, langcode)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];
      // Apply the replacement on all the elements containing the
      // placeholder.
      menuItemHtml = globalThis.rcsReplaceAll(menuItemHtml, fieldPh, entityFieldValue);
    });

  return menuItemHtml;
};

/**
 *
 * @param {array} catArray
 *
 * @returns Filtered array with items for which the value of
 *  include_in_menu property is true.
 */
const filterAvailableItems = function (catArray) {
  return catArray.filter(
    innerCatArray => (innerCatArray.include_in_menu === 1)
  );
};
