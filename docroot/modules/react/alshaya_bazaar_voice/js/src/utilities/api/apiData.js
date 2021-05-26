import Axios from 'axios';
import { getbazaarVoiceSettings, getUserBazaarVoiceSettings } from './request';

function getBvUrl(bazaarVoiceSettings) {
  return bazaarVoiceSettings.reviews.bazaar_voice.endpoint;
}

function getApiVersion(bazaarVoiceSettings) {
  return `apiversion=${bazaarVoiceSettings.reviews.bazaar_voice.api_version}`;
}

function getPassKey(bazaarVoiceSettings) {
  return `&passkey=${bazaarVoiceSettings.reviews.bazaar_voice.passkey}`;
}

function getLocale(bazaarVoiceSettings) {
  return `&locale=${bazaarVoiceSettings.reviews.bazaar_voice.locale}`;
}

export function fetchAPIData(apiUri, params, context = '') {
  let bazaarVoiceSettings = null;
  if (context === 'user') {
    bazaarVoiceSettings = getUserBazaarVoiceSettings();
  } else {
    bazaarVoiceSettings = getbazaarVoiceSettings();
  }
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(bazaarVoiceSettings)}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}${params}`;

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

export function postAPIData(apiUri, params, productId = undefined) {
  const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(bazaarVoiceSettings)}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}${params}`;

  return Axios.post(url, params, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  })
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
