import axios from 'axios';
import { getbazaarVoiceSettings, getUserBazaarVoiceSettings } from './request';
import dispatchCustomEvent from '../../../../../js/utilities/events';

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

  return axios.get(url)
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export function postAPIData(apiUri, params, productId = undefined) {
  const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(bazaarVoiceSettings)}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}`;

  return axios.post(url, params, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  })
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export function postAPIPhoto(apiUri, params) {
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  const url = `${getBvUrl(bazaarVoiceSettings) + apiUri}?${getApiVersion(bazaarVoiceSettings)}${getPassKey(bazaarVoiceSettings)}${getLocale(bazaarVoiceSettings)}${params}`;

  return axios.post(url)
    .then((response) => {
      dispatchCustomEvent('showMessage', { data: response });
      return response;
    })
    .catch((error) => {
      dispatchCustomEvent('showMessage', { data: error });
      return error;
    });
}

export default {
  fetchAPIData,
  postAPIData,
  postAPIPhoto,
};
