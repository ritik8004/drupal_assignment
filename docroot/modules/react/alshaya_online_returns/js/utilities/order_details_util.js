/* eslint-disable */

/**
 * Utility function to return the type of return.
 *
 * @param {object} returnItem
 *   The individual return item object.
 *
 * @returns {string}
 *   A string to tell the type of return.
 */
function getTypeFromReturnItem(returnItem) {
  // Based on the `is_online` flag we will identify the type of return.
  if (returnItem.returnInfo.extension_attributes.is_online) {
    return 'online';
  } else {
    return 'store';
  }
}

export {
  getTypeFromReturnItem,
};
