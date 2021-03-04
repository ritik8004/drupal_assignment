import Axios from 'axios';
import { getbazaarVoiceSettings } from './request';

const bazaarVoiceSettings = getbazaarVoiceSettings();

function getBvUrl() {
  return bazaarVoiceSettings.reviews.bazaar_voice.endpoint;
}

function getApiVersion() {
  return `apiversion=${bazaarVoiceSettings.reviews.bazaar_voice.api_version}`;
}

function getPassKey() {
  return `&passkey=${bazaarVoiceSettings.reviews.bazaar_voice.passkey}`;
}

function getLocale() {
  return `&locale=${bazaarVoiceSettings.reviews.bazaar_voice.locale}`;
}

function getContentLocale() {
  return `&contentlocale=${bazaarVoiceSettings.reviews.bazaar_voice.content_locale}`;
}

export function fetchAPIData(apiUri, params) {
  const url = `${getBvUrl() + apiUri}?${getApiVersion()}${getPassKey()}${getLocale()}${getContentLocale()}${params}`;

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

export function postAPIData(apiUri, params) {
  const url = `${getBvUrl() + apiUri}?${getApiVersion()}${getPassKey()}${getLocale()}${params}`;

  return Axios.post(url)
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

export default {
  fetchAPIData,
  postAPIData,
};
