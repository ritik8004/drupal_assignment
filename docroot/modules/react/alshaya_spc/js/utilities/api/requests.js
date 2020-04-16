import Axios from 'axios';
import {
  cartAvailableInStorage,
  getCartApiUrl,
  redirectToCart,
} from '../get_cart';
import { restoreCartApiUrl } from '../update_cart';
import { getInfoFromStorage } from '../storage';

export const fetchClicknCollectStores = (coords) => {
  const { cart } = getInfoFromStorage();
  if (!cart || !cart.cart_id) {
    return new Promise((resolve) => resolve(null));
  }

  const GET_STORE_URL = Drupal.url(
    `cnc/stores/${cart.cart_id}/${coords.lat}/${coords.lng}`,
  );
  return Axios.get(GET_STORE_URL);
};

export const fetchCartData = () => {
  // Check if cart available in storage.
  let cart = cartAvailableInStorage();

  if (cart === 'empty') {
    return null;
  }

  if (!cart) {
    // Prepare api url.
    const apiUrl = restoreCartApiUrl();

    return Axios.get(apiUrl).then((response) => {
      if (typeof response !== 'object') {
        redirectToCart();
        return;
      }

      if (response.data.error) {
        redirectToCart();
        return;
      }

      if (Object.values(response.data.items).length === 0) {
        redirectToCart();
        return;
      }

      return response.data;
    }).catch((error) => {
      // Processing of error here.
      Drupal.logJavascriptError('Failed to restore cart.', error);

      redirectToCart();
    });
  }
  if (!Number.isInteger(cart)) {
    // If we get integer, mean we get only cart id and thus we need to fetch
    // fresh cart. If we not get integer, means we get cart object and we can
    // just use and return that.
    if (cart.cart_id === null) {
      return null;
    }

    if (Object.values(cart.items).length === 0) {
      redirectToCart();
    }

    // On logout cart object will have a user id and drupalSettings uid will be
    // set to 0. Comparing this to figure out the user is logged out and hence the
    // cart data which is already there in localstorage is not valid and hence,
    // initiating object with empty data will show empty cart and mini cart.
    // Clearing the local storage will be taken care by emptyCustomerCart().
    if (cart.uid !== drupalSettings.user.uid && cart.uid > 0) {
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
  const apiUrl = getCartApiUrl();

  return Axios.get(apiUrl)
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
      Drupal.logJavascriptError('Failed to get cart.', error);
    });
};
