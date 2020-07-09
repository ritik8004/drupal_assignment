import dispatchCustomEvent from './events';
import getStringMessage from './strings';
import { GTM_CHECKOUT_ERRORS } from './constants';

export const markFieldAsValid = (id) => {
  try {
    document.getElementById(id).innerHTML = '';
    document.getElementById(id).classList.remove('error');
  } catch (e) {
    Drupal.logJavascriptError('markFieldAsValid fail', e, GTM_CHECKOUT_ERRORS);
  }
};

export const displayErrorMessage = (id, message) => {
  try {
    document.getElementById(id).innerHTML = message;
    document.getElementById(id).classList.add('error');
  } catch (e) {
    Drupal.logJavascriptError('displayErrorMessage fail', e, GTM_CHECKOUT_ERRORS);
  }
};

export const showRequiredMessage = (id) => {
  try {
    const title = document.getElementById(id).parentNode.querySelector('label');
    const message = (title === null)
      ? Drupal.t('This field is required.')
      : getStringMessage('address_please_enter', { '@label': title.innerHTML });
    document.getElementById(id).innerHTML = message;
    document.getElementById(id).classList.add('error');
  } catch (e) {
    Drupal.logJavascriptError('showRequiredMessage fail', e, GTM_CHECKOUT_ERRORS);
  }
};

export const handleValidationMessage = (id, value, isValid, invalidMessage) => {
  if (isValid) {
    markFieldAsValid(id);
  } else if (value.length === 0) {
    showRequiredMessage(id);
  } else {
    displayErrorMessage(id, invalidMessage);
  }

  dispatchCustomEvent('refreshCompletePurchaseSection', {});
};
