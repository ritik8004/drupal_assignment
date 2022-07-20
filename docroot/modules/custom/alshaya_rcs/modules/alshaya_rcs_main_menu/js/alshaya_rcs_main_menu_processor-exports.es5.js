exports.prepareData = function prepareData(settings, inputs) {
  // @todo use menuMaxDepth;
  const {
    idealMaxColLength,
    menuLayout,
    maxNbCol,
    menuMaxDepth,
    highlightTiming,
  } = settings;

  // List of keys that we will use to render Handlebars templates.
  const allowedKeys = [
    'name',
    'meta_title',
    'url_path',
    'level',
    'include_in_menu',
    'children',
  ];
  inputs = filterAllowedValues(inputs, allowedKeys);

  switch (menuLayout) {
    case 'menu_inline_display':
      break;

    case 'menu_dynamic_display':
    case 'default':
    default:
      // Distribute L3 items into columns.
      inputs = splitIntoCols(inputs, maxNbCol, idealMaxColLength);
  }


  // Filter category menu items if include_in_menu flag true.
  // @todo fix this
  // inputs = filterAvailableItems(inputs);

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
 * Filters items to include in menu.
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

/**
 * Clean up api data, keeping only keys that we will use to render the template.
 *
 * @param object data
 *   The category data.
 *
 * @param array allowedKeys
 *   The list of keys to keep.
 */
const filterAllowedValues = function (data, allowedKeys) {
  function iterate(data) {
    // Convert arrays to objects;
    data = Object.assign({}, data);
    // Loop object;
    for (const [key, value] of Object.entries(data)) {
      // Keep numeric values and allowed values.
      if (allowedKeys.includes(key) || parseInt(key) >= 0) {
        // Keep item.
      }
      else {
        // Delete item.
        delete(data[key]);
        continue;
      }
      // Go deeper into arrays and objects.
      if (value.constructor === Array || value.constructor === Object) {
        data[key] = iterate(value);
      }
    }
    return data;
  }
  return iterate(data);
}

/**
 * Moves children into separate columns to evenly distribute menu items.
 *
 * @param object data
 *   The menu data.
 *
 * @param integer maxCols
 *   Max number of columns.
 *
 * @param integer maxRows
 *   Max number of rows.
 *
 * @return object
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
