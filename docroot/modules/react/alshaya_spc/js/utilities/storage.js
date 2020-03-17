export const setStorageInfo = (data, storageKey = 'cart_data') => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey = 'cart_data') => {
  localStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey = 'cart_data') => {
  const storageItem = localStorage.getItem(storageKey);
  try {
    return JSON.parse(storageItem);
  } catch (e) {
    return storageItem;
  }
};

export const addInfoInStorage = (cart) => {
  const cartInfo = { ...cart };
  // Adding current time to storage to know the last time cart updated.
  cartInfo.last_update = new Date().getTime();
  setStorageInfo(cart);
};

export const removeCartFromStorage = () => {
  removeStorageInfo('cart_data');
};

export const getInfoFromStorage = () => {
  return getStorageInfo('cart_data');
};
