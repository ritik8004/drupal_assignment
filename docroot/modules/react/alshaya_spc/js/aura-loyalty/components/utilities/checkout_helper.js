/* eslint-disable */
import { getElementValue, showError } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../js/utilities/strings';

/**
 * Utility function to get user input value.
 */
function getUserInput(linkCardOption, chosenCountryCode) {
  let elementValue = {};

  if (linkCardOption === 'mobile') {
    const mobile = getElementValue('spc-aura-link-card-input-mobile-mobile-number');

    if (mobile.length === 0 || mobile.match(/^[0-9]+$/) === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_mobile_number'));
      return {};
    }

    elementValue = {
      key: 'mobile',
      type: 'phone',
      value: chosenCountryCode + mobile,
    };
  } else if (linkCardOption === 'cardNumber') {
    const card = getElementValue('spc-aura-link-card-input-card');

    if (card.length === 0) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_card'));
      return {};
    }

    elementValue = {
      key: 'cardNumber',
      type: 'apcNumber',
      value: card.replace(/\s/g, ''),
    };
  } else if (linkCardOption === 'email') {
    const email = getElementValue('spc-aura-link-card-input-email');

    if (email.length === 0 || email.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i) === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_email'));
      return {};
    }

    elementValue = {
      key: 'email',
      type: 'email',
      value: email,
    };
  }

  return elementValue;
}

export {
  getUserInput,
};
