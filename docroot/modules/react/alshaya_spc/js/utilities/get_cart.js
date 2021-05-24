import { getInfoFromStorage } from './storage';

export const cartAvailableInStorage = () => {
  // Get data from local storage.
  const cartData = getInfoFromStorage();
  // If data is not available in storage, we flag it to check/fetch from api.
  if (!cartData || !cartData.cart) {
    return null;
  }

  // Configurable expiration time, by default it is 15m.
  const storageExpireTime = parseInt(drupalSettings.alshaya_spc.cart_storage_expiration, 10);
  const expireTime = storageExpireTime * 60 * 1000;
  const currentTime = new Date().getTime();

  // If data/cart is expired or cart has different language than
  // currently selected language.
  if ((currentTime - cartData.last_update) > expireTime
    || cartData.cart.langcode === undefined
    || drupalSettings.path.currentLanguage !== cartData.cart.langcode) {
    // Do nothing if empty cart is there.
    if (cartData.cart.cart_id === null) {
      return 'empty';
    }

    return cartData.cart.cart_id;
  }

  return cartData.cart;
};

export const redirectToCart = () => {
  if (window.location.pathname.search(/checkout/i) >= 0) {
    window.location = Drupal.url('cart');
  }
};
