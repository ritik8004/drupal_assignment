export const setStorageInfo = (data, storageKey = 'appointment_data') => {
  // Adding current time to storage.
  const appointmentInfo = data;
  appointmentInfo.last_update = new Date().getTime();

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
    const storageItemArray = JSON.parse(storageItem);
    const expireTime = drupalSettings.alshaya_appointment.local_storage_expire;
    const currentTime = new Date().getTime();

    // Return null if data is expired and clear localStorage.
    if (((currentTime - storageItemArray.last_update) > expireTime)) {
      removeStorageInfo();

      return null;
    }

    return storageItemArray;
  } catch (e) {
    return storageItem;
  }
};
