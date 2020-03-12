import axios from 'axios';
import {
  addShippingInCart,
  removeFullScreenLoader,
  showFullScreenLoader,
  triggerCheckoutEvent,
  addBillingInCart
} from './checkout_util';
import {
  extractFirstAndLastName
} from './cart_customer_util';
import {
  gerAreaLabelById
} from './address_util';
import {
  getInfoFromStorage
} from './storage';

/**
 * Process the data got from address form submission.
 *
 * @param {*} e
 * @param {*} cart
 */
export const checkoutAddressProcess = function (e, cart) {
  const notValidAddress = validateAddressFields(e, true);
  // If address form is not valid.
  if (notValidAddress) {
    // Remove the loader.
    removeFullScreenLoader();
    return;
  }

  // Get Prepare address.
  const form_data = prepareAddressDataFromForm(e.target.elements);

  const mobileValidationRequest = axios.get(`verify-mobile/${e.target.elements.mobile.value}`);
  const customerValidationReuest = axios.get(`${drupalSettings.alshaya_spc.middleware_url}/customer/${e.target.elements.email.value}`);

  // API call to validate mobile number and email address.
  return axios.all([mobileValidationRequest, customerValidationReuest]).then(axios.spread((...responses) => {
    const mobileResponse = responses[0].data;
    const customerResponse = responses[1].data;

    // Flag to determine if there any error.
    let isError = false;

    // If invalid mobile number.
    if (mobileResponse.status === false) {
      document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
      document.getElementById('mobile-error').classList.add('error');
      isError = true;
    } else {
      // Remove error class and any error message.
      document.getElementById('mobile-error').innerHTML = '';
      document.getElementById('mobile-error').classList.remove('error');
      isError = false;
    }

    // If customer already exists.
    if (cart.shipping_address === null
      && customerResponse.exists === true) {
      document.getElementById('email-error').innerHTML = Drupal.t('Customer already exists.');
      document.getElementById('email-error').classList.add('error');
      isError = true;
    } else if ((cart.shipping_address !== undefined && cart.shipping_address !== null)
      && cart.shipping_address.email !== form_data.static.email
      && customerResponse.exists === true) {
      document.getElementById('email-error').innerHTML = Drupal.t('Customer already exists.');
      document.getElementById('email-error').classList.add('error');
      isError = true;
    } else {
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

    // Get shipping methods based on address info.
    const cart_info = addShippingInCart('update shipping', form_data);
    if (cart_info instanceof Promise) {
      cart_info.then((cart_result) => {
        // Remove the loader.
        removeFullScreenLoader();

        let cart_data = {
          cart: cart_result,
        };

        // If any error, don't process further.
        if (cart_result.error !== undefined) {
          cart_data = {
            'error_message': cart_result.error_message
          };
        }

        // Trigger event.
        triggerCheckoutEvent('refreshCartOnAddress', cart_data);
      });
    }
  })).catch((errors) => {
    // react on errors.
  });
};


/**
 * Validate contact information.
 */
export const validateContactInfo = (e, validateEmail) => {
  let isError = false;
  const name = e.target.elements.fullname.value.trim();
  const splited_name = name.split(' ');
  if (name.length === 0 || splited_name.length === 1) {
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
      if (field.required === true || (
        key === 'area_parent' || key === 'administrative_area'
      )) {
        const add_field = e.target.elements[key].value.trim();
        if (add_field.length === 0) {
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
 * Format the address which can be used as default value
 * for the edit address form fill.
 *
 * @param {*} address
 */
export const formatAddressDataForEditForm = (address) => {
  let formatted_address = {
    'static': {
      'fullname': address.firstname + ' ' + address.lastname,
      'email': address.email,
      'telephone': address.telephone
    }
  };

  Object.entries(drupalSettings.address_fields).forEach(
    ([key, field]) => {
      formatted_address[field['key']] = address[field['key']];
    }
  );

  return formatted_address;
}

/**
 * Prepare address data from form value.
 *
 * @param {*} elements
 */
export const prepareAddressDataFromForm = (elements) => {
  let {
    firstname,
    lastname
  } = extractFirstAndLastName(elements.fullname.value.trim());

  let address = {
    firstname: firstname,
    lastname: lastname,
    email: elements.email.value,
    city: gerAreaLabelById(false, elements['administrative_area'].value),
    mobile: elements.mobile.value,
  };

  // Getting dynamic fields data.
  Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
    address[key] = elements[key].value;
  });

  return prepareAddressDataForShipping(address);
}

/**
 * Prepare address data for updating shipping.
 *
 * @param {*} address
 */
export const prepareAddressDataForShipping = (address) => {
  let data = {};
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
}

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

  let isValid = validateAddressFields(e, false);
  // If not valid.
  if (isValid) {
    removeFullScreenLoader();
    return;
  }

  let target = e.target.elements;

  const mobile = target.mobile.value.trim();
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

          target.email = {
            value: shipping.email
          };
          let form_data = prepareAddressDataFromForm(target);

          // For logged in user add customer id from shipping.
          if (drupalSettings.user.uid > 0) {
            form_data['static']['customer_id'] = shipping.customer_id;
          }

          // Update billing address.
          let cart_data = addBillingInCart('update billing', form_data);
          if (cart_data instanceof Promise) {
            cart_data.then((cart_result) => {
              let cart_info = {
                cart: cart_result
              };

              // If error.
              if (cart_result.error !== undefined) {
                // In case of error, prepare error info
                // and call refresh cart so that message is shown.
                cart_info = {
                  'error_message': cart_result.error_message
                }
              }
              else {
                // Merging with existing local.
                cart_info = getInfoFromStorage();
                cart_info.cart = cart_result;
              }

              // Trigger the event for updte.
              triggerCheckoutEvent('onBillingAddressUpdate', cart_info);

              // Remove loader.
              removeFullScreenLoader();
            });
          }
        }
      }
    });
  }
}
