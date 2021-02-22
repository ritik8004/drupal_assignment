import Axios from 'axios';

function getBvUrl() {
  return drupalSettings.bazaar_voice.endpoint;
}

function getApiVersion() {
  return `apiversion=${drupalSettings.bazaar_voice.api_version}`;
}

function getPassKey() {
  return `&passkey=${drupalSettings.bazaar_voice.passkey}`;
}

function getLocale() {
  return `&locale=${drupalSettings.bazaar_voice.locale}`;
}

export function fetchAPIData(apiUri, params) {
  const url = `${getBvUrl() + apiUri}?${getApiVersion()}${getPassKey()}${getLocale()}${params}`;
  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
}

export function postAPIData(apiUri, params) {
  const data = {};
  const url = `${getBvUrl() + apiUri}?${getApiVersion()}${getPassKey()}${getLocale()}${params}`;
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
