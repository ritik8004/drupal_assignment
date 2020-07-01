import i18nMiddleWareUrl from './i18n_url';
import { getInfoFromStorage } from './storage';

/**
 * Get the middleware get cart endpoint.
 *
 * @returns {string}
 */
export const getCartApiUrl = () => i18nMiddleWareUrl('cart/get');

/**
 * Get the middleware get cart for checkout endpoint.
 *
 * @returns {string}
 */
export const getCartForCheckoutApiUrl = () => i18nMiddleWareUrl('cart/checkout');

export const cartAvailableInStorage = () => {
  // Get data from local storage.
  const cartData = getInfoFromStorage();
  // If data is not available in storage, we flag it to check/fetch from api.
  if (!cartData || !cartData.cart) {
    return null;
  }

  // 15m time for expire.
  // @Todo: Make this 10m (configurable from BE).
  const storageExpireTime = parseInt(drupalSettings.alshaya_spc.cart_storage_expiration);
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
