exports.prepareData = function prepareData(settings, inputs) {
  const {
    idealMaxColLength,
    menuLayout,
    maxNbCol,
    menuMaxDepth,
    highlightTiming,
  } = settings;

  // Clean up data.
  inputs = filterValues(inputs, menuMaxDepth);

  switch (menuLayout) {
    case 'menu_inline_display':
      break;

    case 'menu_dynamic_display':
    case 'default':
    default:
      // Distribute L3 items into columns.
      inputs = splitIntoCols(inputs, maxNbCol, idealMaxColLength);
  }

  return {
    'menu_type': menuLayout,
    'menu_items': inputs,
    'user_logged_in': drupalSettings.user.uid > 1,
    'path_prefix': drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix,
    'aura_enabled': drupalSettings.aura.enabled,
    'highlight_timing': highlightTiming,
    'level_class': '', // @todo Implement level_class class.
    'promopanel_class': '', // @todo Implement promo panel block class.
    'tag': 'a',
  };
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
const filterValues = function (data, maxLevel) {
  /**
   * Checks if data is scalar.
   *
   * @param {mixed} data
   *   The data
   *
   * @return {boolean}
   *   True if it is scalar, or false.
   */
  function isScalar(data) {
    return (/boolean|number|string/).test(typeof data)
  }

  /**
   * Remove unwanted items from the data array.
   *
   * @param {array} data
   *   The data.
   *
   * @param {string/integer} key
   *   The key.
   */
  function removeUnwantedItem(data, key) {
    // Keep numeric values.
    if (parseInt(key) >= 0) {
      return;
    }

    // List of keys that we want to keep.
    const allowedKeys = [
      'name',
      'meta_title',
      'url_path',
      'level',
      'include_in_menu',
      'children',
    ];
    if (allowedKeys.includes(key)) {
      return;
    }

    // Delete item.
    delete(data[key]);
  }

  /**
   * Iterate and remove unwanted data.
   *
   * @param {object/array} data
   *   The data.
   *
   * @return {object}
   *   The clean data.
   */
  function iterate(data) {
    // Convert arrays to objects;
    data = Object.assign({}, data);

    // Loop object;
    for (const [key, value] of Object.entries(data)) {
      // Check if we have an array or object.
      if (!isScalar(value)) {
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
        // Go oe level deeper into the data.
        else {
          data[key] = iterate(value);
        }
      }

      // Remove unwanted data.
      removeUnwantedItem(data, key);
    }

    return data;
  }

  return iterate(data);
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
