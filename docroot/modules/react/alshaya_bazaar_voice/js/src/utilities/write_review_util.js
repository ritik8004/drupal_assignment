import { getCurrentUserEmail, getSessionCookie } from './user_util';
import { getbazaarVoiceSettings } from './api/request';

const bazaarVoiceSettings = getbazaarVoiceSettings();

/**
 * Email address validation.
 */
export const validEmailRegex = RegExp(
  // eslint-disable-next-line
  /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
);

/**
 * Prepare review data from form value.
 *
 * @param {*} elements
 * @param {*} fieldsConfig
 */
export const prepareRequest = (elements, fieldsConfig) => {
  let params = '';

  Object.entries(fieldsConfig).forEach(([key, field]) => {
    const id = fieldsConfig[key]['#id'];
    // Add input field data in request.
    try {
      if (elements[id].value !== null) {
        params += `&${id}=${elements[id].value}`;

        if (id === 'useremail' && getCurrentUserEmail() === null) {
          params += `&HostedAuthentication_AuthenticationEmail=${elements[id].value}`;
        }
      }
    } catch (e) { return null; }

    // Add photo type data in request
    if (field['#group_type'] === 'photo'
      && elements.photoCount.value > 0) {
      [...Array(5)].map((val, i) => {
        try {
          const photoId = `photourl_${(i + 1)}`;
          params += `&${photoId}=${elements[photoId].value}`;
        } catch (e) { return null; }
        return val;
      });
    }
    return field;
  });

  if (getCurrentUserEmail() === null) {
    // Set callback url for BV auntheticated user.
    params += `&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
  }
  if (getCurrentUserEmail() !== null && getSessionCookie() !== undefined) {
    // Set UAS token in user param.
    params += `&user=${getSessionCookie()}`;
  }
  // Set product id
  params += `&productid=${bazaarVoiceSettings.productid}`;
  // Add device finger printing string in params.
  if (elements.blackBox.value !== '') {
    params += `&fp=${elements.blackBox.value}`;
  }
  // Terms and conditions must be agreed.
  params += `&agreedtotermsandconditions=${true}`;
  // Set action type.
  params += '&action=submit';

  return params;
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
