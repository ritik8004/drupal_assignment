import axios from 'axios';

import {
  addShippingInCart,
  removeFullScreenLoader,
} from './checkout_util';
import {
  validateAddressFields,
  prepareAddressDataFromForm
} from './checkout_address_process';

/**
 * Get the address list of the current logged in user.
 */
export const getUserAddressList = function () {
  return axios.get('user-address-list')
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
    });
};

/**
 * Update default address for the user.
 *
 * @param {*} address_id
 */
export const updateUserDefaultAddress = function (address_id) {
  return axios.post('set-default-address', {
    address_id,
  })
    .then((response) => response)
    .catch((error) => {
      // Processing of error here.
    });
};

/**
 * Add / Edit address for customer.
 *
 * @param {*} address
 */
export const addEditUserAddress = function (address) {
  return axios.post('add-edit-address', {
    address,
  })
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
    });
};

/**
 * Prepare data for customer address add/edit and save.
 *
 * @param {*} val
 */
export const addEditAddressToCustomer = (e) => {
  const notValidAddress = validateAddressFields(e, false);
  // If address form is not valid.
  if (notValidAddress) {
    // Removing loader in case validation fail.
    removeFullScreenLoader();
    return;
  }

  const target = e.target.elements;
  // Validate mobile number.
  const mobile = e.target.elements.mobile.value.trim();
  const mobile_valid = axios.get(`verify-mobile/${mobile}`);
  if (mobile_valid instanceof Promise) {
    mobile_valid.then((result) => {
      if (result.status === 200) {
        // If not valid mobile number.
        if (result.data.status === false) {
          // Removing loader in case validation fail.
          removeFullScreenLoader();
          document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
          document.getElementById('mobile-error').classList.add('error');
        } else {
          // If valid mobile number, remove error message.
          document.getElementById('mobile-error').innerHTML = '';
          document.getElementById('mobile-error').classList.remove('error');

          // Prepare form data.
          const form_data = {};
          const name = target.fullname.value.trim();
          form_data.address = {
            given_name: name.split(' ')[0],
            family_name: name.substring(name.indexOf(' ') + 1),
            city: gerAreaLabelById(false, target['administrative_area'].value),
            address_id: target.address_id.value,
          };

          form_data.mobile = mobile;

          // Getting dynamic fields data.
          Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
            form_data.address[key] = target[key].value;
          });

          // Add/update user address.
          const addressList = addEditUserAddress(form_data);
          if (addressList instanceof Promise) {
            addressList.then((list) => {
              // If any error.
              if (list.status === false) {
                // Remove loader.
                removeFullScreenLoader();
                return;
              }

              const firstKey = Object.keys(list.data)[0];
              target['email'] = {
                'value': list.data[firstKey]['email']
              };

              // Prepare address data for shipping update.
              let data = prepareAddressDataFromForm(target);
              data['static']['customer_address_id'] = list.data[firstKey].address_mdc_id;
              data['static']['customer_id'] = list.data[firstKey].customer_id;

              // Add shipping info in cart.
              const cart_info = addShippingInCart('update shipping', data);
              if (cart_info instanceof Promise) {
                cart_info.then((cart_result) => {
                  // Remove loader.
                  removeFullScreenLoader();
                  // If error, no need to process.
                  if (cart_result.error !== undefined) {
                    return;
                  }

                  const cart_data = {
                    cart: cart_result,
                  };

                  const event = new CustomEvent('refreshCartOnAddress', {
                    bubbles: true,
                    detail: {
                      data: () => cart_data,
                    },
                  });
                  document.dispatchEvent(event);

                  // Close the addresslist popup.
                  const ee = new CustomEvent('closeAddressListPopup', {
                    bubbles: true,
                    detail: {
                      close: true,
                    },
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
};

/**
 * Get Areas list.
 *
 * @param {*} is_parent
 * @param {*} parent_id
 */
export const getAreasList = (is_parent, parent_id) => {
  let areasList = new Array();
  let areas = document.querySelectorAll('[data-list=areas-list]');
  if (areas.length > 0) {
    let idAttribute = is_parent ? 'data-parent-id' : 'data-id';
    let labelAttribute = is_parent ? 'data-parent-label' : 'data-label';
    for (let i = 0; i < areas.length; i++) {
      let id = areas[i].getAttribute(idAttribute);
      // If we need to fetch areas of a given parent.
      if (parent_id !== null) {
        let parentId = areas[i].getAttribute('data-parent-id');
        // If item's parent id not matches.
        if (parent_id != parentId) {
          continue;
        }
      }

      areasList[id] = {
        value: id,
        label: areas[i].getAttribute(labelAttribute),
      };
    }
  }

  return areasList;
};

/**
 * Get label of area based on location id.
 *
 * @param {*} is_parent
 * @param {*} id
 */
export const gerAreaLabelById = (is_parent, id) => {
  let label = '';
  let idAttibute = is_parent ? 'data-parent-id' : 'data-id';
  let labelAttribute = is_parent ? 'data-parent-label' : 'data-label';
  const area = document.querySelectorAll('[' + idAttibute + '="' + id + '"]');
  if (area.length > 0) {
    label = area[0].getAttribute(labelAttribute);
  }

  return label;
};
