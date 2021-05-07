export const setStorageInfo = (data, storageKey = 'reviews_data') => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey = 'reviews_data') => {
  localStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey = 'reviews_data') => {
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

export const removeReviewsFromStorage = () => {
  removeStorageInfo('reviews_data');
};

export const getInfoFromStorage = () => {
  const reviewData = getStorageInfo('reviews_data');
  return reviewData;
};

export const updateStorageInfo = (contentType, contentObj, contentId) => {
  const storageList = getStorageInfo(contentType) !== null
    ? getStorageInfo(contentType) : [];
  let contentExists = false;
  if (storageList !== null) {
    console.log(contentObj);
    const updatedStorage = storageList.map((contentStorage) => {
      // Check if current content already exists in storage.
      if (contentStorage.id === contentId) {
        const storageObj = { ...contentStorage };
        const returnedTarget = Object.assign(storageObj, contentObj);
        contentExists = true;
        return returnedTarget;
      }
      return contentStorage;
    });
    if (contentExists) {
      setStorageInfo(JSON.stringify(updatedStorage), contentType);
    } else {
      storageList.push(contentObj);
      setStorageInfo(JSON.stringify(storageList), contentType);
    }
  }
};
