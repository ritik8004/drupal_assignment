
/**
 * Utility function to store given data in local storage with key.
 *
 * @param {object or array} data
 *  Object or array to store in local storage.
 * @param {string} storageKey
 *  Key to identify data in local storage.
 */
export const setStorageInfo = (data, storageKey) => {
  // Return void if storage key isn't defined.
  if (typeof storageKey === 'undefined') {
    return;
  }

  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

/**
 * Function to remove data from the local storage with key.
 *
 * @param {string} storageKey
 *  Key to identify data in local storage.
 */
export const removeStorageInfo = (storageKey) => {
  // Return void if storage key isn't defined.
  if (typeof storageKey === 'undefined') {
    return;
  }
  localStorage.removeItem(storageKey);
};

/**
 * Function to get data from the local storage with key.
 *
 * @param {string} storageKey
 *  Key to identify data in local storage.
 *
 * @returns {null or object}
 *  Return data stored in local storage or null.
 */
export const getStorageInfo = (storageKey) => {
  // Return null if storage key isn't defined.
  if (typeof storageKey === 'undefined') {
    return null;
  }
  const storageItem = localStorage.getItem(storageKey);
  if (!storageItem) {
    return null;
  }

  try {
    return JSON.parse(storageItem);
  } catch (e) {
    return storageItem;
  }
};
