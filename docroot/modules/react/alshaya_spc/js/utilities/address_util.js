import axios from 'axios';

import {
  addBillingInCart,
  addShippingInCart, cleanMobileNumber,
  removeFullScreenLoader, showFullScreenLoader,
  validateInfo,
} from './checkout_util';
import {
  getInfoFromStorage,
} from './storage';
import getStringMessage from './strings';
import dispatchCustomEvent from './events';
import { extractFirstAndLastName } from './cart_customer_util';

/**
 * Get the address list of the current logged in user.
 */
export const getUserAddressList = () => axios.get('user-address-list')
  .then((response) => response.data)
  .catch((error) => {
    // Processing of error here.
    Drupal.logJavascriptError('get-user-address-list', error);
  });

/**
 * Update default address for the user.
 *
 * @param {*} addressId
 */
export const updateUserDefaultAddress = (addressId) => axios.post('set-default-address', {
  addressId,
})
  .then((response) => response)
  .catch((error) => {
    // Processing of error here.
    Drupal.logJavascriptError('update-user-default-address', error);
  });

/**
 * Add / Edit address for customer.
 *
 * @param {*} address
 * @param {*} isDefault
 */
export const addEditUserAddress = (address, isDefault) => axios.post('add-edit-address', {
  address,
  isDefault,
})
  .then(
    (response) => response.data,
    () => ({
      error: true,
      error_message: getStringMessage('global_error'),
    }),
  )
  .catch((error) => {
    // Processing of error here.
    Drupal.logJavascriptError('add-edit-user-address', error);
  });

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
 * Prepare address data for updating shipping.
 *
 * @param {*} address
 */
export const prepareAddressDataForShipping = (address) => {
  const data = {};
  data.static = {
    firstname: address.firstname,
    lastname: address.lastname,
    email: address.email,
    city: address.city,
    telephone: address.mobile,
    country_id: drupalSettings.country_code,
  };

  // Getting dynamic fields data.
  Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
    data[field.key] = address[key];
  });

  return data;
};

/**
 * Prepare address data from cart shipping.
 *
 * @param {*} address
 */
export const prepareAddressDataFromCartShipping = (address) => {
  const tempShippingData = address;
  Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
    tempShippingData[key] = address[field.key];
  });
  tempShippingData.mobile = address.telephone;

  // Get prepared address data for shipping address update.
  const data = prepareAddressDataForShipping(tempShippingData);

  // Extra info for logged in user.
  if (drupalSettings.user.uid > 0) {
    data.static.customer_address_id = address.customer_address_id;
    data.static.customer_id = address.customer_id;
  }

  return data;
};

/**
 * Prepare address to post for cnc from cnc shipppings and store.
 *
 * @param {*} address
 * @param {*} store
 */
export const prepareCnCAddressFromCartShipping = (address, store) => {
  const data = {
    static: {
      firstname: address.firstname,
      lastname: address.lastname,
      email: address.email,
      telephone: address.telephone,
      country_id: drupalSettings.country_code,
    },
    shipping_type: 'cnc',
    store: {
      name: store.name,
      code: store.code,
      rnc_available: store.rnc_available,
      cart_address: store.cart_address,
    },
    carrier_info: { ...drupalSettings.map.cnc_shipping },
  };

  return data;
};

/**
 * Prepare address data from form value.
 *
 * @param {*} elements
 */
export const prepareAddressDataFromForm = (elements) => {
  const {
    firstname,
    lastname,
  } = extractFirstAndLastName(elements.fullname.value.trim());

  const address = {
    firstname,
    lastname,
    email: elements.email.value,
    city: gerAreaLabelById(false, elements.administrative_area.value),
    mobile: `+${drupalSettings.country_mobile_code}${cleanMobileNumber(elements.mobile.value)}`,
  };

  // Getting dynamic fields data.
  Object.entries(drupalSettings.address_fields).forEach(([key]) => {
    address[key] = elements[key].value;
  });

  return prepareAddressDataForShipping(address);
};

/**
 * Validate contact information.
 */
