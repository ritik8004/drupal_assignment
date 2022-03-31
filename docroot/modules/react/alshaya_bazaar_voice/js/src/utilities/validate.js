import getStringMessage from '../../../../js/utilities/strings';
import { validateInputLang, validEmailRegex } from './write_review_util';
import { getbazaarVoiceSettings } from './api/request';

/**
 * Validates the form details.
 */
export const processFormDetails = (e, ReviewId) => {
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  // Flag to determine if there is any error.
  let isError = false;

  // Check to ensure no required field is empty.
  Array.prototype.forEach.call(e.target.elements, (element) => {
    if (!element.id || element.id === 'ioBlackBox') {
      return;
    }
    if (!element.value.length) {
      const label = element.id.replace(`-${ReviewId}`, '');
      const title = getStringMessage(label) !== '' ? getStringMessage(label) : getStringMessage('screen_name');
      document.getElementById(`${element.id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': title });
      document.getElementById(`${element.id}-error`).classList.add('error');
      document.getElementById(`${element.id}`).classList.add('error');
      isError = true;
    } else {
      document.getElementById(`${element.id}-error`).innerHTML = '';
    }
  });

  const targetElementEmail = e.target.elements.email;
  if (targetElementEmail.value.length > 0) {
    const isValidEmail = validEmailRegex.test(targetElementEmail.value);
    if (!isValidEmail) {
      document.getElementById(`${targetElementEmail.id}`).classList.add('error');
      document.getElementById(`${targetElementEmail.id}-error`).innerHTML = getStringMessage('valid_email_error', { '%mail': targetElementEmail.value });
      document.getElementById(`${targetElementEmail.id}-error`).classList.add('error');
      isError = true;
    }
  }

  const targetElementCommentbox = e.target.elements.commentbox;
  const commentMinLength = bazaarVoiceSettings.reviews.bazaar_voice.comment_box_min_length;
  if (targetElementCommentbox.value.length > 0
    && !validateInputLang(targetElementCommentbox.value)) {
    document.getElementById(`${targetElementCommentbox.id}`).classList.add('error');
    document.getElementById(`${targetElementCommentbox.id}-error`).innerHTML = getStringMessage('text_input_lang_error');
    document.getElementById(`${targetElementCommentbox.id}-error`).classList.add('error');
    isError = true;
  } else if (targetElementCommentbox.value.length > 0
    && targetElementCommentbox.value.length < commentMinLength) {
    const commentlabel = getStringMessage('comment');
    document.getElementById(`${targetElementCommentbox.id}`).classList.add('error');
    document.getElementById(`${targetElementCommentbox.id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': targetElementCommentbox.minLength, '%fieldTitle': commentlabel });
    document.getElementById(`${targetElementCommentbox.id}-error`).classList.add('error');
    isError = true;
  }

  const targetElementNickname = e.target.elements.nickname;
  const nicknameMinLength = bazaarVoiceSettings.reviews.bazaar_voice.screen_name_min_length;
  if (targetElementNickname.value.length > 0
    && targetElementNickname.value.length < nicknameMinLength) {
    const label = getStringMessage('screen_name');
    document.getElementById(`${targetElementNickname.id}`).classList.add('error');
    document.getElementById(`${targetElementNickname.id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': targetElementNickname.minLength, '%fieldTitle': label });
    document.getElementById(`${targetElementNickname.id}-error`).classList.add('error');
    isError = true;
  }

  return isError;
};

/**
 * To Get average percentage value.
 */
export const getPercentVal = (count, totalCount) => {
  const average = count / totalCount;

  return average * 100;
};

export default {
  processFormDetails,
  getPercentVal,
};
