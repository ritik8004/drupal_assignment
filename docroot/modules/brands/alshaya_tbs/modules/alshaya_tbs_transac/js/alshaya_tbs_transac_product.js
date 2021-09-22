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
    case 'description':
      // @todo this will be updated when CORE-34014 is ready.
      data[fieldName].label = rcsTranslatedText('Features and benefits');
      break;

    case 'short_description':
      // @todo this will be updated when CORE-34014 is ready.
      data[fieldName].label = rcsTranslatedText('Features and benefits');
      data[fieldName] = data['description'];
      break;
  }
};
