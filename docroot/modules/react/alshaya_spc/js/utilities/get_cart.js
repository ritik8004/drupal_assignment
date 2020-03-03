import axios from 'axios';
import { restoreCartApiUrl } from './update_cart';
import { i18nMiddleWareUrl } from './i18n_url';

/**
 * Get the middleware get cart endpoint.
 *
 * @returns {string}
 */
export function getCartApiUrl(cart_id) {
  return i18nMiddleWareUrl(`cart/${cart_id}`);
}

export const cartAvailableInStorage = function () {
  // Get data from local storage.
  var cart_data = localStorage.getItem('cart_data');
  // If data is not available in storage, we flag it to check/fetch from api.
  if (!cart_data) {
    return null;
  }

  // 1m time for expire.
  // @Todo: Make this 10m (configurable from BE).
  const expire_time = 1 * 60 * 1000;
  const current_time = new Date().getTime();
  var cart_data = JSON.parse(cart_data);

  // If data/cart is expired or cart has different language than
  // currently selected language.
  if ((current_time - cart_data.cart.last_update) > expire_time
    || cart_data.cart.langcode === undefined
    || window.drupalSettings.path.currentLanguage !== cart_data.cart.langcode) {
    return cart_data.cart.cart_id;
  }

  return cart_data.cart;
};

export const fetchCartData = function () {
  // Check if cart available in storage.
  let cart = cartAvailableInStorage();
  if (!cart) {
    // Prepare api url.
    var api_url = restoreCartApiUrl();

    return axios.get(api_url)
      .then((response) => {
        if (typeof response !== 'object') {
          redirectToCart();
          return null;
        }
        if (response.data.error) {
          redirectToCart();
        }
        return response.data;
      })
      .catch((error) => {
        // Processing of error here.
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
  var api_url = getCartApiUrl(cart);

  return axios.get(api_url)
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
    });
};

export const getGlobalCart = () => ((window.cart_data && window.cart_data.cart) ? window.cart_data.cart : null);

export const redirectToCart = () => {
  if (window.location.pathname.search(/checkout/i) >= 0) {
    window.location = Drupal.url('cart');
  }
};
