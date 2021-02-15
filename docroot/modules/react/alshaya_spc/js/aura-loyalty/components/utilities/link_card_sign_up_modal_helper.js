import {
  removeError,
} from '../../../../../alshaya_aura_react/js/utilities/aura_utils';

/**
 * Utility function to get link card element selector by type.
 */
function getElementSelector(type = 'all') {
  if (type === 'all') {
    const selectors = {
      email: '.spc-aura-link-card-wrapper input[name="email"]',
      signUpEmail: 'input[name="new-aura-user-email"]',
      cardNumber: '#spc-aura-link-card-input-card',
      mobile: '#spc-aura-link-card-input-mobile-mobile-number',
      signUpOtpMobile: '#otp-mobile-number',
      signUpMobile: '#new-aura-user-mobile-number',
      otp: '#otp',
    };
    return selectors;
  }

  if (type === 'email') {
    return { [type]: '.spc-aura-link-card-wrapper input[name="email"]' };
  }

  if (type === 'signUpEmail') {
    return { [type]: 'input[name="new-aura-user-email"]' };
  }

  if (type === 'emailCheckout') {
    return { [type]: '#spc-aura-link-card-input-email' };
  }

  if (type === 'cardNumber' || type === 'cardNumberCheckout') {
    return { [type]: '#spc-aura-link-card-input-card' };
  }

  if (type === 'mobile' || type === 'mobileCheckout') {
    return { [type]: '#spc-aura-link-card-input-mobile-mobile-number' };
  }

  if (type === 'signUpOtpMobile') {
    return { [type]: '#otp-mobile-number' };
  }

  if (type === 'signUpMobile') {
    return { [type]: '#new-aura-user-mobile-number' };
  }

  if (type === 'otp') {
    return { [type]: '#otp' };
  }

  if (type === 'fullName') {
    return { [type]: '#new-aura-user-full-name' };
  }

  return {};
}

/**
 * Utility function to get element value by type.
 */
function getElementValueByType(type, context = '') {
  let elementValue = '';

  const selector = getElementSelector(type)[type];
  const selectorWithContext = context ? `${context} ${selector}` : selector;
  const element = document.querySelector(selectorWithContext);
  elementValue = element ? element.value : '';

  if ((type === 'cardNumber' || type === 'cardNumberCheckout') && elementValue.length > 0) {
    elementValue = elementValue.replace(/\s/g, '');
  }

  return elementValue;
}

function getInlineErrorSelector(type = 'all') {
  if (type === 'all') {
    const selectors = {
      email: 'email-error',
      signUpEmail: 'new-aura-user-email-error',
      cardNumber: 'link-card-number-error',
      mobile: 'spc-aura-link-card-input-mobile-aura-mobile-field-error',
      signUpOtpMobile: 'otp-aura-mobile-field-error',
      signUpMobile: 'new-aura-user-aura-mobile-field-error',
      otp: 'otp-error',
    };
    return selectors;
  }

  if (type === 'email') {
    return { [type]: 'email-error' };
  }

  if (type === 'signUpEmail') {
    return { [type]: 'new-aura-user-email-error' };
  }

  if (type === 'cardNumber') {
    return { [type]: 'link-card-number-error' };
  }

  if (type === 'cardNumberCheckout' || type === 'emailCheckout' || type === 'mobileCheckout') {
    return { [type]: 'spc-aura-link-api-response-message' };
  }

  if (type === 'mobile') {
    return { [type]: 'spc-aura-link-card-input-mobile-aura-mobile-field-error' };
  }

  if (type === 'signUpOtpMobile') {
    return { [type]: 'otp-aura-mobile-field-error' };
  }

  if (type === 'signUpMobile') {
    return { [type]: 'new-aura-user-aura-mobile-field-error' };
  }

  if (type === 'otp') {
    return { [type]: 'otp-error' };
  }

  if (type === 'fullName') {
    return { [type]: 'new-aura-user-full-name-error' };
  }

  return {};
}

/**
 * Utility function to reset input field.
 */
function resetInputElement(type = 'all', context = '') {
  const selectors = Object.values(getElementSelector(type));

  if (selectors.length > 0) {
    selectors.forEach((selector) => {
      const elementSelector = context ? `${context} ${selector}` : selector;
      const element = document.querySelector(elementSelector);
      if (element) {
        element.value = '';
      }
    });
  }
}

/**
 * Utility function to reset inline error.
 */
function resetInlineError(type = 'all') {
  const selectors = Object.values(getInlineErrorSelector(type));

  if (selectors.length > 0) {
    selectors.forEach((selector) => {
      removeError(selector);
    });
  }
}

export {
  getElementValueByType,
  getElementSelector,
  getInlineErrorSelector,
  resetInputElement,
  resetInlineError,
};