export const validateContactInfo = (e, validateEmail) => {
  let isError = false;
  const name = e.target.elements.fullname.value.trim();
  const splitedName = name.split(' ');
  if (name.length === 0 || splitedName.length === 1) {
    document.getElementById('fullname-error').innerHTML = Drupal.t('Please enter your full name.');
    document.getElementById('fullname-error').classList.add('error');
    isError = true;
  } else {
    document.getElementById('fullname-error').innerHTML = '';
    document.getElementById('fullname-error').classList.remove('error');
  }

  const mobile = e.target.elements.mobile.value.trim();
  if (mobile.length === 0) {
    document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter mobile number.');
    document.getElementById('mobile-error').classList.add('error');
    isError = true;
  } else {
    document.getElementById('mobile-error').innerHTML = '';
    document.getElementById('mobile-error').classList.remove('error');
  }

  // If email validation needs to be done.
  if (validateEmail) {
    const email = e.target.elements.email.value.trim();
    if (email.length === 0) {
      document.getElementById('email-error').innerHTML = Drupal.t('Please enter email.');
      document.getElementById('email-error').classList.add('error');
      isError = true;
    } else {
      document.getElementById('email-error').innerHTML = '';
      document.getElementById('email-error').classList.remove('error');
    }
  }
  return isError;
};

/**
 * Validate address fields.
 */
