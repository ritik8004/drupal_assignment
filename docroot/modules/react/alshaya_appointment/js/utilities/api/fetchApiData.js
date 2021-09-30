import Axios from 'axios';
import { removeFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';

function getMiddleWareUrl() {
  return drupalSettings.alshaya_appointment.middleware_url;
}

function getLanguageCode() {
  return drupalSettings.path.currentLanguage;
}

function fetchAPIData(apiUrl) {
  // Pass language code as parameter.
  // Check if endpoint already has parameter, then append langcode.
  const langcode = (apiUrl.indexOf('?') !== -1)
    ? `&langcode=${getLanguageCode()}`
    : `?langcode=${getLanguageCode()}`;
  const url = getMiddleWareUrl() + apiUrl + langcode;

  return Axios.get(url)
    .then((response) => {
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: response,
        },
      });
      document.dispatchEvent(event);
      return response;
    })
    .catch((error) => {
      removeFullScreenLoader();
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: error,
        },
      });
      document.dispatchEvent(event);
      return error;
    });
}

function postAPICall(apiUrl, data) {
  const url = getMiddleWareUrl() + apiUrl;

  return Axios.post(url, data)
    .then((response) => {
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: response,
        },
      });
      document.dispatchEvent(event);
      return response;
    })
    .catch((error) => {
      removeFullScreenLoader();
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: error,
        },
      });
      document.dispatchEvent(event);
      return error;
    });
}

export {
  fetchAPIData,
  postAPICall,
};
