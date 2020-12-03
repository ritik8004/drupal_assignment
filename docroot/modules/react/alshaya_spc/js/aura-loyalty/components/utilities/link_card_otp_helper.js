import {
  showError,
  removeError,
} from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../js/utilities/strings';
import { validateInfo } from '../../../utilities/checkout_util';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

/**
 * Utility function to get link card element selector by type.
 */
function getLinkCardElementSelector(type = 'all') {
  if (type === 'all') {
    const selectors = {
      email: '.spc-aura-link-card-wrapper input[name="email"]',
      cardNumber: '#spc-aura-link-card-input-card',
      mobile: '#spc-aura-link-card-input-mobile-mobile-number',
      otp: '#otp',
    };
    return selectors;
  }

  if (type === 'email') {
    return { type: '.spc-aura-link-card-wrapper input[name="email"]' };
  }

  if (type === 'cardNumber') {
    return { type: '#spc-aura-link-card-input-card' };
  }

  if (type === 'mobile') {
    return { type: '#spc-aura-link-card-input-mobile-mobile-number' };
  }

  if (type === 'otp') {
    return { type: '#otp' };
  }

  return {};
}

/**
 * Utility function to get element value by type.
 */
function getElementValueByType(type) {
  let elementValue = '';

  const element = document.querySelector(getLinkCardElementSelector(type).type);
  elementValue = element ? element.value : '';

  if (type === 'cardNumber' && elementValue.length > 0) {
    elementValue = elementValue.replace(/\s/g, '');
  }

  return elementValue;
}

/**
 * Utility function to get error selector by type.
 */
function getInlineErrorSelector(type) {
  if (type === 'email') {
    return 'email-error';
  }

  if (type === 'cardNumber') {
    return 'link-card-number-error';
  }

  if (type === 'mobile') {
    return 'spc-aura-link-card-input-mobile-aura-mobile-field-error';
  }

  return '';
}

/**
 * Utility function to validate element value by type.
 */
function validateElementValueByType(element) {
  let hasError = false;

  if (element.type === 'email') {
    if (element.value.length === 0 || element.value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i) === null) {
      showError(getInlineErrorSelector(element.type), getStringMessage('form_error_email'));
      hasError = true;
    } else {
      removeError(getInlineErrorSelector(element.type));
    }
    return hasError;
  }

  if (element.type === 'cardNumber') {
    if (element.value.length === 0 || element.value.match(/^[0-9]+$/) === null) {
      showError(getInlineErrorSelector(element.type), getStringMessage('form_error_empty_card'));
      hasError = true;
    } else {
      removeError(getInlineErrorSelector(element.type));
    }
    return hasError;
  }

  if (element.type === 'mobile') {
    if (element.value.length === 0 || element.value.match(/^[0-9]+$/) === null) {
      showError(getInlineErrorSelector(element.type), getStringMessage('form_error_mobile_number'));
      hasError = true;
    } else {
      removeError(getInlineErrorSelector(element.type));
    }
    return hasError;
  }

  return hasError;
}

function validateMobile(type, data) {
  let isValid = true;

  const validationRequest = validateInfo(data);
  showFullScreenLoader();
  return validationRequest.then((result) => {
    if (result.status === 200 && result.data.status) {
      // If not valid mobile number.
      if (result.data.mobile === false) {
        showError(getInlineErrorSelector(type), getStringMessage('form_error_valid_mobile_number'));
        isValid = false;
      } else {
        // If valid mobile number, remove error message.
        removeError(getInlineErrorSelector(type));
      }
    }
    removeFullScreenLoader();
    return isValid;
  });
}

/**
 * Utility function to reset input field.
 */
function resetInputElement(type = 'all') {
  const selectors = Object.values(getLinkCardElementSelector(type));

  if (selectors.length > 0) {
    selectors.forEach((selector) => {
      const element = document.querySelector(selector);
      if (element) {
        element.value = '';
      }
    });
  }
}

export {
  getElementValueByType,
  validateElementValueByType,
  getLinkCardElementSelector,
  getInlineErrorSelector,
  validateMobile,
  resetInputElement,
};
