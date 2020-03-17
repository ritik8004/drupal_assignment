import { i18nMiddleWareUrl } from './i18n_url';
import { getInfoFromStorage } from './storage';

/**
 * Get the middleware get cart endpoint.
 *
 * @returns {string}
 */
export const getCartApiUrl = () => i18nMiddleWareUrl('cart');

export const cartAvailableInStorage = () => {
  // Get data from local storage.
  const cartData = getInfoFromStorage();
  // If data is not available in storage, we flag it to check/fetch from api.
  if (!cartData) {
    return null;
  }

  // 1m time for expire.
  // @Todo: Make this 10m (configurable from BE).
  const expireTime = 1 * 60 * 1000;
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

export const getGlobalCart = () => ((window.cartData && window.cartData.cart)
  ? window.cartData.cart
  : null
);

export const redirectToCart = () => {
  if (window.location.pathname.search(/checkout/i) >= 0) {
    window.location = Drupal.url('cart');
  }
};
