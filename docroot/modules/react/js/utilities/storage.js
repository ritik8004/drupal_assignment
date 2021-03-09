export const setStorageInfo = (data, storageKey) => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey) => {
  localStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey) => {
  const storageItem = localStorage.getItem(storageKey);
  if (!storageItem) {
    return null;
  }

  try {
    const storageItemArray = JSON.parse(storageItem);
    // @TODO: Handle storage expiration.
    return storageItemArray;
  } catch (e) {
    return storageItem;
  }
};
