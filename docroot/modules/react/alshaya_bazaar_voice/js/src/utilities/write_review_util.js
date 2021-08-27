import { getbazaarVoiceSettings, getUserDetails } from './api/request';
import getStringMessage from '../../../../js/utilities/strings';
import { getStorageInfo } from './storage';
import { smoothScrollTo } from './smoothScroll';
import dispatchCustomEvent from '../../../../js/utilities/events';

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
export const prepareRequest = async (elements, fieldsConfig, productId) => {
  let params = '';
  const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
  const userDetails = await getUserDetails(productId);
  const userStorage = getStorageInfo(`bvuser_${userDetails.user.userId}`);

  Object.entries(fieldsConfig).forEach(([key, field]) => {
    const id = fieldsConfig[key]['#id'];
    // Add input data from field types.
    try {
      if (elements[id].value !== null) {
        if (id === 'useremail') {
          if (userDetails.user.userId === 0 && userStorage !== null) {
            if (userStorage.uasToken === null) {
              // Add email value to anonymous user storage.
              if (userStorage.email === undefined
                || (userStorage.email !== undefined
                && userStorage.email !== elements[id].value)) {
                userStorage.email = elements[id].value;
              }
              if (userStorage.bvUserId === undefined
                || (userStorage.email !== undefined
                && userStorage.email !== elements[id].value)) {
                params += `&HostedAuthentication_AuthenticationEmail=${elements[id].value}&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
              }
            }
          }
        } else if (id === 'usernickname') {
          // Add nickname value to user storage.
          if (userStorage !== null) {
            if (userStorage.nickname === undefined
              || (userStorage.nickname !== undefined
              && userStorage.nickname !== elements[id].value)) {
              userStorage.nickname = encodeURIComponent(elements[id].value);
            }
          }
        }
        params += `&${id}=${encodeURIComponent(elements[id].value)}`;
      }
    } catch (e) { return null; }

    return field;
  });

  // Add photo urls uploaded from photo upload.
  if (elements.photoCount !== undefined && elements.photoCount.value > 0) {
    const count = Number(elements.photoCount.value);
    [...Array(count)].map((key, index) => {
      const photoId = `photourl_${(index + 1)}`;
      params += `&${photoId}=${elements[photoId].value}`;

      return key;
    });
  }

  // Set user authenticated string (UAS).
  if (userStorage !== null) {
    if (userStorage.uasToken !== null && userStorage.uasToken !== undefined) {
      params += `&user=${userStorage.uasToken}`;
    } else if (userDetails.user.userId === 0 && userStorage.bvUserId !== undefined) {
      params += `&User=${userStorage.bvUserId}`;
    }
  }
  // Set product id
  params += `&productid=${bazaarVoiceSettings.productid}`;
  // Add device finger printing string.
  if (elements.blackBox.value !== '') {
    params += `&fp=${encodeURIComponent(elements.blackBox.value)}`;
  }
  // Add verified purchaser context value.
  const path = decodeURIComponent(window.location.search);
  const queryParams = new URLSearchParams(path);
  if (productId !== undefined || ((queryParams.get('messageType') === 'PIE' || queryParams.get('messageType') === 'PIE_FOLLOWUP')
    && bazaarVoiceSettings.productid === queryParams.get('productId'))) {
    params += `&contextdatavalue_VerifiedPurchaser=${true}`;
  }
  // Add tnc status and it must be true only.
  params += `&agreedtotermsandconditions=${true}`;
  // Set action type.
  params += '&action=submit';

  const requestParams = {
    params,
    userStorage,
  };
  return requestParams;
};

/**
 * Validate request data to be passed in submit review api.
 *
 * @param {*} elements
 * @param {*} fieldsConfig
 */
export const validateRequest = (elements, fieldsConfig, e, newPdp) => {
  let isError = false;

  Object.entries(fieldsConfig).forEach(([key, field]) => {
    const id = fieldsConfig[key]['#id'];
    const required = fieldsConfig[key]['#required'];
    const groupType = fieldsConfig[key]['#group_type'];
    const title = fieldsConfig[key]['#title'];
    // Validate input data from field types.
    try {
      if (required) {
        if (elements[id].value === '') {
          switch (groupType) {
            case 'textfield':
            case 'textarea':
              document.getElementById(`${id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': title });
              document.getElementById(`${id}-error`).classList.add('error');
              document.getElementById(id).classList.add('error');
              isError = true;
              break;
            case 'select':
              document.getElementById(`${id}-error`).innerHTML = getStringMessage('empty_select_field_default_error', { '%fieldTitle': title });
              document.getElementById(`${id}-error`).classList.add('error');
              isError = true;
              break;
            case 'ratings':
              document.getElementById(`${id}-error`).classList.add('rating-error');
              document.getElementById(`${id}-error`).classList.add('error');
              isError = true;
              break;
            default:
              document.getElementById(`${id}-error`).classList.add('radio-error');
              document.getElementById(`${id}-error`).classList.add('error');
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
          if (!isError) {
            document.getElementById(`${id}-error`).classList.remove('error');
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
        // Scroll to error message.
        if (isError && newPdp) {
          smoothScrollTo(e, '#reviews-section .error', newPdp, 'write_review');
        } else if (isError) {
          smoothScrollTo(e, '.error', '', 'write_review');
        }
      }
    } catch (exception) { return null; }

    return field;
  });

  return isError;
};

/**
 * When click on review submit.
 */
export const onReviewPost = (e) => {
  // Dispatch event so that other can use this.
  dispatchCustomEvent('reviewPosted', { formElement: () => e.target.elements });
};

export const convertHex2aString = (hex) => {
  let str = '';
  for (let i = 0; i < hex.length; i += 2) {
    str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
  }
  return str;
};
