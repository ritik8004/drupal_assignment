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
  if (inputs.length !== 0) {
    // Get the L1 menu list element.
    const menuListLevel1Ele = innerHtmlObj.find('.menu__list.menu--one__list');

    // Filter category menu items if include_in_menu flag true.
    inputs = filterAvailableItems(inputs);

    // Sort the remaining category menu items by position in asc order.
    inputs.sort(function (a, b) {
      return parseInt(a.position) - parseInt(b.position);
    });

    let menuHtml = '';
    // Iterate over each L1 item and get the inner markup
    // prepared recursively.
    inputs.forEach(function eachCategory(level1) {
      menuHtml += getMenuMarkup(level1, 1, innerHtmlObj, settings);
    });

    // Remove the placeholders markup.
    menuListLevel1Ele.find('li').remove();

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
 *
 * @returns
 *  {string} Generated menu markup for given level.
 */
const getMenuMarkup = function (levelObj, level, phHtmlObj, settings) {
  // We support max depth by L4.
  if (level > parseInt(drupalSettings.alshayaRcs.navigationMenu.menuMaxDepth)) {
    return;
  }

  // @todo build menu item path prefix.
  const menuPathPrefixFull = `${settings.path.pathPrefix}${settings.rcsPhSettings.categoryPathPrefix}`;
  // @todo remove this when API return the correct path.
  levelObj.url_path = `${menuPathPrefixFull}${levelObj.url_path}`;

  const levelIdentifier = `level-${level}`;
  const ifChildren = levelObj.children && levelObj.children.length > 0;

  // Clone the relevant placeholder element from the given html.
  var clonePhEle = null;
  if (levelObj.is_anchor) {
    clonePhEle = phHtmlObj.find(`li.${levelIdentifier}.clickable`).clone();
  }
  else {
    // For non-clickable the placeholder name is different.
    levelObj.name1 = levelObj.name;
    clonePhEle = phHtmlObj.find(`li.${levelIdentifier}.non-clickable`).clone();
  }

  // If menu has no children further, return with actual markup.
  if (!ifChildren) {
    clonePhEle.find('ul').remove();
    return navRcsReplacePh(clonePhEle, levelObj);
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

      levelObj.children.forEach(function eachCategory(rcLevelObj) {
        let l2_cost = (rcLevelObj.children)
          ? rcLevelObj.children.length + 2
          : 2;

        // If we are detecting a longer column than the expected size
        // we iterate with new max.
        if (l2_cost > ideal_max_col_length) {
          ideal_max_col_length = l2_cost;
          reprocess = true;
          return;
        }

        if ((colTotal + l2_cost) > ideal_max_col_length) {
          col++;
          colTotal = 0;
          isNewColumn = true;
        }

        // If we have too many columns we try with more items per column.
        if (col >= max_nb_col) {
          ideal_max_col_length++;
          return;
        }

        if (isNewColumn) {
          levelHtml += `</div><div class="column ${col}">`;
          isNewColumn = false;
        }

        levelHtml += getMenuMarkup(rcLevelObj, (parseInt(level) + 1), phHtmlObj, settings);

        colTotal += l2_cost;
      });
    } while (reprocess || (col >= max_nb_col));

    levelHtml += '</div>';
  } else {
    // For deep level items or if not a menu inline layout.
    levelObj.children.forEach(function eachCategory(rcLevelObj) {
      levelHtml += getMenuMarkup(rcLevelObj, (parseInt(level) + 1), phHtmlObj, settings);
    });
  }

  clonePhEle.find('ul > div:first-child').append(levelHtml);
  return navRcsReplacePh(clonePhEle, levelObj);
};

/**
 * It will take the element, replace the navigation placeholders
 * in that and return the output Html.
 */
const navRcsReplacePh = function (phElement, entity) {
  const langcode = drupalSettings.path.currentLanguage;

  // Identify all the field placeholders and get the replacement
  // value. Parse the html to find all occurrences at apply the
  // replacement.
  rcsPhReplaceEntityPh(phElement[0].outerHTML, 'category', entity, langcode)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];

      $(`:contains('${fieldPh}')`)
        .each(function eachEntityPhReplace() {
          $(phElement).html(
            $(phElement)
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
      for (const attribute of ['href']) {
        $(`[${attribute} *= '${fieldPh}']`, phElement)
          .each(function eachEntityPhAttributeReplace() {
            $(this).attr(attribute, $(this).attr(attribute).replace(fieldPh, entityFieldValue));
          });
      }
    });
  return phElement[0].outerHTML;
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
