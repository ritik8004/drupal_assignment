import getStringMessage from '../../../../js/utilities/strings';
import { validEmailRegex } from './write_review_util';

/**
 * Validates the customer details.
 */
export const processCustomerDetails = async (e) => {
  // Flag to determine if there is any error.
  let isError = false;

  // Check to ensure no required field is empty.
  Array.prototype.forEach.call(e.target.elements, (element) => {
    if (!element.id) {
      return;
    }
    if (!element.value.length) {
      document.getElementById(`${element.id}-error`).innerHTML = getStringMessage('empty_field_default_error');
      document.getElementById(`${element.id}-error`).classList.add('error');
      document.getElementById(`${element.id}`).classList.add('error');
      isError = true;
    } else {
      document.getElementById(`${element.id}-error`).innerHTML = '';
      document.getElementById(`${element.id}-error`).classList.remove('error');
      document.getElementById(`${element.id}`).classList.add('error');
    }
  });

  const targetElementEmail = e.target.elements.email;
  if (targetElementEmail !== undefined && targetElementEmail.value.toString().length > 0) {
    const isValidEmail = validEmailRegex.test(targetElementEmail.value);
    if (!isValidEmail) {
      document.getElementById(`${targetElementEmail.id}`).classList.add('error');
      document.getElementById(`${targetElementEmail.id}-error`).innerHTML = getStringMessage('valid_email_error', { '%mail': targetElementEmail.value });
      document.getElementById(`${targetElementEmail.id}-error`).classList.add('error');
      isError = true;
    }
  }

  const targetElementCommentbox = e.target.elements.commentbox;
  if (targetElementCommentbox !== undefined
    && targetElementCommentbox.value.toString().length < 100) {
    document.getElementById(`${targetElementCommentbox.id}`).classList.add('error');
    document.getElementById(`${targetElementCommentbox.id}-error`).innerHTML = getStringMessage('commentbox_length_error');
    document.getElementById(`${targetElementCommentbox.id}-error`).classList.add('error');
    isError = true;
  }
  return isError;
};

export default {
  processCustomerDetails,
};
