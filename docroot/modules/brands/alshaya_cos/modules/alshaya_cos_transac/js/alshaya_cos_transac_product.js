/**
 * @file
 * JS file for product pages.
 */

/**
 * Alter the field data.
 *
 * @param fieldName
 *   The field name being processed.
 * @param data
 *   The data set.
 */
window.rcsFieldDataAlter = function (fieldName, data) {
  switch (fieldName) {
    case 'short_description':
      data[fieldName] = data['description'];
      break;
  }
};
