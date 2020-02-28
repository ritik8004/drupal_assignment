import axios from 'axios';
import {
  addShippingInCart,
  removeFullScreenLoader
} from './checkout_util';

/**
 * Process the data got from address form submission.
 *
 * @param {*} e
 * @param {*} cart
 */
export const checkoutAddressProcess = function (e, cart) {
  // Add loading class.
  document.getElementById('save-address').classList.add('loading');
  let notValidAddress = validateAddressFields(e, true);
  // If address form is not valid.
  if (notValidAddress) {
    // Remove the loader.
    removeFullScreenLoader();
    return;
  }

  // Get form data.
  let form_data = prepareAddressData(e);

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
      removeFullScreenLoader();
      // Remove loading class.
      document.getElementById('save-address').classList.remove('loading');
      return false;
    }
    else {
      // Get shipping methods based on address info.
      var cart_info = addShippingInCart('update shipping', form_data);
      if (cart_info instanceof Promise) {
        cart_info.then((cart_result) => {
          let cart_data = {
            'cart': cart_result
          }

          // Remove the loader.
          removeFullScreenLoader();

          var event = new CustomEvent('refreshCartOnAddress', {
            bubbles: true,
            detail: {
              data: () => cart_data
            }
          });
          document.dispatchEvent(event);
        });
      }
    }
  })).catch(errors => {
    // react on errors.
  })

}

/**
 * Validate address fields.
 */
export const validateAddressFields = (e, validateEmail) => {
  let isError = false;
  let name = e.target.elements.fullname.value.trim();
  let splited_name = name.split(' ');
  if (name.length === 0 || splited_name.length === 1) {
    document.getElementById('fullname-error').innerHTML = Drupal.t('Please enter your full name.');
    document.getElementById('fullname-error').classList.add('error');
    isError = true;
  }
  else {
    document.getElementById('fullname-error').innerHTML = '';
    document.getElementById('fullname-error').classList.remove('error');
  }

  let mobile = e.target.elements.mobile.value.trim();
  if (mobile.length === 0) {
    document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter mobile number.');
    document.getElementById('mobile-error').classList.add('error');
    isError = true;
  }
  else {
    document.getElementById('mobile-error').innerHTML = '';
    document.getElementById('mobile-error').classList.remove('error');
  }

  // If email validation needs to be done.
  if (validateEmail) {
    let email = e.target.elements.email.value.trim();
    if (email.length === 0) {
      document.getElementById('email-error').innerHTML = Drupal.t('Please enter email.');
      document.getElementById('email-error').classList.add('error');
      isError = true;
    } else {
      document.getElementById('email-error').innerHTML = '';
      document.getElementById('email-error').classList.remove('error');
    }
  }

  // Iterate over address fields.
  Object.entries(window.drupalSettings.address_fields).forEach(
    ([key, field]) => {
      if (field.required === true) {
        let add_field = e.target.elements[key].value.trim();
        if (add_field.length === 0) {
          document.getElementById(key + '-error').innerHTML = Drupal.t('Please enter @label.', {'@label': field.label});
          document.getElementById(key + '-error').classList.add('error');
          isError = true;
        }
        else {
          document.getElementById(key + '-error').innerHTML = '';
          document.getElementById(key + '-error').classList.remove('error');
        }
      }
    }
  );

  return isError;
}

/**
 * Prepare form data.
 *
 * @param {*} e
 */
export const prepareAddressData = (e) => {
  let form_data = {};

  let name = e.target.elements.fullname.value.trim();
  form_data['static'] = {
    'firstname': name.split(' ')[0],
    'lastname': name.substring(name.indexOf(' ') + 1),
    'email': e.target.elements.email.value,
    'city': 'Dummy Value',
    'telephone': e.target.elements.mobile.value,
    'country_id': window.drupalSettings.country_code
  };

  // Getting dynamic fields data.
  Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
    form_data[field.key] = e.target.elements[key].value
  });

  return form_data;
}

/**
 * Get the address popup class.
 */
export const getAddressPopupClassName = () => {
  return window.drupalSettings.user.uid > 0 ?
    "spc-address-list-member" :
    "spc-address-form-guest";
};
