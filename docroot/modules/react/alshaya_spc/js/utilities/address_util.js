import axios from 'axios';
import parse from 'html-react-parser';

import {
  addBillingInCart,
  addShippingInCart, cleanMobileNumber,
  removeFullScreenLoader, showFullScreenLoader,
  validateInfo,
} from './checkout_util';
import getStringMessage from './strings';
import dispatchCustomEvent from './events';
import { extractFirstAndLastName, makeFullName } from './cart_customer_util';
import {
  smoothScrollToAddressField,
  smoothScrollTo,
} from './smoothScroll';
import { isExpressDeliveryEnabled } from '../../../js/utilities/expressDeliveryHelper';
import { setDeliveryAreaStorage } from './delivery_area_util';
import { hasValue } from '../../../js/utilities/conditionsUtility';
import { isEgiftCardEnabled } from '../../../js/utilities/util';
import { isUserAuthenticated } from '../../../js/utilities/helper';
import { getTopUpQuote } from '../../../js/utilities/egiftCardHelper';

/**
 * Use this to auto scroll to the right field in address form upon
 * inline validation failure.
 *
 * @param {*} selector
 */
export const addressFormInlineErrorScroll = () => {
  // If error is on contact fields.
  const contactFieldsSelector = '.spc-checkout-contact-information-fields > div > div.error:not(:empty)';
  let errorElement = document.querySelector(contactFieldsSelector);
  if (errorElement !== undefined && errorElement !== null) {
    smoothScrollToAddressField(errorElement, true);
    return;
  }

  const addressFieldsSelector = '.delivery-address-fields > div > div.error:not(:empty)';
  errorElement = document.querySelector(addressFieldsSelector);
  // If error found in address fields, scroll and return.
  if (errorElement !== undefined && errorElement !== null) {
    smoothScrollToAddressField(errorElement);
  }
};

/**
 * Get the address list of the current logged in user.
 */
