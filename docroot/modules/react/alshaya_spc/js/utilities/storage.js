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

export const addInfoInStorage = (cart) => window.commerceBackend.setCartData(cart);

export const removeCartFromStorage = () => {
  removeStorageInfo('cart_data');

  // Remove last selected payment on page load.
  // We use this to ensure we trigger events for payment method
  // selection at-least once and not more than once.
  removeStorageInfo('last_selected_payment');
};

export const getInfoFromStorage = () => window.commerceBackend.getCartData();
