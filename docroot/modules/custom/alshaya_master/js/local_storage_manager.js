(function ($, Drupal) {
  'use strict';
  /**
   * Drupal utility functions for the local storage manager.
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
  Drupal.addItemInLocalStorage = function (
    storageKey = null,
    storageData = null,
    expireAfterTime = 0) {
    // Return if data to store is not provided, or
    // the local storage key is not set, of
    // storage expiry time is zero.
    if (!storageData || !storageKey) {
      return false;
    }

    // Prepare the expiry time for the storage data. Storage expiry
    // time must be provided in seconds.
    const expiry_time = new Date().getTime() + (expireAfterTime * 1000);

    // Store data in the local storage with the expiry time.
    localStorage.setItem(storageKey, JSON.stringify({
      data: ((typeof storageData === 'object')
        ? storageData
        : JSON.parse(storageData)),
      expiry_time,
    }));

    return true;
  };

  /**
   * Helper function to remove an item from the local storage.
   *
   * @param {string} storageKey
   *  Local storage key to remove associated data.
   *
   * @returns {boolean}
   *  true/false based on the action performed.
   */
  Drupal.removeItemFromLocalStorage = function (storageKey = null) {
    // Remove item from the local storage if key is set.
    return (storageKey)
      ? localStorage.removeItem(storageKey)
      : false;
  };

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
  Drupal.getItemFromLocalStorage = function (storageKey = null) {
    // Return if the local storage key is not set.
    if (!storageKey) {
      return null;
    }

    // Return is item not found in the storage with the provided key.
    let storageItem = localStorage.getItem(storageKey);
    if (!storageItem) {
      return null;
    }

    // If item is available parse the info to JSON object.
    storageItem = JSON.parse(storageItem);

    // Check if the storage items data isn't expired. For that,
    // we will check if the expiry_time is set with the storage
    // item and it should be set to a future time.
    const currentTime = new Date().getTime();
    if (typeof storageItem.expiry_time !== 'undefined'
      && currentTime > storageItem.expiry_time) {
      // If item is expired, we will remove this from the local storage.
      Drupal.removeItemFromLocalStorage(storageKey);
      return null;
    }

    // Prepare data to return. If the data attribute is set
    // else we will return item retrieved from storage.
    const dataToReturn = (typeof storageItem.data !== 'undefined')
      ? storageItem.data
      : storageItem;

    // Add expiry time with return data for other components
    // or modules to perform custom actions
    dataToReturn.expiry_time = storageItem.expiry_time;

    // Return the prepared data.
    return dataToReturn;
  };

  /**
   * Async function to check the local storage data for the given keys
   * and remove if expiry time for that data is reached.
   */
  Drupal.runLocalStorageCleaner = async function () {
    // Key to consider for checking and cleaning. These can be modified
    // and more can be added here based on the future implementation.
    // @todo: see if we can make it dynamic and manage from the relevant
    // modules itself.
    const keysForCleaner = [
      'facets',
      'productinfo',
      'cart_data',
      'appointment_data',
      'wishlist',
      'user_action_logger',
    ];

    // Get all the local storage keys having the above defined key strings.
    const filteredKeys = Object.keys(localStorage).filter(
      (localStgKey) => keysForCleaner.some(
        (stgKey) => localStgKey.includes(stgKey)
      )
    );

    // If we have keys available to clean, iterate and run get item storage
    // helper func for each to remove it from local storage, if expired
    if (filteredKeys) {
      filteredKeys.forEach(storageKey => {
        Drupal.getItemFromLocalStorage(storageKey);
      });
    }
  };

  // Run the cleaner function once on the window load event.
  $(window).once('html').on('load', function () {
    console.log("Imhere")
    Drupal.runLocalStorageCleaner();
  });

})(jQuery, Drupal);
