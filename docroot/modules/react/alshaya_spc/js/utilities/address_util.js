import axios from 'axios';

import {
  addShippingInCart
} from './checkout_util';

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
 * @param {*} e
 */
export const addEditAddressToCustomer = (e) => {
  let form_data = {};
  form_data['address'] = {
    'given_name': e.target.elements.fname.value,
    'family_name': e.target.elements.lname.value,
    'city': 'Dummy Value',
    'address_id': e.target.elements.address_id.value
  };

  form_data['mobile'] = e.target.elements.mobile.value

  // Getting dynamic fields data.
  Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
    form_data['address'][key] = e.target.elements[key].value
  });

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
          let cart_data = {
            'cart': cart_result
          }
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
              close: () => true
            }
          });
          document.dispatchEvent(ee);
        });
      }
    });
  }
}
