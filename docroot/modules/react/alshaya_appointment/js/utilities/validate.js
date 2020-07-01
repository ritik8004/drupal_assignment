import axios from 'axios';

export const validateInfo = (data) => axios.post(Drupal.url('appointment/validate-info'), data);

/**
 * Validates the customer details.
 */
export const processCustomerDetails = async (e) => {
  // Flag to determine if there is any error.
  let isError = false;

  // Check to ensure no required field is empty.
  Array.prototype.forEach.call(e.target.elements, (element) => {
    if (element.required && !element.value.length) {
      isError = true;
    }
  });

  const validationData = {
    mobile: e.target.elements.mobile.value,
  };
  const targetElementEmail = e.target.elements.email;
  if (targetElementEmail !== undefined && targetElementEmail.value.toString().length > 0) {
    validationData.email = e.target.elements.email.value;
  }

  return await validateInfo(validationData).then((response) => {
    if (!response || response.data.status === undefined || !response.data.status) {
      return false;
    }

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

    // If invalid email address.
    if (response.data.email !== undefined) {
      if (response.data.email === false) {
        document.getElementById('email-error').innerHTML = Drupal.t('The email address %mail is not valid.', { '%mail': validationData.email });
        document.getElementById('email-error').classList.add('error');
        isError = true;
      } else {
        // Remove error class and any error message.
        document.getElementById('email-error').innerHTML = '';
        document.getElementById('email-error').classList.remove('error');
      }
    }

    return isError;
  })
    .catch((error) => {
      Drupal.logJavascriptError('Email and mobile number validation fail', error);
    });
};
