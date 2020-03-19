import {
  addShippingInCart,
  removeFullScreenLoader,
  showFullScreenLoader,
  triggerCheckoutEvent,
  addBillingInCart, validateInfo, cleanMobileNumber,
} from './checkout_util';
import {
  extractFirstAndLastName,
} from './cart_customer_util';
import {
  gerAreaLabelById,
  addEditUserAddress
} from './address_util';
import {
  getInfoFromStorage,
} from './storage';

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
  Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
    address[key] = elements[key].value;
  });

  return prepareAddressDataForShipping(address);
};

/**
 * Process the data got from address form submission.
 *
 * @param {*} e
 */
export const checkoutAddressProcess = function (e) {
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

  if (e.target.elements.email !== undefined && e.target.elements.email.value.toString().length > 0) {
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
          cartData = {
            error_message: cartResult.error_message,
          };
        } else {
          cartData = getInfoFromStorage();
          cartData.cart = cartResult;
        }

        // Trigger event.
        triggerCheckoutEvent('refreshCartOnAddress', cartData);
      });
    }
  }).catch((error) => {
    console.error(error);
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
    ([key, field]) => {
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
                address_id: target.address_id.value
              },
              mobile: cleanMobileNumber(formData.static.telephone)
            };

            // Getting dynamic fields data.
            Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
              customerData.address[key] = formData[field['key']];
            });
          }

          const address = saveCustomerAddressFromBilling(customerData);
          if (address instanceof Promise) {
            address.then((list) => {
              if (list !== null) {
                if(list.error === true) {
                  removeFullScreenLoader();
                  const eventData = {
                    error: true,
                    error_message: list.error_message,
                  };
                  triggerCheckoutEvent('onBillingAddressUpdate', eventData);
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

                  // If error.
                  if (cartResult.error !== undefined) {
                    // In case of error, prepare error info
                    // and call refresh cart so that message is shown.
                    cartInfo = {
                      error_message: cartResult.error_message,
                    };
                  } else {
                    // Merging with existing local.
                    cartInfo = getInfoFromStorage();
                    cartInfo.cart = cartResult;
                  }

                  // Trigger the event for updte.
                  triggerCheckoutEvent('onBillingAddressUpdate', cartInfo);

                  // Remove loader.
                  removeFullScreenLoader();
                });
              }
            });
          }
        }
      }
    }).catch((error) => {
      console.error(error);
    });
  }
};

/**
 * Saves customer address added in billing in addressbook.
 */
export const saveCustomerAddressFromBilling = (data) => {
  // If logged in user.
  if (drupalSettings.user.uid > 0) {
    // Add/update user address.
    return addEditUserAddress(data);
  }

  return Promise.resolve(null);
};
