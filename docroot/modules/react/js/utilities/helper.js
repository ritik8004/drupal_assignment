/**
 * Helper function to check if AURA is enabled.
 * 
 * This will be true only when alshaya_aura_react module is enabled.
 */
function isAuraEnabled() {
  let enabled = false;
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'enabled')
    && drupalSettings.aura.enabled) {
    enabled = true;
  }

  return enabled;
}

const setStorageInfo = (data, storageKey) => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

const removeStorageInfo = (storageKey) => {
  localStorage.removeItem(storageKey);
};

const getStorageInfo = (storageKey) => {
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

export {
  isAuraEnabled,
  setStorageInfo,
  removeStorageInfo,
  getStorageInfo
};
