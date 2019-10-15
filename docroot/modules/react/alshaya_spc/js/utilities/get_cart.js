import axios from 'axios';

export const cartAvailableInStorage = function () {
  // Get data from local storage.
  var cart_data = localStorage.getItem('cart_data');

  // If data is not available in storage, we flag it to check/fetch from api.
  if (!cart_data) {
    return false;
  }

  // 1m time for expire.
  // @Todo: Make this 10m (configurable from BE).
  var expire_time = 1*60*1000;
  var current_time = new Date().getTime();
  var cart_data = JSON.parse(cart_data);

  // If data/cart is expired.
  if ((current_time - cart_data.last_update) > expire_time) {
    return false;
  }

  return cart_data;
}

export const fetchCartData = function () {
  // Check if cart available in storage.
  var cart = cartAvailableInStorage();
  if (cart !== false) {
    return Promise.resolve(cart);
  }

  return null;

  // Prepare api url.
  var api_url = window.drupalSettings.alshaya_spc.middleware_url + '/cart/' + cart.cart_id;

  return axios.get(api_url)
    .then(response => {
      return response.data
  })
  .catch(error => {
    // Processing of error here.
  });

}
