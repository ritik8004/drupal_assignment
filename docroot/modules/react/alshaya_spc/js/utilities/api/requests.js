import Axios from 'axios';
import Cookies from 'js-cookie';
import {
  cartAvailableInStorage,
  getCartApiUrl,
  getCartForCheckoutApiUrl,
  redirectToCart,
} from '../get_cart';
import { restoreCartApiUrl } from '../update_cart';
import {
  removeCartFromStorage,
} from '../storage';
import i18nMiddleWareUrl from '../i18n_url';

export const fetchClicknCollectStores = (args) => {
  const { coords, cartId } = args;
  if (cartId === undefined) {
    return new Promise((resolve) => resolve(null));
  }

  const GET_STORE_URL = i18nMiddleWareUrl(
    `cart/stores/${coords.lat}/${coords.lng}`,
  );
  return Axios.get(GET_STORE_URL);
};

export const fetchCartData = () => {
  // If session cookie not exists, no need to process/check.
  // @TODO: Remove Cookies.get('Drupal.visitor.acq_cart_id') check when we
  // uninstall alshaya_acm module.
  if (drupalSettings.user.uid === 0
    && !Cookies.get('PHPSESSID')
    && !Cookies.get('Drupal.visitor.acq_cart_id')
  ) {
    removeCartFromStorage();
    return null;
  }

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
        return null;
      }

      if (response.data.error) {
        redirectToCart();
        return null;
      }

      if (Object.values(response.data.items).length === 0) {
        redirectToCart();
        return null;
      }

      return response.data;
    }).catch((error) => {
      if (error.message === 'Request aborted') {
        return error.message;
      }

      // Processing of error here.
      Drupal.logJavascriptError('Failed to restore cart.', error);

      redirectToCart();
      return null;
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

export const fetchCartDataForCheckout = () => {
  // Remove cart data from storage every-time we land on checkout page.
  removeCartFromStorage();

  // If session cookie not exists, no need to process/check.
  if (drupalSettings.user.uid === 0 && !Cookies.get('PHPSESSID')) {
    return null;
  }

  // Prepare api url.
  const apiUrl = getCartForCheckoutApiUrl();

  return Axios.get(apiUrl)
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
      Drupal.logJavascriptError('Failed to get cart for checkout.', error);
    });
};
