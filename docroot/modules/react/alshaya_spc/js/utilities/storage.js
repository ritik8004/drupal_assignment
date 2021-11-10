export const setStorageInfo = (data, storageKey = 'cart_data') => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey = 'cart_data') => {
  localStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey = 'cart_data') => {
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

/**
 * Gets properties from data in local storage.
 *
 * @param {string} key
 *   The key of the stored data.
 * @param {string} path
 *   The path to the data in the object. i.e. cart.customer.id
 *
 * @returns {Object|array|string|number|null}
 *   The value or null if not found.
 */
export const getStorageItem = (key = 'cart_data', path = null) => {
  const data = getStorageInfo(key);
  if (data) {
    // Splits the path using dot then tries to find the value inside the object.
    const value = path.split('.').reduce((prev, curr) => prev && prev[curr], data);
    if (typeof value !== 'undefined') {
      return value;
    }
  }

  return null;
};
