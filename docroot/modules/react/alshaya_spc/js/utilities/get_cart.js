import axios from 'axios';

/**
 * Get the middleware get cart endpoint.
 *
 * @returns {string}
 */
export function getCartApiUrl(cart_id) {
  const langcode = window.drupalSettings.path.currentLanguage;
  return window.drupalSettings.alshaya_spc.middleware_url + '/cart/' + cart_id + '?lang=' + langcode;
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

  // If data/cart is expired or cart has different language than
  // currentlt selected language.
  if ((current_time - cart_data.last_update) > expire_time || window.drupalSettings.path.currentLanguage !== cart_data.langcode) {
    return cart_data.cart_id;
  }

  return cart_data;
}

export const fetchCartData = function () {
  // Check if cart available in storage.
  var cart = cartAvailableInStorage();

  if (cart === false) {
    return null;
  }

  // If we get integer, mean we get only cart id and thus we need to fetch
  // fresh cart. If we not get integer, means we get cart object and we can
  // just use and return that.
  if (!Number.isInteger(cart)) {
    return Promise.resolve(cart);
  }

  // Prepare api url.
  var api_url = getCartApiUrl(cart);

  return axios.get(api_url)
    .then(response => {
      return response.data
  })
  .catch(error => {
    // Processing of error here.
  });

}
