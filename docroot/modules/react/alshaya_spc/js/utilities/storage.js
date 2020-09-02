export const setStorageInfo = (data, storageKey = 'cart_data') => {
  const dataToStore = (typeof data === 'object') ? JSON.stringify(data) : data;
  localStorage.setItem(storageKey, dataToStore);
};

export const removeStorageInfo = (storageKey = 'cart_data') => {
  localStorage.removeItem(storageKey);
};

export const getStorageInfo = (storageKey = 'cart_data') => {
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

export const addInfoInStorage = (cart) => {
  const cartInfo = { ...cart };
  // Adding current time to storage to know the last time cart updated.
  cartInfo.last_update = new Date().getTime();
  setStorageInfo(cartInfo);
};

export const removeCartFromStorage = () => {
  removeStorageInfo('cart_data');

  // Remove last selected payment on page load.
  // We use this to ensure we trigger events for payment method
  // selection at-least once and not more than once.
  removeStorageInfo('last_selected_payment');
};

export const getInfoFromStorage = () => {
  const cartData = getStorageInfo('cart_data');
  return cartData;
};
