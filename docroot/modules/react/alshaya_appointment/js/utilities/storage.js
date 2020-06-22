export const setStorageInfo = (data, storageKey = 'appointment_data') => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey = 'appointment_data') => {
  localStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey = 'appointment_data') => {
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
