export const setStorageInfo = (data, storageKey) => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  sessionStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey) => {
  sessionStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey) => {
  const storageItem = sessionStorage.getItem(storageKey);
  if (!storageItem) {
    return null;
  }

  try {
    const storageItemArray = JSON.parse(storageItem);
    return storageItemArray;
  } catch (e) {
    return storageItem;
  }
};
