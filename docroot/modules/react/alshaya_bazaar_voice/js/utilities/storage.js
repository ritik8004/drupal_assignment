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

export const addInfoInStorage = (review) => {
  const reviewInfo = { ...review };
  // Adding current time to storage to know the last time reviews updated.
  reviewInfo.last_update = new Date().getTime();
  setStorageInfo(reviewInfo);
};

export const removeReviewsFromStorage = () => {
  removeStorageInfo('reviews_data');
};

export const getInfoFromStorage = () => {
  const reviewData = getStorageInfo('reviews_data');
  return reviewData;
};
