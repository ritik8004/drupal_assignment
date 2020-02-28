import axios from 'axios';

import {
  addShippingInCart,
  removeFullScreenLoader
} from './checkout_util';
import {
  validateAddressFields
} from './checkout_address_process';

/**
 * Get the address list of the current logged in user.
 */
export const getUserAddressList = function () {
  return axios.get('user-address-list')
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });
}

/**
 * Update default address for the user.
 *
 * @param {*} address_id
 */
export const updateUserDefaultAddress = function (address_id) {
  return axios.post('set-default-address', {
      'address_id': address_id
    })
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });
}

/**
 * Deletes address for the user.
 *
 * @param {*} address_id
 */
export const deleteUserAddress = function (address_id) {
  return axios.post('delete-address', {
      'address_id': address_id
    })
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });
}

/**
 * Add / Edit address for customer.
 *
 * @param {*} address
 */
export const addEditUserAddress = function (address) {
  return axios.post('add-edit-address', {
      'address': address
    })
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });
}

/**
 * Prepare data for customer address add/edit and save.
 *
 * @param {*} val
 */
export const addEditAddressToCustomer = (e) => {
  let notValidAddress = validateAddressFields(e, false);
  // If address form is not valid.
  if (notValidAddress) {
    // Removing loader in case validation fail.
    removeFullScreenLoader();
    return;
  }

  let target = e.target.elements;
  // Validate mobile number.
  let mobile = e.target.elements.mobile.value.trim();
  let mobile_valid = axios.get('verify-mobile/' + mobile);
  if (mobile_valid instanceof Promise) {
    mobile_valid.then(result => {
      if (result.status === 200) {
        // If not valid mobile number.
        if (result.data.status === false) {
          // Removing loader in case validation fail.
          removeFullScreenLoader();
          document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
          document.getElementById('mobile-error').classList.add('error');
        }
        else {
          // If valid mobile number, remove error message.
          document.getElementById('mobile-error').innerHTML = '';
          document.getElementById('mobile-error').classList.remove('error');

          // Prepare form data.
          let form_data = {};
          let name = target.fullname.value.trim();
          form_data['address'] = {
            'given_name': name.split(' ')[0],
            'family_name': name.substring(name.indexOf(' ') + 1),
            'city': 'Dummy Value',
            'address_id': target.address_id.value
          };

          form_data['mobile'] = mobile;

          // Getting dynamic fields data.
          Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
            form_data['address'][key] = target[key].value
          });

          // Add/update user address.
          let addressList = addEditUserAddress(form_data);
          if (addressList instanceof Promise) {
            addressList.then((list) => {
              let firstKey = Object.keys(list.data)[0]
              let data = {
                'address_id': list.data[firstKey]['address_mdc_id'],
                'country_id': window.drupalSettings.country_code
              };

              // Add shipping info in cart.
              var cart_info = addShippingInCart('update shipping', data);
              if (cart_info instanceof Promise) {
                cart_info.then((cart_result) => {
                  // If cart id not available, no need to process.
                  if (cart_result.cart_id === null) {
                    return;
                  }

                  let cart_data = {
                    'cart': cart_result
                  }

                  // Removing loader when process finishes.
                  removeFullScreenLoader();

                  var event = new CustomEvent('refreshCartOnAddress', {
                    bubbles: true,
                    detail: {
                      data: () => cart_data
                    }
                  });
                  document.dispatchEvent(event);

                  // Close the addresslist popup.
                  let ee = new CustomEvent('closeAddressListPopup', {
                    bubbles: true,
                    detail: {
                      close: true
                    }
                  });
                  document.dispatchEvent(ee);
                });
              }
            });
          }

        }
      }
    });
  }
}
