import axios from 'axios';

/**
 * Get shipping methods.
 *
 * @param cart_id
 * @param data
 * @returns {boolean}
 */
export const getShippingMethods = function (cart_id, data) {
  var middleware_url = window.drupalSettings.alshaya_spc.middleware_url;
 
  return axios.post(middleware_url + '/cart/shipping-methods' , {
    data: data,
    cart_id: cart_id 
  })
    .then((response) => {
      return response.data;
  }, (error) => {
    // Processing of error here.
  });
}

/**
 * Get payment methods.
 *
 * @param cart_id
 * @returns {boolean}
 */
export const getPaymentMethods = function (cart_id) {
  var middleware_url = window.drupalSettings.alshaya_spc.middleware_url;
 
  return axios.get(middleware_url + '/cart/' + cart_id + '/payment-methods')
    .then((response) => {
      return response.data;
  }, (error) => {
    // Processing of error here.
  });
}


