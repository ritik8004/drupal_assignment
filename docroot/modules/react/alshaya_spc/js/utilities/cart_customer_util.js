import Axios from 'axios';
import { getInfoFromStorage, addInfoInStorage, removeCartFromStorage } from './storage';
import { i18nMiddleWareUrl } from './i18n_url';

export async function checkCartCustomer(cartData = null) {
  if (!(cartData) || cartData.cart_id === undefined) {
    const cartJson = getInfoFromStorage();
    cartData = cartJson.cart;
  }

  if (cartData.cart_id === null) {
    return false;
  }

  // If the cart user and drupal user does not match.
  if (cartData.uid !== window.drupalSettings.user.uid) {
    if (!cartData.uid) {
      cartData.uid = window.drupalSettings.user.uid;
      if (window.drupalSettings.user.uid === 0) {
        addInfoInStorage({ cart: cartData });
        return false;
      }

      await associateCart(cartData);
      return true;
    }
    emptyCustomerCart();
    return false;
  }
  return false;
}

const associateCart = (cartData) => {
  const url = i18nMiddleWareUrl('associate-cart');
  return Axios.get(url)
    .then((response) => {
      if (response.data) {
        addInfoInStorage({ cart: response.data });
      }
    })
    .catch((error) => {
      // Processing of error here.
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
  const refreshCart = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => emptyCart } });
  document.dispatchEvent(refreshCart);

  const refreshMiniCart = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => emptyCart } });
  document.dispatchEvent(refreshMiniCart);
};

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
