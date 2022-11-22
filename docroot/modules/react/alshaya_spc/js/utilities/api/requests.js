import Cookies from 'js-cookie';
import {
  cartAvailableInStorage,
  redirectToCart,
} from '../get_cart';
import dispatchCustomEvent from '../events';
import validateCartResponse from '../validation_util';
import { isEgiftCardEnabled } from '../../../../js/utilities/util';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getTopUpQuote } from '../../../../js/utilities/egiftCardHelper';

export const fetchClicknCollectStores = (args) => {
  const { coords, cartId, cncStoresLimit } = args;
  if (cartId === undefined) {
    return new Promise((resolve) => resolve(null));
  }

  return window.commerceBackend.fetchClickNCollectStores(coords, cncStoresLimit);
};

export const fetchCartData = async () => {
  // If user is requesting for cart data and it's for Topup card then check if
  // page is other than /checkout. If 'YES' then remove the topup quote from
  // localstorage.
  if (isEgiftCardEnabled()
    && drupalSettings.path.currentPath !== 'checkout'
    && getTopUpQuote()) {
    Drupal.removeItemFromLocalStorage('topupQuote');
  }
  // First thing, load the guest cart id for association later if required.
  if (typeof window.commerceBackend.associateCartToCustomer !== 'undefined') {
    await window.commerceBackend.associateCartToCustomer('cart');
  }

  if (window.commerceBackend.isAnonymousUserWithoutCart()) {
    window.commerceBackend.removeCartDataFromStorage();
    return null;
  }

  // If reset_cart_storage cookie is set then remove cart from storage.
  if (Cookies.get('reset_cart_storage')) {
    window.commerceBackend.removeCartDataFromStorage();
    Cookies.remove('reset_cart_storage');
  }

  // Check if cart available in storage.
  let cart = cartAvailableInStorage();

  if (cart === 'empty') {
    return null;
  }

  if (!cart) {
    return window.commerceBackend.restoreCart().then((response) => {
      if (response === null) {
        return null;
      }

      if (response === null || typeof response !== 'object') {
        redirectToCart();
        return null;
      }

      if (!validateCartResponse(response.data)) {
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

      // If cart from stale cache.
      if (response.data.stale_cart !== undefined && response.data.stale_cart === true) {
        // Dispatch event to show error message to the customer.
        dispatchCustomEvent('spcCartMessageUpdate', {
          type: 'error',
          message: drupalSettings.globalErrorMessage,
        });
      }

      return response.data;
    }).catch((error) => {
      if (error.message === 'Request aborted') {
        return error.message;
      }

      // Processing of error here.
      Drupal.logJavascriptError('Failed to restore cart.', error, GTM_CONSTANTS.CART_ERRORS);

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

  return window.commerceBackend.getCart()
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
      Drupal.logJavascriptError('Failed to get cart.', error, GTM_CONSTANTS.CART_ERRORS);
    });
};

export const fetchCartDataForCheckout = async () => {
  // First thing, load the guest cart id for association later if required.
  if (typeof window.commerceBackend.associateCartToCustomer !== 'undefined') {
    await window.commerceBackend.associateCartToCustomer('checkout');
  }

  // Store cart int id before deleting cart data.
  const currentCart = await window.commerceBackend.getCart();
  if (hasValue(currentCart)) {
    globalThis.cartIdInt = currentCart.data.cart_id_int;
  }

  // Remove cart data from storage every-time we land on checkout page.
  window.commerceBackend.removeCartDataFromStorage();

  // Quick check for guest user if cart available.
  if (window.commerceBackend.isAnonymousUserWithoutCart()) {
    return null;
  }

  // Prepare api url.
  return window.commerceBackend.getCartForCheckout()
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
      Drupal.logJavascriptError('Failed to get cart for checkout.', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    });
};
