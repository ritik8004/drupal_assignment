import axios from 'axios';

export const getCartCookie = function() {
  // If cart id cookie is not set, don't process further.
  // @Todo: check if we will be using the acq_cart_id or something else.
  // @Todo: need to check what will happen for logged in user when cookie is not set.
  var cookie = document.cookie.match('(^|;) ?' + 'Drupal.visitor.acq_cart_id' + '=([^;]*)(;|$)');
  var cart_id = cookie ? cookie[2] : null;

  // No need to process if cookie is not set.
  if (cart_id === null) {
    return false;
  }
}

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
  var cart_cookie = getCartCookie();
  if (cart_cookie === false) {
    return null;
  }

  // Check if cart available in storage.
  var cart = cartAvailableInStorage();
  if (cart !== false) {
    return Promise.resolve(cart);
  }
  // Prepare api url.
  var api_url = window.drupalSettings.mini_cart.base_url + '/' + window.drupalSettings.mini_cart.langcode;

  return axios.get(api_url + '/get-cart')
    .then(response => {
      return response.data
  })
  .catch(error => {
    console.log(error);
  });

}
