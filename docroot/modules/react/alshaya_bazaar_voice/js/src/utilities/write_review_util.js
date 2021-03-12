import { getCurrentUserEmail, getSessionCookie } from './user_util';
import { getbazaarVoiceSettings } from './api/request';
import getStringMessage from '../../../../js/utilities/strings';

const bazaarVoiceSettings = getbazaarVoiceSettings();

/**
 * Email address validation.
 */
export const validEmailRegex = RegExp(
  // eslint-disable-next-line
  /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
);

export const getArraysIntersection = (currentOptions, options) => currentOptions.filter((n) => {
  if (options.find((element) => element.value === n.value) !== undefined) {
    return true;
  }
  return false;
});

/**
 * Prepare request data to be sent in submit review api.
 *
 * @param {*} elements
 * @param {*} fieldsConfig
 */
export const prepareRequest = (elements, fieldsConfig) => {
  let params = '';

  Object.entries(fieldsConfig).forEach(([key, field]) => {
    const id = fieldsConfig[key]['#id'];
    // Add input data from field types.
    try {
      if (elements[id].value !== null) {
        params += `&${id}=${elements[id].value}`;

        if (id === 'useremail' && getCurrentUserEmail() === null) {
          params += `&HostedAuthentication_AuthenticationEmail=${elements[id].value}`;
        }
      }
    } catch (e) { return null; }

    return field;
  });

  // Add photo urls uploaded from photo upload.
  if (elements.photoCount.value > 0) {
    const count = Number(elements.photoCount.value);
    [...Array(count)].map((key, index) => {
      const photoId = `photourl_${(index + 1)}`;
      params += `&${photoId}=${elements[photoId].value}`;

      return key;
    });
  }
  // Set callback url for BV auntheticated user.
  if (getCurrentUserEmail() === null) {
    params += `&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
  }
  // Set user authenticated string (UAS).
  const userToken = getSessionCookie();
  if (getCurrentUserEmail() !== null && userToken !== undefined) {
    params += `&user=${userToken}`;
  }
  // Set product id
  params += `&productid=${bazaarVoiceSettings.productid}`;
  // Add device finger printing string.
  if (elements.blackBox.value !== '') {
    params += `&fp=${elements.blackBox.value}`;
  }
  // Add tnc status and it must be true only.
  params += `&agreedtotermsandconditions=${true}`;
  // Set action type.
  params += '&action=submit';

  return params;
};

/**
 * Validate request data to be passed in submit review api.
 *
 * @param {*} elements
 * @param {*} fieldsConfig
 */
export const validateRequest = (elements, fieldsConfig) => {
  let isError = false;

  Object.entries(fieldsConfig).forEach(([key, field]) => {
    const id = fieldsConfig[key]['#id'];
    const required = fieldsConfig[key]['#required'];
    const groupType = fieldsConfig[key]['#group_type'];
    // Validate input data from field types.
    try {
      if (required) {
        if (elements[id].value === '') {
          switch (groupType) {
            case 'textfield':
            case 'textarea':
              document.getElementById(`${id}-error`).innerHTML = getStringMessage('empty_field_default_error');
              document.getElementById(`${id}-error`).classList.add('error');
              isError = true;
              break;
            case 'select':
              document.getElementById(`${id}-error`).innerHTML = getStringMessage('empty_select_field_default_error');
              document.getElementById(`${id}-error`).classList.add('error');
              isError = true;
              break;
            case 'ratings':
              document.getElementById(`${id}-error`).classList.add('rating-error');
              isError = true;
              break;
            default:
              document.getElementById(`${id}-error`).classList.add('radio-error');
              isError = true;
          }
        } else if (id === 'reviewtext'
          || id === 'usernickname'
          || id === 'useremail') {
          if (id === 'reviewtext' || id === 'usernickname') {
            if (elements[id].value.length < fieldsConfig[key]['#minlength']) {
              document.getElementById(`${id}-error`).classList.add('error');
              isError = true;
            }
          }
          if (id === 'useremail' && !validEmailRegex.test(elements[id].value)) {
            document.getElementById(`${id}-error`).classList.add('error');
            isError = true;
          }
        } else {
          if (groupType === 'textfield'
           || groupType === 'textarea'
           || groupType === 'select') {
            document.getElementById(`${id}-error`).innerHTML = '';
          }
          document.getElementById(`${id}-error`).classList.remove('error');
          document.getElementById(`${id}-error`).classList.remove('radio-error');
          document.getElementById(`${id}-error`).classList.remove('rating-error');
        }
      }
    } catch (e) { return null; }

    return field;
  });

  return isError;
};

/**
 * When click on review submit.
 */
export const onReviewPost = (e) => {
  // Dispatch event so that other can use this.
  const event = new CustomEvent('reviewPosted', {
    bubbles: true,
    detail: {
      formElement: () => e.target.elements,
    },
  });
  document.dispatchEvent(event);
};
