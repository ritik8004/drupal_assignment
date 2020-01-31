import { getInfoFromStorage, addInfoInStorage } from "./storage";
import Axios from "axios";

export function checkCartCustomer() {
  var cart_data = getInfoFromStorage();

  if (cart_data.uid !== window.drupalSettings.user.uid) {
    cart_data.uid = window.drupalSettings.user.uid;
    associateCart(cart_data);
  }
}

const associateCart = (cart_data) => {
  let url = window.drupalSettings.alshaya_spc.middleware_url + '/associate-cart';
  Axios.get(url)
  .then(response => {
    if (response.data) {
      console.log(response.data);
      // addInfoInStorage(cart_data);
    }
  })
  .catch(error => {
  // Processing of error here.
  });

}
