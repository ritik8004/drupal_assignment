import getStringMessage from '../../../../js/utilities/strings';
import { validEmailRegex } from './write_review_util';
import { getbazaarVoiceSettings } from './api/request';

/**
 * Validates the form details.
 */
export const processFormDetails = (e) => {
  // Flag to determine if there is any error.
  let isError = false;

  // Check to ensure no required field is empty.
  Array.prototype.forEach.call(e.target.elements, (element) => {
    if (!element.id || element.id === 'ioBlackBox') {
      return;
    }
    if (!element.value.length) {
      const title = getStringMessage('screen_name');
      document.getElementById(`${element.id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': title });
      document.getElementById(`${element.id}-error`).classList.add('error');
      document.getElementById(`${element.id}`).classList.add('error');
      isError = true;
    } else {
      document.getElementById(`${element.id}-error`).innerHTML = '';
      document.getElementById(`${element.id}-error`).classList.remove('error');
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
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  const commentMinLength = bazaarVoiceSettings.reviews.bazaar_voice.comment_form_box_length;
  if (targetElementCommentbox !== undefined
    && targetElementCommentbox.value.toString().length < commentMinLength) {
    const label = getStringMessage('comment');
    document.getElementById(`${targetElementCommentbox.id}`).classList.add('error');
    document.getElementById(`${targetElementCommentbox.id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': targetElementCommentbox.minLength, '%fieldTitle': label });
    document.getElementById(`${targetElementCommentbox.id}-error`).classList.add('error');
    isError = true;
  }
  return isError;
};

export default {
  processFormDetails,
};
