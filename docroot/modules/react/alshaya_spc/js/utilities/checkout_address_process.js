import axios from 'axios';
import { getShippingMethods } from './checkout_util';
import { addShippingInCart } from './checkout_util';

/**
 * Process the data got from address form submission.
 *
 * @param {*} e
 * @param {*} cart
 */
export const checkoutAddressProcess = function (e, cart) {
  // Add loading class.
  document.getElementById('save-address').classList.add('loading');
  let form_data = {};
  form_data['static'] = {
    'firstname': e.target.elements.fname.value,
    'lastname': e.target.elements.lname.value,
    'email': e.target.elements.email.value,
    'city': 'Dummy Value',
    'telephone': e.target.elements.mobile.value,
    'country_id': window.drupalSettings.country_code
  };

  // Getting dynamic fields data.
  Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
    form_data[field.key] = e.target.elements[key].value
  });

  const mobileValidationRequest = axios.get('verify-mobile/' + e.target.elements.mobile.value);
  const customerValidationReuest = axios.get(window.drupalSettings.alshaya_spc.middleware_url + '/customer/' + e.target.elements.email.value);

  // API call to validate mobile number and email address.
  return axios.all([mobileValidationRequest, customerValidationReuest]).then(axios.spread((...responses) => {
    const mobileResponse = responses[0].data;
    const customerResponse = responses[1].data;

    // Flag to determine if there any error.
    let isError = false;

    // If invalid mobile number.
    if (mobileResponse['status'] === false) {
      document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
      document.getElementById('mobile-error').classList.add('error');
      isError = true;
    }
    else {
      // Remove error class and any error message.
      document.getElementById('mobile-error').innerHTML = '';
      document.getElementById('mobile-error').classList.remove('error');
      isError = false;
    }

    // If customer already exists.
    if (cart['shipping_address'] === null
      && customerResponse['exists'] === true) {
      document.getElementById('email-error').innerHTML = Drupal.t('Customer already exists.');
      document.getElementById('email-error').classList.add('error');
      isError = true;
    }
    else if ((cart['shipping_address'] !== undefined && cart['shipping_address'] !== null)
      && cart['shipping_address']['email'] !== form_data['static']['email']
      && customerResponse['exists'] === true) {
      document.getElementById('email-error').innerHTML = Drupal.t('Customer already exists.');
      document.getElementById('email-error').classList.add('error');
      isError = true;
    }
    else {
      // Remove error class and any error message.
      document.getElementById('email-error').innerHTML = '';
      document.getElementById('email-error').classList.remove('error');
    }

    if (isError) {
      // Remove loading class.
      document.getElementById('save-address').classList.remove('loading');
      return false;
    }
    else {
      // Get shipping methods based on address info.
      let shipping_methods = getShippingMethods(cart.cart_id, form_data);
      if (shipping_methods instanceof Promise) {
        shipping_methods.then((shipping) => {
          // Add shipping method in cart.
          form_data['carrier_info'] = {
            'code': shipping[0].carrier_code,
            'method': shipping[0].method_code
          };
          var cart_info = addShippingInCart('update shipping', form_data);
          if (cart_info instanceof Promise) {
            cart_info.then((cart_result) => {
              let cart_data = {
                'cart': cart_result,
                'delivery_type': cart_result.delivery_method,
                'shipping_methods': shipping,
                'address': form_data
              }
              var event = new CustomEvent('refreshCartOnAddress', {
                bubbles: true,
                detail: {
                  data: () => cart_data
                }
              });
              document.dispatchEvent(event);
            });
          }
        });
      }
    }
  })).catch(errors => {
    // react on errors.
  })

}
