import axios from 'axios';
import { restoreCartApiUrl } from './update_cart';

/**
 * Get the middleware get cart endpoint.
 *
 * @returns {string}
 */
export function getCartApiUrl(cart_id) {
  const langcode = window.drupalSettings.path.currentLanguage;
  return window.drupalSettings.alshaya_spc.middleware_url + '/cart/' + cart_id + '?lang=' + langcode;
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
  var expire_time = 1 * 60 * 1000;
  var current_time = new Date().getTime();
  var cart_data = JSON.parse(cart_data);

  // If data/cart is expired or cart has different language than
  // currently selected language.
  if ((current_time - cart_data.cart.last_update) > expire_time
    || cart_data.cart.langcode === undefined
    || window.drupalSettings.path.currentLanguage !== cart_data.cart.langcode) {
    return cart_data.cart.cart_id;
  }

  return cart_data.cart;
}

export const fetchCartData = function () {
  // Check if cart available in storage.
  var cart = cartAvailableInStorage();
  if (!cart) {
    // Prepare api url.
    var api_url = restoreCartApiUrl();

    return axios.get(api_url)
      .then(response => {
        return response.data
      })
      .catch(error => {
        // Processing of error here.
      });
  }
  else if (!Number.isInteger(cart)) {
    // If we get integer, mean we get only cart id and thus we need to fetch
    // fresh cart. If we not get integer, means we get cart object and we can
    // just use and return that.
    if (cart.cart_id === null) {
      return null;
    }

    // On logout cart object will havee a user id and drupalSettings uid will be
    // set to 0. Comparing this to figure out the user is logged out and hence the
    // cart data which is already there in localstorage is not valid and hence,
    // initiating object with empty data will show empty cart and mini cart.
    // Clearing the local storage will be taken care by emptyCustomerCart().
    if (cart.uid !== window.drupalSettings.user.uid && cart.uid.length > 0) {
      cart = {
        cart_id: null,
        cart_total: null,
        items_qty: null,
        items: []
      }
    }

    return Promise.resolve(cart);
  }

  // Prepare api url.
  var api_url = getCartApiUrl(cart);

  return axios.get(api_url)
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });

}

export const getGlobalCart = () => {
  return (window.cart_data && window.cart_data.cart) ? window.cart_data.cart : null;
}