export const validateAddressFields = (e, validateEmail) => {
  let isError = validateContactInfo(e, validateEmail);

  // Iterate over address fields.
  Object.entries(drupalSettings.address_fields).forEach(
    ([key, field]) => {
      if (field.required === true) {
        const addField = e.target.elements[key].value.trim();
        if (addField.length === 0) {
          document.getElementById(`${key}-error`).innerHTML = Drupal.t('Please enter @label.', { '@label': field.label });
          document.getElementById(`${key}-error`).classList.add('error');
          isError = true;
        } else {
          document.getElementById(`${key}-error`).innerHTML = '';
          document.getElementById(`${key}-error`).classList.remove('error');
        }
      }
    },
  );

  return isError;
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
          const addressList = addEditUserAddress(formData, true);
          if (addressList instanceof Promise) {
            addressList.then((list) => {
              // If any error.
              if (list.error === true) {
                // Remove loader.
                removeFullScreenLoader();
                dispatchCustomEvent('addressPopUpError', {
                  type: 'error',
                  message: list.error_message,
                });
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
                  }

                  cartData = getInfoFromStorage();
                  cartData.cart = cartResult;

                  // Refresh cart.
                  dispatchCustomEvent('refreshCartOnAddress', cartData);

                  // Close the addresslist popup.
                  dispatchCustomEvent('closeAddressListPopup', true);
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
  const selector = (parentId !== null)
    ? `[data-list="areas-list"][data-parent-id="${parentId}"]`
    : '[data-list="areas-list"]';

  const areas = document.querySelectorAll(selector);
  if (areas.length > 0) {
    const idAttribute = isParent ? 'data-parent-id' : 'data-id';
    const labelAttribute = isParent ? 'data-parent-label' : 'data-label';
    for (let i = 0; i < areas.length; i++) {
      const id = areas[i].getAttribute(idAttribute);
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

/**
 * Process the data got from address form submission.
 *
 * @param {*} e
 */
export const checkoutAddressProcess = (e) => {
  const notValidAddress = validateAddressFields(e, true);
  // If address form is not valid.
  if (notValidAddress) {
    // Remove the loader.
    removeFullScreenLoader();
    return;
  }

  // Get Prepare address.
  const formData = prepareAddressDataFromForm(e.target.elements);

  const validationData = {
    mobile: e.target.elements.mobile.value,
  };
  const targetElementEmail = e.target.elements.email;
  if (targetElementEmail !== undefined && targetElementEmail.value.toString().length > 0) {
    validationData.email = e.target.elements.email.value;
  }

  const validationRequest = validateInfo(validationData);
  validationRequest.then((response) => {
    if (!response || response.data.status === undefined || !response.data.status) {
      // API Call failed.
      // @TODO: Handle error.
      return false;
    }

    // Flag to determine if there any error.
    let isError = false;

    // If invalid mobile number.
    if (response.data.mobile === false) {
      document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
      document.getElementById('mobile-error').classList.add('error');
      isError = true;
    } else {
      // Remove error class and any error message.
      document.getElementById('mobile-error').innerHTML = '';
      document.getElementById('mobile-error').classList.remove('error');
    }

    // Do the processing only if we did email validation.
    if (response.data.email !== undefined) {
      if (response.data.email === 'invalid') {
        document.getElementById('email-error').innerHTML = Drupal.t('The email address %mail is not valid.', { '%mail': validationData.email });
        document.getElementById('email-error').classList.add('error');
        isError = true;
      } else if (response.data.email === 'exists') {
        document.getElementById('email-error').innerHTML = Drupal.t('Customer already exists.');
        document.getElementById('email-error').classList.add('error');
        isError = true;
      } else {
        // Remove error class and any error message.
        document.getElementById('email-error').innerHTML = '';
        document.getElementById('email-error').classList.remove('error');
      }
    }

    if (isError) {
      removeFullScreenLoader();
      // Remove loading class.
      document.getElementById('save-address').classList.remove('loading');
      return false;
    }

    // Get shipping methods based on address info.
    const cartInfo = addShippingInCart('update shipping', formData);
    if (cartInfo instanceof Promise) {
      cartInfo.then((cartResult) => {
        // Remove the loader.
        removeFullScreenLoader();

        let cartData = {};
        // If any error, don't process further.
        if (cartResult.error !== undefined) {
          dispatchCustomEvent('addressPopUpError', {
            type: 'error',
            message: cartResult.error_message,
          });
          return;
        }

        cartData = getInfoFromStorage();
        cartData.cart = cartResult;

        // Trigger event.
        dispatchCustomEvent('refreshCartOnAddress', cartData);
      });
    }

    return true;
  }).catch((error) => {
    Drupal.logJavascriptError(error);
  });
};

/**
 * Format the address which can be used as default value
 * for the edit address form fill.
 *
 * @param {*} address
 */
export const formatAddressDataForEditForm = (address) => {
  const formattedAddress = {
    static: {
      fullname: `${address.firstname} ${address.lastname}`,
      email: address.email,
      telephone: address.telephone,
    },
  };

  Object.entries(drupalSettings.address_fields).forEach(
    ([, field]) => {
      formattedAddress[field.key] = address[field.key];
    },
  );

  return formattedAddress;
};

/**
 * Get the address popup class.
 */
export const getAddressPopupClassName = () => (drupalSettings.user.uid > 0
  ? 'spc-address-list-member'
  : 'spc-address-form-guest');

/**
 * Saves customer address added in billing in addressbook.
 */
export const saveCustomerAddressFromBilling = (data) => {
  // If logged in user.
  if (drupalSettings.user.uid > 0) {
    // Add/update user address.
    return addEditUserAddress(data, false);
  }

  return Promise.resolve(null);
};

/**
 * Updates the address info in cart to middleware
 * and handle proccesing.
 *
 * @param {*} data
 * @param {*} data
 */
export const processBillingUpdateFromForm = (e, shipping) => {
  // Start the loader.
  showFullScreenLoader();

  const isValid = validateAddressFields(e, false);
  // If not valid.
  if (isValid) {
    removeFullScreenLoader();
    return;
  }

  const target = e.target.elements;

  const validationRequest = validateInfo({ mobile: target.mobile.value.trim() });
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

          target.email = {
            value: shipping.email,
          };
          const formData = prepareAddressDataFromForm(target);

          // For logged in user add customer id from shipping.
          let customerData = {};
          if (drupalSettings.user.uid > 0) {
            formData.static.customer_id = shipping.customer_id;
            customerData = {
              address: {
                given_name: formData.static.firstname,
                family_name: formData.static.firstname,
                city: formData.static.city,
                address_id: target.address_id.value,
              },
              mobile: cleanMobileNumber(formData.static.telephone),
            };

            // Getting dynamic fields data.
            Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
              customerData.address[key] = formData[field.key];
            });
          }

          const address = saveCustomerAddressFromBilling(customerData);
          if (address instanceof Promise) {
            address.then((list) => {
              if (list !== null) {
                if (list.error === true) {
                  removeFullScreenLoader();
                  dispatchCustomEvent('addressPopUpError', {
                    type: 'error',
                    message: list.error_message,
                  });
                  return;
                }

                const firstKey = Object.keys(list.data)[0];
                // Set the address id.
                formData.static.customer_address_id = list.data[firstKey].address_mdc_id;
              }

              // Update billing address.
              const cartData = addBillingInCart('update billing', formData);
              if (cartData instanceof Promise) {
                cartData.then((cartResult) => {
                  let cartInfo = {
                    cart: cartResult,
                  };

                  // Remove loader.
                  removeFullScreenLoader();

                  // If error.
                  if (cartResult.error !== undefined) {
                    dispatchCustomEvent('addressPopUpError', {
                      type: 'error',
                      message: cartResult.error_message,
                    });
                    return;
                  }

                  // Merging with existing local.
                  cartInfo = getInfoFromStorage();
                  cartInfo.cart = cartResult;

                  // Trigger the event for update.
                  dispatchCustomEvent('onBillingAddressUpdate', cartInfo);
                });
              }
            });
          }
        }
      }
    }).catch((error) => {
      Drupal.logJavascriptError(error);
    });
  }
};
