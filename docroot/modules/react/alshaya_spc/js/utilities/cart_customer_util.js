import { getInfoFromStorage, addInfoInStorage } from "./storage";
import Axios from "axios";

export function checkCartCustomer(cart_data = null) {
  cart_data = cart_data !== null ? cart_data : getInfoFromStorage();

  if (cart_data.cart.uid !== window.drupalSettings.user.uid) {
    cart_data.cart.uid = window.drupalSettings.user.uid;
    if (window.drupalSettings.user.uid === 0) {
      addInfoInStorage(cart_data);
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
      cart_data.cart.uid = response.data.uid;
      addInfoInStorage(cart_data);
    }
  })
  .catch(error => {
    // Processing of error here.
  });
}
