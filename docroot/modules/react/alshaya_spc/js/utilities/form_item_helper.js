import { dispatchCustomEvent } from './events';

export const markFieldAsValid = (id) => {
  try {
    document.getElementById(id).innerHTML = '';
    document.getElementById(id).classList.remove('error');
  } catch (e) {
    console.error(e);
  }
};

export const displayErrorMessage = (id, message) => {
  try {
    document.getElementById(id).innerHTML = message;
    document.getElementById(id).classList.add('error');
  } catch (e) {
    console.error(e);
  }
};

export const showRequiredMessage = (id) => {
  try {
    const title = document.getElementById(id).parentNode.querySelector('label');
    const message = (title === null)
      ? Drupal.t('This field is required.')
      : Drupal.t('Please enter @label.', { '@label': title.innerHTML });
    document.getElementById(id).innerHTML = message;
    document.getElementById(id).classList.add('error');
  } catch (e) {
    console.error(e);
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
