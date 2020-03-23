import axios from 'axios';

import {
  addShippingInCart,
  removeFullScreenLoader,
  triggerCheckoutEvent, validateInfo,
} from './checkout_util';
import {
  validateAddressFields,
  prepareAddressDataFromForm,
} from './checkout_address_process';
import {
  getInfoFromStorage,
} from './storage';
import getStringMessage from './strings';
import {
  dispatchCustomEvent,
} from './events';

/**
 * Get the address list of the current logged in user.
 */
export const getUserAddressList = function () {
  return axios.get('user-address-list')
    .then((response) => response.data)
    .catch((error) => {
      // Processing of error here.
      console.error(error);
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
      console.error(error);
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
    .then(
      (response) => response.data,
      (error) => ({
        error: true,
        error_message: getStringMessage('global_error'),
      }),
    )
    .catch((error) => {
      // Processing of error here.
      console.error(error);
    });
};

/**
 * Get label of area based on location id.
 *
 * @param {*} isParent
 * @param {*} id
 */
export const gerAreaLabelById = (isParent, id) => {
  let label = '';
  const idAttibute = isParent ? 'data-parent-id' : 'data-id';
  const labelAttribute = isParent ? 'data-parent-label' : 'data-label';
  const area = document.querySelectorAll(`[${idAttibute}="${id}"]`);
  if (area.length > 0) {
    label = area[0].getAttribute(labelAttribute);
  }

  return label;
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
  const validationRequest = validateInfo({ mobile });
  if (validationRequest instanceof Promise) {
    validationRequest.then((result) => {
      if (result.status === 200 && result.data.status) {
        // If not valid mobile number.
        if (result.data.mobile === false) {
          // Removing loader in case validation fail.
          removeFullScreenLoader();
          document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
          document.getElementById('mobile-error').classList.add('error');
        } else {
          // If valid mobile number, remove error message.
          document.getElementById('mobile-error').innerHTML = '';
          document.getElementById('mobile-error').classList.remove('error');

          // Prepare form data.
          const formData = {};
          const name = target.fullname.value.trim();
          formData.address = {
            given_name: name.split(' ')[0],
            family_name: name.substring(name.indexOf(' ') + 1),
            city: gerAreaLabelById(false, target.administrative_area.value),
            address_id: target.address_id.value,
          };

          formData.mobile = mobile;

          // Getting dynamic fields data.
          Object.entries(drupalSettings.address_fields).forEach(([key]) => {
            formData.address[key] = target[key].value;
          });

          // Add/update user address.
          const addressList = addEditUserAddress(formData);
          if (addressList instanceof Promise) {
            addressList.then((list) => {
              // If any error.
              if (list.error === true) {
                // Remove loader.
                removeFullScreenLoader();
                const eventData = {
                  type: 'error',
                  message: list.error_message,
                };
                dispatchCustomEvent('addressPopUpError', eventData);
                return;
              }

              const firstKey = Object.keys(list.data)[0];
              target.email = {
                value: list.data[firstKey].email,
              };

              // Prepare address data for shipping update.
              const data = prepareAddressDataFromForm(target);
              data.static.customer_address_id = list.data[firstKey].address_mdc_id;
              data.static.customer_id = list.data[firstKey].customer_id;

              // Add shipping info in cart.
              const cartInfo = addShippingInCart('update shipping', data);
              if (cartInfo instanceof Promise) {
                cartInfo.then((cartResult) => {
                  // Remove loader.
                  removeFullScreenLoader();

                  let cartData = {};

                  // If error, no need to process.
                  if (cartResult.error !== undefined) {
                    dispatchCustomEvent('addressPopUpError', {
                      type: 'error',
                      message: cartResult.error_message,
                    });
                    return;
                  } else {
                    cartData = getInfoFromStorage();
                    cartData.cart = cartResult;
                  }

                  // Refresh cart.
                  triggerCheckoutEvent('refreshCartOnAddress', cartData);

                  // Close the addresslist popup.
                  triggerCheckoutEvent('closeAddressListPopup', true);
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
 * @param {*} isParent
 * @param {*} parentId
 */
export const getAreasList = (isParent, parentId) => {
  const areasList = [];
  const areas = document.querySelectorAll('[data-list=areas-list]');
  if (areas.length > 0) {
    const idAttribute = isParent ? 'data-parent-id' : 'data-id';
    const labelAttribute = isParent ? 'data-parent-label' : 'data-label';
    for (let i = 0; i < areas.length; i++) {
      const id = areas[i].getAttribute(idAttribute);
      // If we need to fetch areas of a given parent.
      if (parentId !== null) {
        const parentIdVal = areas[i].getAttribute('data-parent-id');
        // If item's parent id not matches.
        if (parseInt(parentId, 10) !== parseInt(parentIdVal, 10)) {
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
 * Get the parent areas of a given area.
 *
 * If we want to check via label.
 *
 * @param {*} isLabel
 * @param {*} areaId
 */
export const getAreaParentId = (isLabel, areaId) => {
  let parentArea = null;
  const idAttibute = isLabel ? 'data-label' : 'data-id';
  const area = document.querySelectorAll(`[${idAttibute}="${areaId}"]`);
  // If there are parents available.
  if (area.length > 0) {
    parentArea = [];
    for (let i = 0; i < area.length; i++) {
      const city = {
        label: area[i].getAttribute('data-parent-label'),
        id: area[i].getAttribute('data-parent-id'),
      };
      parentArea.push(city);
    }
  }

  return parentArea;
};
