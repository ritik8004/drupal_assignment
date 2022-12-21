import Drupal from '../../../../../../core/misc/drupal.es6';
// Bridge to make Drupal Underscore available while running tests.
global._ = require('underscore');

let staticStorage = {};
export const drupalSettings = {
  jest: 1,
  cart: {
    url: '/rest/kwt_en',
    siteInfo: {
      site_code: 'vs',
      country_code: 'kw',
    },
    addressFields: {
      default: {
        kw: [
          'area',
          'address_apartment_segment',
        ],
      },
    },
    exceptionMessages: {
      'This product is out of stock.': 'OOS',
    },
  },
  path: {
    currentLanguage: 'en',
  },
  user: {
    uid: 0,
  },
  userDetails: {
    customerId: 0,
  },
};

// Define a mock implementation here otherwise will get error on running the
// test about the function not being found.
// This is most likely because this function is included by Drupal and is not a
// part of react.
window.commerceBackend = window.commerceBackend || {};
window.commerceBackend.getProductStatus = function getProductStatus() {};
window.commerceBackend.getCartId = function getCartId() {};
window.commerceBackend.getCartIdFromStorage = function getCartIdFromStorage() {};
window.commerceBackend.removeCartIdFromStorage = function removeCartIdFromStorage() {};

// Mock implementation of static storage.
global.Drupal.alshayaSpc = Drupal.alshayaSpc || {};
global.Drupal.alshayaSpc.staticStorage = global.Drupal.alshayaSpc || {};
// Mock function from alshaya_spc/assets/js/static_storage.js to help running
// the npm tests.
global.Drupal.alshayaSpc.staticStorage.clear = function clear() {
  staticStorage = {};
};
global.Drupal.alshayaSpc.staticStorage.set = function set(key, value) {
  staticStorage[key] = value;
};
global.Drupal.alshayaSpc.staticStorage.get = function get(key) {
  if (typeof staticStorage[key] === 'undefined') {
    return null;
  }

  return staticStorage[key];
};
global.Drupal.alshayaSpc.staticStorage.remove = function remove(key) {
  staticStorage[key] = null;
};

// Start copiying functions from alshaya_master/js/local_storage_manager.js
// to help running the npm tests.
// Duplicate of function `Drupal.addItemInLocalStorage`.
global.Drupal.addItemInLocalStorage = function addItemInLocalStorage(
  storageKey,
  storageData = null,
  expireAfterTime = null,
) {
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

  // If this is null, we don't set any expiry to the data storage in the local storage.
  if (expireAfterTime) {
    dataToStore.expiry_time = new Date().getTime() + (parseInt(expireAfterTime, 10) * 1000);
  }

  // Store data in the local storage with the expiry time.
  localStorage.setItem(storageKey, JSON.stringify(dataToStore));

  // Return true as an indication of values stored successfully.
  return true;
};

// Duplicate of function `Drupal.removeItemFromLocalStorage`.
global.Drupal.removeItemFromLocalStorage = function removeItemFromLocalStorage(storageKey) {
  // Remove item from the local storage if key is set.
  return (storageKey)
    ? localStorage.removeItem(storageKey)
    : false;
};

// Duplicate of function `Drupal.getItemFromLocalStorage`.
global.Drupal.getItemFromLocalStorage = function getItemFromLocalStorage(storageKey) {
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

  // Return the prepared data.
  return dataToReturn;
};

// End copiying functions from alshaya_master/js/local_storage_manager.js
// to help running the npm tests.

// Duplicate of function `Drupal.hasValue`.
global.Drupal.hasValue = function hasValue(value) {
  if (typeof value === 'undefined') {
    return false;
  }

  if (value === null) {
    return false;
  }

  if (Object.prototype.hasOwnProperty.call(value, 'length') && value.length === 0) {
    return false;
  }

  if (value.constructor === Object && Object.keys(value).length === 0) {
    return false;
  }

  return Boolean(value);
};


export default {
  drupalSettings,
  Drupal,
};
