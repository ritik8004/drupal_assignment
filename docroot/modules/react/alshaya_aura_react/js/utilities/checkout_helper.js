import { getElementValue, showError } from './aura_utils';
import getStringMessage from '../../../js/utilities/strings';

/**
 * Utility function to get user's aura details default state.
 */
function getUserAuraDetailsDefaultState() {
  const auraDetails = {
    loyaltyStatus: 0,
    tier: '',
    points: 0,
    cardNumber: '',
    email: '',
    mobile: '',
  };

  return auraDetails;
}

/**
 * Utility function to get user input value.
 */
function getUserInput(linkCardOption) {
  let elementValue = {};

  if (linkCardOption === 'mobile') {
    const mobile = getElementValue('spc-aura-link-card-input-mobile-mobile-number');

    if (mobile.length === 0 || mobile.match(/^[0-9]+$/) === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_mobile_number'));
      return {};
    }

    elementValue = {
      type: 'phone',
      value: mobile,
    };
  } else if (linkCardOption === 'card') {
    const card = getElementValue('spc-aura-link-card-input-card');

    if (card.length === 0) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_card'));
      return {};
    }

    elementValue = {
      type: 'apcNumber',
      value: card,
    };
  } else if (linkCardOption === 'email') {
    const email = getElementValue('spc-aura-link-card-input-email');

    if (email.length === 0 || email.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i) === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_email'));
      return {};
    }

    elementValue = {
      type: 'email',
      value: email,
    };
  }

  return elementValue;
}

export {
  getUserInput,
  getUserAuraDetailsDefaultState,
};
