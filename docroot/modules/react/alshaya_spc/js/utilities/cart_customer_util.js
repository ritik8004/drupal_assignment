import { getInfoFromStorage, addInfoInStorage, removeCartFromStorage } from "./storage";
import Axios from "axios";

export function checkCartCustomer(cart_data = null) {
  if (cart_data && typeof cart_data.cart_id !== 'undefined') {
    cart_data = cart_data;
  }
  else {
    const cart_json = getInfoFromStorage();
    cart_data = cart_json.cart;
  }
  // If the cart user and drupal user does not match.
  if (cart_data.uid !== window.drupalSettings.user.uid) {
    if (!cart_data.uid) {
      cart_data.uid = window.drupalSettings.user.uid;
      if (window.drupalSettings.user.uid === 0) {
        addInfoInStorage({ cart: cart_data });
      }
      else {
        associateCart(cart_data);
      }
    }
    else {
      emptyCustomerCart();
    }
  }
}

const associateCart = (cart_data) => {
  let url = window.drupalSettings.alshaya_spc.middleware_url + '/associate-cart';
  Axios.get(url)
    .then(response => {
      if (response.data) {
        cart_data.uid = response.data.uid;
        addInfoInStorage({ cart: cart_data });
      }
    })
    .catch(error => {
      // Processing of error here.
    });
}

/**
 * Empty cart.
 */
const emptyCustomerCart = () => {
  removeCartFromStorage();

  let empty_cart = {
    cart_id: null,
    cart_total: null,
    items_qty: null,
    items: []
  }

  // Triggering event to notify react component.
  var event = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => empty_cart } });
  document.dispatchEvent(event);

  var event = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => empty_cart } });
  document.dispatchEvent(event);
}
