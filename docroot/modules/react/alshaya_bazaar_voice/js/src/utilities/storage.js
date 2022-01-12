export const setStorageInfo = (data, storageKey = 'reviews_data') => Drupal.addItemInLocalStorage(storageKey, data);

export const removeStorageInfo = (storageKey = 'reviews_data') => Drupal.removeItemFromLocalStorage(storageKey);

export const getStorageInfo = (storageKey = 'reviews_data') => Drupal.getItemFromLocalStorage(storageKey);

export const updateStorageInfo = (contentType, contentObj, contentId) => {
  const storageList = getStorageInfo(contentType) !== null
    ? getStorageInfo(contentType) : [];
  let contentExists = false;
  if (storageList !== null) {
    const updatedStorage = storageList.map((contentStorage) => {
      // Check if current content already exists in storage.
      if (contentStorage.id === contentId) {
        const storageObj = { ...contentStorage };
        const mergedStorage = Object.assign(storageObj, contentObj);
        contentExists = true;
        return mergedStorage;
      }
      return contentStorage;
    });
    if (contentExists) {
      setStorageInfo(updatedStorage, contentType);
    } else {
      storageList.push(contentObj);
      setStorageInfo(storageList, contentType);
    }
  }
};
