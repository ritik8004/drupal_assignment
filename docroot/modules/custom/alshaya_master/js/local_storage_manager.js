(function ($, Drupal, drupalSettings) {
  'use strict';
  /**
   * Drupal utility functions for the local storage manager.
   */

  /**
   * Helper function to add an item into the local storage.
   * @param {string} storageKey
   *  A string key to identify provided data in storage.
   * @param {object|string} storageData
   *  Data to store in the local storage.
   * @param {integer} expireAfterTime
   *  Time, in seconds, after this data will be expired and clean.
   *
   * @returns {boolean}
   *  true/false based on the action performed.
   */
  Drupal.addItemInLocalStorage = function (
    storageKey,
    storageData = null,
    expireAfterTime = null) {

    // Return if data to store is not provided, or
    // the local storage key is not set, of
    // storage expiry time is zero.
    if (!storageKey || (storageData === null)) {
      return false;
    }

    // Prepare the expiry time for the storage data. Storage expiry
    // time must be provided in seconds.
    // If it's zero, we don't store data in the local storage.
    if (expireAfterTime === 0) {
      return false;
    }

    // Prepare data to store.
    const dataToStore = { data: storageData };

    // Specialkeys for the cookie.
    var specialKeys = drupalSettings?.alshaya_master?.cookie_keys;
    if (expireAfterTime) {
      dataToStore.expiry_time = new Date().getTime() + (parseInt(expireAfterTime) * 1000);
    }

    // Avoids insecure notice in mobile browsers when cookies are disabled.
    // If this is null, we don't set any expiry to the data storage in the local storage.
    try {
      // Store data in the local storage with the expiry time.
      localStorage.setItem(storageKey, JSON.stringify(dataToStore));
    }
    catch (e) {
      if (e.name === 'QuotaExceededError') {
        // Log quota exceeded error in datadog and set flag.
        if (sessionStorage.getItem('_quotaExceededErrorLogged') !== '1') {
          let localKeys = Object.keys(localStorage);
          let _lsTotal=0, totalSize;
          $.each(localKeys, function (index, value) {
            _lsTotal += ((localStorage[value].length + value.length)* 2);
          });
          totalSize = (_lsTotal / 1024).toFixed(2) + " KB";
          // Logging error message and size used in local storage.
          Drupal.alshayaLogger('error', e.message + ' Total size in localStorage: ' + totalSize, localKeys);
          sessionStorage.setItem('_quotaExceededErrorLogged', '1');
        }
      } else {
        Drupal.alshayaLogger('error', e.message);
        if (specialKeys && $.inArray(storageKey, specialKeys) > -1) {
          // Local storeage is not available save it in cookie only for allowed keys.
          var expiry = new Date().getTime() + (parseInt(expireAfterTime) * 1000);
          var cookieOptions = {path: '/', expires: expiry, secure: true};
          // Set cookie.
          $.cookie('local_storage_' + storageKey, JSON.stringify(dataToStore), cookieOptions);
          // Return true as an indication of values stored successfully.
          return true;
        }
      }
    }
    return false;
  };

  /**
   * Helper function to remove an item from the local storage.
   *
   * @param {string} storageKey
   *  Local storage key to remove associated data.
   */
  Drupal.removeItemFromLocalStorage = function (storageKey) {
    // Avoids insecure notice in mobile browsers when localStorage is disabled.
    $.removeCookie('local_storage_' + storageKey, {path: '/'});
    try {
      localStorage.removeItem(storageKey)
    }
    catch(e) {
      Drupal.alshayaLogger('warning', 'Error occurred while executing Drupal.removeItemFromLocalStorage. Error: @message.', {
        '@message': e.message,
      });
    }
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
  Drupal.getItemFromLocalStorage = function (storageKey) {
    // Avoids insecure notice in mobile browsers when cookies are disabled.
    try {
      // Return is item not found in the storage with the provided key.
      var storageItem = localStorage.getItem(storageKey);
    }
    catch(e) {
      // Log issue with localstorage.
      Drupal.alshayaLogger('warning', 'Error occurred while executing Drupal.getItemFromLocalStorage. Error: @message.', {
        '@message': e.message,
      });
      // Fetch from cookie if localstorage not present.
      var storageItem = $.cookie('local_storage_' + storageKey);
    }

    if (!storageItem) {
      return null;
    }

    try {
      // If item is available parse the info to JSON object.
      storageItem = JSON.parse(storageItem);
    }
    catch (error) {
      // If JSON parse failed we return string.
      return storageItem;
    }

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

    // Remove and return null, if local storage data is in old format.
    // For checking old format,
    // - expiry_time must not be set as we do set this in new format only
    // - last_update or create must be set that we use now.
    // - anything within the data object doesn't impact.
    if (typeof storageItem.expiry_time === 'undefined'
      && (typeof storageItem.last_update !== 'undefined'
      || typeof storageItem.created !== 'undefined')) {
      // If item is expired, we will remove this from the local storage.
      Drupal.removeItemFromLocalStorage(storageKey);
      return null;
    }

    // If it's a new format simply return the data.
    return (typeof storageItem.data !== 'undefined') ? storageItem.data : storageItem;
  };

  /**
   * Async function to check the local storage data for all keys
   * and remove if expiry time for that data is reached.
   */
  Drupal.runLocalStorageCleaner = async function () {
    try {
      // Get all the local storage keys having the above defined key strings.
      const filteredKeys = Object.keys(localStorage);

      // If we have keys available to clean, iterate and run get item storage
      // helper func for each to remove it from local storage, if expired
      if (filteredKeys) {
        filteredKeys.forEach(storageKey => {
          Drupal.getItemFromLocalStorage(storageKey);
        });
      }
    }
    catch(e) {
      Drupal.alshayaLogger('warning', 'Error occurred while executing Drupal.runLocalStorageCleaner. Error: @message.', {
        '@message': e.message,
      });
    }
  };

  // Run the cleaner function once on the window load event.
  $(window).once('html').on('load', function () {
    Drupal.runLocalStorageCleaner();
  });

})(jQuery, Drupal, drupalSettings);
