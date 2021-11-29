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
import { getInlineErrorSelector, getElementValueByType } from './link_card_sign_up_modal_helper';

/**
 * Utility function to validate mobile by api call to drupal.
 */
function validateMobile(type, data) {
  let isValid = true;

  const validationRequest = validateInfo(data);
  showFullScreenLoader();
  return validationRequest.then((result) => {
    if (result.status === 200 && result.data.status) {
      // If not valid mobile number.
      if (!result.data.mobile) {
        showError(getInlineErrorSelector(type)[type], getStringMessage('form_error_valid_mobile_number'));
        isValid = false;
      } else {
        // If valid mobile number, remove error message.
        removeError(getInlineErrorSelector(type)[type]);
      }
    }
    removeFullScreenLoader();
    return isValid;
  });
}

/**
 * Utility function to get validation error message.
 */
function getValidationErrorMessage(type, modalType) {
  if (type === 'signUpOtpMobile' || type === 'signUpMobile') {
    return 'signup_empty_mobile';
  }

  if (type === 'mobile' || type === 'mobileCheckout') {
    if (modalType === 'link_card') {
      return 'link_card_empty_mobile';
    }
    return 'form_error_mobile_number';
  }

  if (type === 'email' || type === 'emailCheckout') {
    if (modalType === 'link_card') {
      return 'link_card_empty_email';
    }
    return 'form_error_email';
  }

  if (type === 'signUpEmail') {
    return 'signup_empty_email';
  }

  if (type === 'cardNumber' || type === 'cardNumberCheckout') {
    if (modalType === 'link_card') {
      return 'link_card_empty_card_number';
    }
    return 'form_error_empty_card';
  }

  return 'something_went_wrong';
}

/**
 * Utility function to validate input by element type.
 */
function validateElementValueByType(type, context, modalType) {
  const inputValue = getElementValueByType(type, context);

  if (type === 'mobile' || type === 'signUpOtpMobile' || type === 'signUpMobile' || type === 'mobileCheckout') {
    if (inputValue.length === 0 || inputValue.match(/^[0-9]+$/) === null) {
      showError(
        getInlineErrorSelector(type)[type],
        getStringMessage(getValidationErrorMessage(type, modalType)),
      );
      return false;
    }
    removeError(getInlineErrorSelector(type)[type]);
    return true;
  }

  if (type === 'email' || type === 'emailCheckout' || type === 'signUpEmail') {
    if (inputValue.length === 0 || inputValue.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i) === null) {
      showError(
        getInlineErrorSelector(type)[type],
        getStringMessage(getValidationErrorMessage(type, modalType)),
      );
      return false;
    }
    removeError(getInlineErrorSelector(type)[type]);
    return true;
  }

  if (type === 'cardNumber' || type === 'cardNumberCheckout') {
    if (inputValue.length === 0 || inputValue.match(/^[0-9]+$/) === null) {
      showError(
        getInlineErrorSelector(type)[type],
        getStringMessage(getValidationErrorMessage(type, modalType)),
      );
      return false;
    }
    removeError(getInlineErrorSelector(type)[type]);
    return true;
  }

  if (type === 'fullName') {
    if (inputValue.length === 0) {
      showError(getInlineErrorSelector(type)[type], getStringMessage('form_error_full_name'));
      return false;
    }
    let splitedName = inputValue.split(' ');
    splitedName = splitedName.filter((s) => (
      (s.trim().length > 0
      && (s !== '\\n' && s !== '\\t' && s !== '\\r'))));

    if (splitedName.length === 1) {
      showError(getInlineErrorSelector(type)[type], getStringMessage('form_error_full_name'));
      return false;
    }
    removeError(getInlineErrorSelector(type)[type]);
    return true;
  }

  return true;
}

export {
  validateElementValueByType,
  validateMobile,
};
