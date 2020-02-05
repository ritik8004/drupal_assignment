import { getInfoFromStorage, addInfoInStorage } from "./storage";
import Axios from "axios";

export function checkCartCustomer(cart_data = null) {
  if (cart_data && typeof cart_data.cart_id !== 'undefined') {
    cart_data = cart_data;
  }
  else {
    const cart_json = getInfoFromStorage();
    cart_data = cart_json.cart;
  }

  if (cart_data.uid !== window.drupalSettings.user.uid) {
    cart_data.uid = window.drupalSettings.user.uid;
    if (window.drupalSettings.user.uid === 0) {
      addInfoInStorage({cart: cart_data});
    }
    else {
      associateCart(cart_data);
    }
  }
}

const associateCart = (cart_data) => {
  let url = window.drupalSettings.alshaya_spc.middleware_url + '/associate-cart';
  Axios.get(url)
  .then(response => {
    if (response.data) {
      cart_data.uid = response.data.uid;
      addInfoInStorage({cart: cart_data});
    }
  })
  .catch(error => {
    // Processing of error here.
  });
}
