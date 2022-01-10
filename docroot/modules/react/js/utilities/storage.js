/**
 * React utility functions for the alshaya local storage manager. Originally,
 * functions are available in `alshaya_master/js/local_storage_manager.js`.
 */

/**
   * Helper function to add an item into the local storage.
   *
   * @param {object|string} storageData
   *  Data to store in the local storage.
   * @param {string} storageKey
   *  A string key to identify provided data in storage.
   * @param {integer} expireAfterTime
   *  Time, in seconds, after this data will be expired and clean.
   *
   * @returns {boolean}
   *  true/false based on the action performed.
   */
export const setStorageInfo = (
  storageData,
  storageKey,
  expireAfterTime = null,
) => Drupal.getItemFromLocalStorage(
  storageKey,
  storageData,
  expireAfterTime,
);

/**
 * Helper function to remove an item from the local storage.
 *
 * @param {string} storageKey
 *  Local storage key to remove associated data.
 *
 * @returns {boolean}
 *  true/false based on the action performed.
 */
export const removeStorageInfo = (storageKey) => Drupal.removeItemFromLocalStorage(storageKey);

/**
 * Helper function to get data from the local storage for the
 * provided key. This will removed the item from the storage
 * as well if the data is expired.
 *
 * @param {string} storageKey
 *  Local storage key to get associated data for.
 *
 * @returns {object|string}
 *  Return null if no data available or is expired.
 *  Return data object if available.
 */
export const getStorageInfo = (storageKey) => Drupal.getItemFromLocalStorage(storageKey);
