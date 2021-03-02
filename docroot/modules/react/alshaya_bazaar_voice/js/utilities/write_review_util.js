import { getCurrentUserEmail, getSessionCookie } from './user_util';

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
    // Add text input data in request.
    if (field['#type'] === 'textfield'
      || field['#type'] === 'textarea') {
      const value = (elements[id].value === undefined)
        ? ''
        : elements[id].value;

      if (value !== null && value !== '') {
        params += `&${id}=${value}`;

        if (id === 'useremail' && getCurrentUserEmail() === undefined) {
          params += `&HostedAuthentication_AuthenticationEmail=${value}`;
        }
      }
    }
    // Add select input data in request.
    if (field['#type'] === 'select') {
      const value = (elements[id].value === undefined)
        ? ''
        : elements[id].value;

      if (value !== null && value !== '') {
        params += `&${id}=${value}`;
      }
    }
    // Add tags type data in request
    if (field['#type'] === 'checkbox') {
      const value = (elements[id].value === undefined)
        ? ''
        : elements[id].value;

      if (value !== null && value !== '') {
        params += `&${id}=${value}`;
      }
    }

    // Add tags type data in request
    if (field['#group_type'] === 'photo') {
      [...Array(5)].map((val, i) => {
        let photoUrl = '';
        try {
          const photoId = `photourl_${(i + 1)}`;
          photoUrl = `&${photoId}=${elements[photoId].value}`;
        } catch (e) {
          Drupal.logJavascriptError('photo-url-exception', e.message);
        }
        if (photoUrl !== '') {
          params += photoUrl;
        }
        return photoUrl;
      });
    }
  });

  // Set product id
  params += `&productid=${drupalSettings.bazaar_voice.productid}`;
  // Set action type.
  params += '&action=submit';

  if (getCurrentUserEmail() === undefined) {
    // Set callback url for BV auntheticated user.
    params += `&HostedAuthentication_CallbackURL=${drupalSettings.product.url}`;
  }

  if (getCurrentUserEmail() !== undefined && getSessionCookie() !== undefined) {
    // Set UAS token in user param.
    params += `&user=${getSessionCookie()}`;
  }

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
