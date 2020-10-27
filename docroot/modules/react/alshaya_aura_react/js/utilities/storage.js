export const setStorageInfo = (data, storageKey = 'aura_data') => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey = 'aura_data') => {
  localStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey = 'aura_data') => {
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
