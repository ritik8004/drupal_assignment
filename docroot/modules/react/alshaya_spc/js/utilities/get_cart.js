import axios from 'axios';
import { restoreCartApiUrl } from './update_cart';
import { i18nMiddleWareUrl } from './i18n_url';
import { getInfoFromStorage } from './storage';

/**
 * Get the middleware get cart endpoint.
 *
 * @returns {string}
 */
export function getCartApiUrl() {
  return i18nMiddleWareUrl('cart');
}

export const cartAvailableInStorage = function () {
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
  // If someone tried to modify and it is not JSON string now.
  if (!cartData) {
    return null;
  }

  // If data/cart is expired or cart has different language than
  // currently selected language.
  if ((currentTime - cartData.cart.last_update) > expireTime
    || cartData.cart.langcode === undefined
    || window.drupalSettings.path.currentLanguage !== cartData.cart.langcode) {
    // Do nothing if empty cart is there.
    if (cartData.cart.cart_id === null) {
      return 'empty';
    }

    return cartData.cart.cart_id;
  }

  return cartData.cart;
};

export const fetchCartData = () => {
  // Check if cart available in storage.
  let cart = cartAvailableInStorage();

  if (cart === 'empty') {
    return;
  }

  if (!cart) {
    // Prepare api url.
    const apiUrl = restoreCartApiUrl();

    return axios.get(apiUrl)
      .then((response) => {
        if (typeof response !== 'object') {
          redirectToCart();
        }
        if (response.data.error) {
          redirectToCart();
        }
        return response.data;
      })
      .catch((error) => {
        // Processing of error here.
        console.error(error);
      });
  }
  if (!Number.isInteger(cart)) {
    // If we get integer, mean we get only cart id and thus we need to fetch
    // fresh cart. If we not get integer, means we get cart object and we can
    // just use and return that.
    if (cart.cart_id === null) {
      return null;
    }

    // On logout cart object will have a user id and drupalSettings uid will be
    // set to 0. Comparing this to figure out the user is logged out and hence the
    // cart data which is already there in localstorage is not valid and hence,
    // initiating object with empty data will show empty cart and mini cart.
    // Clearing the local storage will be taken care by emptyCustomerCart().
    if (cart.uid !== window.drupalSettings.user.uid && cart.uid > 0) {
      cart = {
        cart_id: null,
        cart_total: null,
        items_qty: null,
        items: [],
      };
    }

    return Promise.resolve(cart);
  }

  // Prepare api url.
  var api_url = getCartApiUrl();

  return axios.get(api_url)
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
      console.error(error);
    });
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
