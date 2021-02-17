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
 */
export const prepareRequest = (elements, formFieldMeta) => {
  let params = '';

  Object.entries(formFieldMeta).forEach(([field]) => {
    const id = field['#id'];

    // Handle text input data.
    if (field['#type'] === 'textfield'
      || field['#type'] === 'textarea') {
      const value = (typeof elements[id].value === 'undefined')
        ? ''
        : elements[id].value;

      if (value !== null && value !== '') {
        params += `&${id}=${value}`;

        if (id === 'useremail') {
          params += `&HostedAuthentication_AuthenticationEmail=${value}`;
        }
      }
    }

    // Handle select input data.
    if (field['#type'] === 'select') {
      const value = (typeof elements[id].value === 'undefined')
        ? ''
        : elements[id].value;

      if (value !== null && value !== '') {
        params += `&${id}=${value}`;
      }
    }
  });

  // Set product id
  params += `&productid=${drupalSettings.bazaar_voice.productid}`;
  // Set action type.
  params += '&action=submit';
  // Set callback url BV auntheticated user.
  params += `&HostedAuthentication_CallbackURL=${drupalSettings.bazaar_voice.base_url}/auth-bv-user`;
  params += '&agreedtotermsandconditions=1';

  return params;
};

/**
 * Validate write review form information.
 */
export const validateWriteReviewFormInfo = (elements, formFieldMeta) => {
  let isError = false;

  Object.entries(formFieldMeta).forEach(([field]) => {
    if (field['#visible'] === true
       && (field['#type'] === 'textfield'
       || field['#type'] === 'textarea')) {
      const value = (elements[field['#id']].value !== undefined)
        ? elements[field['#id']].value.trim()
        : '';

      if (value.length < field['#minlength'] && value !== '') {
        isError = true;
      }

      if (field === 'useremail') {
        if (!validEmailRegex.test(value)) {
          isError = true;
        }
      }
    }
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
