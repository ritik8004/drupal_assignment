exports.prepareData = function prepareData(settings, inputs) {
  const {
    idealMaxColLength,
    menuLayout,
    maxNbCol,
    menuMaxDepth,
    highlightTiming,
  } = settings;

  // Clone the input data.
  let inputsClone = JSON.parse(JSON.stringify(inputs));
  // Clean up data.
  inputsClone = processData(inputsClone, menuMaxDepth);

  switch (menuLayout) {
    case 'menu_inline_display':
      break;

    case 'menu_dynamic_display':
    case 'default':
    default:
      // Distribute L3 items into columns.
      inputsClone = splitIntoCols(inputsClone, maxNbCol, idealMaxColLength);
  }

  return {
    'menu_type': menuLayout,
    'menu_items': inputsClone,
    'user_logged_in': drupalSettings.user.uid > 1,
    'path_prefix': drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix,
    'aura_enabled': drupalSettings.aura.enabled,
    'highlight_timing': highlightTiming,
    'promopanel_class': '', // @todo Implement promo panel block class.
    'tag': 'a',
  };
}

/**
 * Process and add the enrichments to the menu item.
 *
 * @param {Object} menuItem
 *   Individual menu item object.
 */
function processEnrichment(menuItem) {
  var enrichmentData = globalThis.rcsGetEnrichedCategories();
  var enrichedMenuItem = enrichmentData[menuItem.url_path];

  if (!Drupal.hasValue(enrichedMenuItem)) {
    return;
  }

  menuItem = Object.assign(menuItem, enrichedMenuItem);
}

/**
 * Clean up api data.
 *
 * @param {object} data
 *   The category data.
 *
 * @param {integer} maxLevel
 *   The max depth of menus.
 *
 *  @return object data
 *   The cleaned data.
 */
const processData = function (data, maxLevel) {
  // Convert arrays to objects;
  data = Object.assign({}, data);

  // Loop object;
  for (const [key, value] of Object.entries(data)) {
    processEnrichment(data[key]);

    if (typeof data[key].include_in_desktop !== 'undefined') {
      if (!data[key].include_in_desktop) {
        data[key].hide_in_desktop = 'hide-on-desktop';
      }
      else {
        data[key].hide_in_desktop = '';
      }
    }

    if (typeof data[key].include_in_mobile_tablet !== 'undefined') {
      if (!data[key].include_in_mobile_tablet) {
        data[key].hide_in_mobile_tablet = 'hide-on-mobile';
      }
      else {
        data[key].hide_in_mobile_tablet = '';
      }
    }

    if (Drupal.hasValue(data[key].move_to_right)) {
      data[key].move_to_right = 'move-to-right';
    }

    // Check if we have an array or object.
    if ((/array|object/).test(typeof value)) {
      // Check if the item should be included in the menu.
      if (typeof data[key].include_in_menu !== 'undefined'
        && !data[key].include_in_menu) {
        delete (data[key]);
      }
      // Check if we reached max level.
      else if (typeof data[key].level !== 'undefined'
        && data[key].level - 1 > maxLevel) {
        delete (data[key]);
      }
    }
    // Check children.
    if (typeof data[key].children !== 'undefined') {
      if (Object.values(data[key].children).length < 1) {
        // When the menus don't have children, we set to false. This is required
        // because Handlebars doesn't check empty objects in the same way it does
        // for arrays, see https://handlebarsjs.com/guide/builtin-helpers.html#if.
        data[key].children = false;
      }
      else {
        processData(data[key].children, maxLevel);
      }
    }
  }

  return data;
}

/**
 * Moves children into separate columns to evenly distribute menu items.
 *
 * @param {object} data
 *   The menu data.
 *
 * @param {integer} maxCols
 *   Max number of columns.
 *
 * @param {integer} maxRows
 *   Max number of rows.
 *
 * @return {object}
 *   The menu data distributed into columns.
 */
function splitIntoCols(data, maxCols = 6, maxRows = 10) {
  // Initialize flags.
  let reprocess, col, col_total, columns = null;
  // Convert arrays to objects;
  data = Object.assign({}, data);
  // Loop each L1 item.
  for (const [key, value] of Object.entries(data)) {
    // Reset ajustableMaxRows before looping L2 items.
    let adjustableMaxRows = maxRows;
    if (typeof value.children !== 'undefined' && Object.keys(value.children).length) {
      // Loop L2 items.
      do {
        // Reset flags;
        columns = [];
        col_total = 0;
        col = 0;
        reprocess = false;

        // Loop L2 items.
        for (const [key, child] of Object.entries(value.children)) {
          // Calculate the rows needed for L3 items + 2
          // 2 means L2 item + one blank line for spacing).
          l2_cost = 2 + Object.keys(child.children).length;

          // If we are detecting a longer rows than the expected size
          // we iterate with new max.
          if (l2_cost > adjustableMaxRows) {
            adjustableMaxRows = l2_cost;
            reprocess = true;
            break;
          }

          if (col_total + l2_cost > adjustableMaxRows) {
            col++;
            col_total = 0;
          }

          // If we have too many columns we try with more items per column.
          if (col >= adjustableMaxRows) {
            adjustableMaxRows++;
            reprocess = true;
            break;
          }

          if (typeof columns[col] == 'undefined') {
            columns[col] = [];
          }

          // Push L3 items into column.
          columns[col].push(child);
          col_total += l2_cost;
        }
      } while (reprocess || col >= adjustableMaxRows);
      // Replace child items.
      data[key]['children'] = columns;
      // Children are moved into columns and can be deleted now.
      delete (data[key].columns);
    }
  }
  return data;
}