export const getUserAddressList = () => axios.get('spc/user-address-list')
  .then((response) => response.data)
  .catch((error) => {
    // Processing of error here.
    Drupal.logJavascriptError('get-user-address-list', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
  });

/**
 * Add / Edit address for customer.
 *
 * @param {*} address
 * @param {*} isDefault
 */
export const addEditUserAddress = (address, isDefault) => axios.post('spc/add-edit-address', {
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
    Drupal.logJavascriptError('add-edit-user-address', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
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
    postcode: address.postal_code,
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
    shipping_type: 'click_and_collect',
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

  // Save area for customer if express delivery feature enabled.
  if (isExpressDeliveryEnabled()) {
    const areaSelected = {
      label: gerAreaLabelById(false, elements.administrative_area.value),
      area: parseInt(elements.administrative_area.value, 10),
      governate: parseInt(elements.area_parent.value, 10),
    };
    setDeliveryAreaStorage(areaSelected);
  }

  return prepareAddressDataForShipping(address);
};

/**
 * Validate contact information.
 */
export const validateContactInfo = (e, validateEmail) => {
  let isError = false;
  const name = e.target.elements.fullname.value.trim();
  let splitedName = name.split(' ');
  splitedName = splitedName.filter((s) => (
    (s.trim().length > 0
    && (s !== '\\n' && s !== '\\t' && s !== '\\r'))));
  if (name.length === 0 || splitedName.length === 1) {
    document.getElementById('fullname-error').innerHTML = getStringMessage('form_error_full_name');
    document.getElementById('fullname-error').classList.add('error');
    isError = true;
  } else {
    document.getElementById('fullname-error').innerHTML = '';
    document.getElementById('fullname-error').classList.remove('error');
  }

  const mobile = e.target.elements.mobile.value.trim();
  if (mobile.length === 0
    || mobile.match(/^[0-9]+$/) === null) {
    document.getElementById('mobile-error').innerHTML = getStringMessage('form_error_mobile_number');
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
      document.getElementById('email-error').innerHTML = getStringMessage('form_error_email');
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
      if (field.required === true && field.visible === true) {
        const addField = e.target.elements[key].value.trim();
        if (addField.length === 0) {
          document.getElementById(`${key}-error`).innerHTML = getStringMessage('address_please_enter', { '@label': field.label });
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
    addressFormInlineErrorScroll();
    return;
  }

  const target = e.target.elements;
  // Validate mobile number.
  const mobile = e.target.elements.mobile.value.trim();

  const validationData = {
    mobile: target.mobile.value.trim(),
    fullname: extractFirstAndLastName(target.fullname.value.trim()),
  };

  const validationRequest = validateInfo(validationData);
  if (validationRequest instanceof Promise) {
    validationRequest.then((result) => {
      if (result.status === 200 && result.data.status) {
        let validName = true;
        // If invalid full name.
        if (result.data.fullname === false) {
          validName = false;
          removeFullScreenLoader();
          document.getElementById('fullname-error').innerHTML = getStringMessage('form_error_full_name');
          document.getElementById('fullname-error').classList.add('error');
          return;
        }

        // If name is valid.
        if (validName === true) {
          // Remove error class and any error message.
          document.getElementById('fullname-error').innerHTML = '';
          document.getElementById('fullname-error').classList.remove('error');
        }

        // If not valid mobile number.
        if (result.data.mobile === false) {
          // Removing loader in case validation fail.
          removeFullScreenLoader();
          document.getElementById('mobile-error').innerHTML = getStringMessage('form_error_valid_mobile_number');
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
                  showDismissButton: false,
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

                  // If error, no need to process.
                  if ((cartResult.error !== undefined)
                    || (typeof cartResult.response_message !== 'undefined'
                    && cartResult.response_message.status !== 'success')
                  ) {
                    const responseErrorMessage = (typeof cartResult.error_message !== 'undefined')
                      ? cartResult.error_message
                      : cartResult.response_message.msg;
                    dispatchCustomEvent('addressPopUpError', {
                      type: 'error',
                      message: responseErrorMessage,
                      showDismissButton: false,
                    });

                    // Add address id in hidden id field so that on next
                    // save, new address is not created but uses existing one.
                    const addressIdHiddenElement = document.getElementsByName('address_id');
                    if (addressIdHiddenElement.length > 0) {
                      document.getElementsByName('address_id')[0].value = list.data[firstKey].address_id;
                    }

                    return;
                  }

                  const cartData = { cart: cartResult };

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

    // Sort list by label.
    areasList.sort((a, b) => ((a.label > b.label) ? 1 : -1));
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
    addressFormInlineErrorScroll();
    return;
  }

  // Get Prepare address.
  const formData = prepareAddressDataFromForm(e.target.elements);

  const validationData = {
    mobile: e.target.elements.mobile.value,
    fullname: extractFirstAndLastName(e.target.elements.fullname.value.trim()),
  };
  const targetElementEmail = e.target.elements.email;
  if (targetElementEmail !== undefined && targetElementEmail.value.toString().length > 0) {
    validationData.email = e.target.elements.email.value;
  }

  const validationRequest = validateInfo(validationData);
  validationRequest.then((response) => {
    if (!response || response.data.status === undefined || !response.data.status) {
      // API Call failed.
      Drupal.logJavascriptError('Email and mobile number validation fail', 'response format invalid', GTM_CONSTANTS.CHECKOUT_ERRORS);
      return false;
    }

    // Flag to determine if there any error.
    let isError = false;

    // If invalid mobile number.
    if (response.data.mobile === false) {
      document.getElementById('mobile-error').innerHTML = getStringMessage('form_error_valid_mobile_number');
      document.getElementById('mobile-error').classList.add('error');
      isError = true;
    } else {
      // Remove error class and any error message.
      document.getElementById('mobile-error').innerHTML = '';
      document.getElementById('mobile-error').classList.remove('error');
    }

    // If invalid full name.
    if (response.data.fullname === false) {
      document.getElementById('fullname-error').innerHTML = getStringMessage('form_error_full_name');
      document.getElementById('fullname-error').classList.add('error');
      isError = true;
    } else {
      // Remove error class and any error message.
      document.getElementById('fullname-error').innerHTML = '';
      document.getElementById('fullname-error').classList.remove('error');
    }

    // Do the processing only if we did email validation.
    if (response.data.email !== undefined) {
      if (response.data.email === 'invalid') {
        document.getElementById('email-error').innerHTML = getStringMessage('form_error_email_not_valid', { '%mail': validationData.email });
        document.getElementById('email-error').classList.add('error');
        isError = true;
      } else if (response.data.email === 'exists') {
        if (document.getElementById('email-error') !== null) {
          document.getElementById('email-error').innerHTML = getStringMessage('form_error_customer_exists');
          document.getElementById('email-error').classList.add('error');
        }
        isError = true;
      } else {
        // Remove error class and any error message.
        document.getElementById('email-error').innerHTML = '';
        document.getElementById('email-error').classList.remove('error');
      }
    }

    if (isError) {
      removeFullScreenLoader();
      addressFormInlineErrorScroll();
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
        if (!cartResult) {
          return;
        }

        // If any error, don't process further.
        if (cartResult.error !== undefined) {
          dispatchCustomEvent('addressPopUpError', {
            type: 'error',
            message: cartResult.error_message,
            showDismissButton: false,
          });
          return;
        }
        // Push error to GTM events.
        if (cartResult.is_error) {
          Drupal.logJavascriptError('checkout-address-save', cartResult.response_message.msg, GTM_CONSTANTS.CHECKOUT_ERRORS);
        }
        if (typeof cartResult.response_message !== 'undefined'
            && cartResult.response_message.status !== 'success') {
          dispatchCustomEvent('addressPopUpError', {
            type: 'error',
            message: cartResult.response_message.msg,
            showDismissButton: false,
          });

          return;
        }

        const cartData = { cart: cartResult };
        // Store the address in localStorage.
        Drupal.addItemInLocalStorage(
          'shippingaddress-formdata',
          formData,
          parseInt(drupalSettings.address_storage_expiration, 1440) * 60,
        );
        // Trigger event.
        dispatchCustomEvent('refreshCartOnAddress', cartData);
      });
    }

    return true;
  }).catch((error) => {
    Drupal.logJavascriptError('Email and mobile number validation fail', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
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
      fullname: makeFullName(address.firstname || '', address.lastname || ''),
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

export const customerHasAddress = (cart) => (drupalSettings.user.uid > 0
  && cart.cart.customer.addresses !== undefined
  && cart.cart.customer.addresses.length > 0);

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
  // If logged in user and user is not doing topup.
  if (getTopUpQuote() === null && drupalSettings.user.uid > 0) {
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
    addressFormInlineErrorScroll();
    return;
  }

  const target = e.target.elements;

  const validationData = {
    mobile: target.mobile.value.trim(),
    fullname: extractFirstAndLastName(target.fullname.value.trim()),
  };

  // If email id exists that means user is not an authenticated user and cart
  // contains only virtual item.
  // Now we are adding validation here to check if an account already exists
  // with this email id, if 'YES' then user will have to login before placing
  // order.
  if (hasValue(target.email)) {
    validationData.email = target.email.value;
  }

  const validationRequest = validateInfo(validationData);
  if (validationRequest instanceof Promise) {
    validationRequest.then((result) => {
      if (result.status === 200 && result.data.status) {
        let validName = true;
        // If invalid full name.
        if (result.data.fullname === false) {
          removeFullScreenLoader();
          validName = false;
          document.getElementById('fullname-error').innerHTML = getStringMessage('form_error_full_name');
          document.getElementById('fullname-error').classList.add('error');
          return;
        }

        // If name is valid, remove error.
        if (validName === true) {
          // Remove error class and any error message.
          document.getElementById('fullname-error').innerHTML = '';
          document.getElementById('fullname-error').classList.remove('error');
        }

        // If not valid mobile number.
        if (result.data.mobile === false) {
          // Removing loader in case validation fail.
          removeFullScreenLoader();
          document.getElementById('mobile-error').innerHTML = getStringMessage('form_error_valid_mobile_number');
          document.getElementById('mobile-error').classList.add('error');
        } else {
          // If valid mobile number, remove error message.
          document.getElementById('mobile-error').innerHTML = '';
          document.getElementById('mobile-error').classList.remove('error');
          // A flag value to check if error exists.
          let isError = false;
          // Validate if email id exists then throw error and return.
          if (result.data.email === 'exists') {
            if (document.getElementById('email-error') !== null) {
              document.getElementById('email-error').innerHTML = getStringMessage('form_error_customer_exists');
              document.getElementById('email-error').classList.add('error');
            }
            isError = true;
          } else if (result.data.email === 'invalid') {
            document.getElementById('email-error').innerHTML = getStringMessage('form_error_email_not_valid', { '%mail': validationData.email });
            document.getElementById('email-error').classList.add('error');
            isError = true;
          }
          // Return from here if error exists.
          if (isError) {
            // Removing loader in case validation fail.
            removeFullScreenLoader();
            addressFormInlineErrorScroll();
            return;
          }

          // Add this only when we are not passing email via form.
          if (!hasValue(target.email)) {
            let userEmail = hasValue(shipping) ? shipping.email : '';
            // Update user email id if it's missing from shipping method.
            if (!hasValue(userEmail)
              && isEgiftCardEnabled()
              && isUserAuthenticated()) {
              userEmail = drupalSettings.userDetails.userEmailID;
            }
            target.email = {
              value: userEmail,
            };
          }
          const formData = prepareAddressDataFromForm(target);

          // For logged in user add customer id from shipping.
          let customerData = {};
          // If user is doing topup then don't pass the customer detail as we are
          // using guest cart update endpoint for authenticated user for topup.
          if (getTopUpQuote() === null && drupalSettings.user.uid > 0) {
            // Incase of cart having only egift card then shipping information
            // is not available. use drupalSettings customer_id.
            formData.static.customer_id = hasValue(shipping) && hasValue(shipping.customer_id)
              ? shipping.customer_id : drupalSettings.userDetails.customerId;
            customerData = {
              address: {
                given_name: formData.static.firstname,
                family_name: formData.static.lastname,
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
              let addressId = null;
              if (list !== null) {
                if (list.error === true) {
                  removeFullScreenLoader();
                  dispatchCustomEvent('addressPopUpError', {
                    type: 'error',
                    message: list.error_message,
                    showDismissButton: false,
                  });
                  return;
                }

                const firstKey = Object.keys(list.data)[0];
                // Set the address id.
                formData.static.customer_address_id = list.data[firstKey].address_mdc_id;
                addressId = list.data[firstKey].address_id;
              }

              // Update billing address.
              const cartData = addBillingInCart('update billing', formData);
              if (cartData instanceof Promise) {
                cartData.then((cartResult) => {
                  if (!cartResult) {
                    return;
                  }
                  // Remove loader.
                  removeFullScreenLoader();

                  // If error.
                  if (cartResult.error !== undefined) {
                    dispatchCustomEvent('addressPopUpError', {
                      type: 'error',
                      message: cartResult.error_message,
                      showDismissButton: false,
                    });

                    // If not null, means this is for logged in user.
                    if (addressId !== null) {
                      // Add address id in hidden id field so that on next
                      // save, new address is not created but uses existing one.
                      const addressIdHiddenElement = document.getElementsByName('address_id');
                      if (addressIdHiddenElement.length > 0) {
                        document.getElementsByName('address_id')[0].value = addressId;
                      }
                    }

                    return;
                  }
                  if (typeof cartResult.response_message !== 'undefined'
                      && cartResult.response_message.status !== 'success') {
                    dispatchCustomEvent('addressPopUpError', {
                      type: 'error',
                      message: cartResult.response_message.msg,
                      showDismissButton: false,
                    });

                    return;
                  }

                  const cartInfo = { cart: cartResult };

                  // Trigger the event for update.
                  dispatchCustomEvent('onBillingAddressUpdate', cartInfo);

                  // Close the addresslist popup.
                  dispatchCustomEvent('closeAddressListPopup', true);
                });
              }
            });
          }
        }
      }
    }).catch((error) => {
      Drupal.logJavascriptError('Email and mobile number validation fail', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    });
  }
};

/**
 * Scroll to the first mandatory address field when not filled
 * on clicked on 'deliver to my location' or search address on map.
 */
export const errorOnDropDownFieldsNotFilled = () => {
  let showError = false;
  Object.entries(drupalSettings.address_fields).forEach(
    ([key]) => {
      // If city/area field.
      if (key === 'administrative_area'
        || key === 'area_parent') {
        const fieldVal = document.getElementById(key).value;
        // If area/city not filled.
        if (fieldVal.length === 0) {
          showError = true;
        }
      }
    },
  );

  const errorMessage = (showError === true)
    ? parse(getStringMessage('address_not_filled'))
    : null;

  // If no error, we remove the error message.
  dispatchCustomEvent('addressPopUpError', {
    type: 'warning',
    message: errorMessage,
    showDismissButton: false,
  });

  // If need to show error message.
  if (showError === true) {
    // Attach click handler to the dynamic element
    // to scroll to the address drop down.
    document.getElementById('scroll-to-dropdown').addEventListener('click', () => {
      // Remove error message on click.
      dispatchCustomEvent('addressPopUpError', {
        type: 'warning',
        message: null,
        showDismissButton: false,
      });
      // Scroll to address section.
      smoothScrollTo('.spc-address-form-sidebar .spc-type-select:first-child', 'center');
    });
  }
};

/**
 * Prepare address data to update shipping address.
 */
export const prepareAddressToUpdate = (address) => {
  const addressUpdate = address;
  addressUpdate.city = gerAreaLabelById(false, address.administrative_area);
  addressUpdate.mobile = `+${drupalSettings.country_mobile_code}${cleanMobileNumber(address.mobile)}`;
  const data = prepareAddressDataForShipping(addressUpdate);
  data.static.customer_address_id = address.address_mdc_id;
  data.static.customer_id = address.customer_id;
  return data;
};

/**
 * When user changes address, update the cart.
 */
export const updateSelectedAddress = (address, type) => {
  // Prepare address data for address info update.
  const data = prepareAddressToUpdate(address);

  // Update address on cart.
  const cartInfo = type === 'billing'
    ? addBillingInCart('update billing', data)
    : addShippingInCart('update shipping', data);

  if (cartInfo instanceof Promise) {
    cartInfo.then((cartResult) => {
      if (!cartResult) {
        return;
      }
      // Remove loader.
      removeFullScreenLoader();
      // Prepare cart data.
      let cartData = {};
      // If there is any error.
      if (cartResult.error !== undefined) {
        cartData = {
          error_message: cartResult.error_message,
        };
      } else if (typeof cartResult.response_message !== 'undefined'
          && cartResult.response_message.status !== 'success') {
        cartData = {
          error_message: cartResult.response_message.msg,
        };
      } else {
        cartData.cart = cartResult;
      }
      // Trigger event to close shipping popups.
      dispatchCustomEvent('refreshCartOnAddress', cartData);

      if (type === 'billing') {
        // Trigger event to close billing popups.
        dispatchCustomEvent('onBillingAddressUpdate', cartData);
      }
    });
  }
};

/**
 * Pre populate default area with storage value.
 * Blank out other address fields.
 */
export const editDefaultAddressFromStorage = (address, areaSelected) => {
  const addressData = { ...address };
  Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
    if (addressData[val.key] !== undefined && val.visible === true) {
      if (key === 'administrative_area') {
        addressData[val.key] = areaSelected.value[val.key];
      } else if (key === 'area_parent') {
        addressData[val.key] = areaSelected.value[val.key];
      } else {
        addressData[val.key] = '';
      }
    }
  });
  return addressData;
};
