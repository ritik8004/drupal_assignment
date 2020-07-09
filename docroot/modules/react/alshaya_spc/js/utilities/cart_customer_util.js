import Axios from 'axios';
import { getInfoFromStorage, addInfoInStorage, removeCartFromStorage } from './storage';
import i18nMiddleWareUrl from './i18n_url';
import { GTM_CART_ERRORS } from './constants';

const associateCart = () => {
  const url = i18nMiddleWareUrl('cart/associate');
  return Axios.get(url)
    .then((response) => {
      if (response.data) {
        addInfoInStorage({ cart: response.data });
      }
    })
    .catch((error) => {
      // Processing of error here.
      Drupal.logJavascriptError('cart/associate', error, GTM_CART_ERRORS);
    });
};

/**
 * Empty cart.
 */
const emptyCustomerCart = () => {
  removeCartFromStorage();

  const emptyCart = {
    cart_id: null,
    cart_total: null,
    items_qty: null,
    items: [],
  };

  // Triggering event to notify react component.
  const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => emptyCart } });
  document.dispatchEvent(refreshCartEvent);

  const refreshMiniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => emptyCart } });
  document.dispatchEvent(refreshMiniCartEvent);
};

export async function checkCartCustomer(cartData = null) {
  let cartDataVal = cartData;
  if (!(cartDataVal) || cartDataVal.cart_id === undefined) {
    const cartJson = getInfoFromStorage();
    cartDataVal = cartJson.cart;
  }

  if (cartDataVal.cart_id === null) {
    return false;
  }

  // If the cart user and drupal user does not match.
  if (cartDataVal.uid !== window.drupalSettings.user.uid) {
    if (!cartDataVal.uid) {
      cartDataVal.uid = window.drupalSettings.user.uid;
      if (window.drupalSettings.user.uid === 0) {
        addInfoInStorage({ cart: cartDataVal });
        return false;
      }

      await associateCart();
      return true;
    }
    emptyCustomerCart();
    return false;
  }
  return false;
}

export const extractFirstAndLastName = (name) => {
  const splitName = name.split(' ');
  // Check if the name has space in string.
  // if user has enters only firstname lastname should be empty.
  return {
    firstname: splitName[0],
    lastname: splitName[1] ? name.substring(name.indexOf(' ') + 1) : '',
  };
};

export const makeFullName = (fname = '', lname = '') => {
  if (fname.trim() === '' || lname.trim() === '') {
    return fname.trim().concat(lname.trim());
  }

  return `${fname} ${lname}`;
};
