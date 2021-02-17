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
  const url = `${getBvUrl() + apiUri}?${getApiVersion()}${getPassKey()}${getLocale()}${params}`;
  return Axios.post(url)
    .then((response) => response)
    .catch((error) => error);
}

export default {
  fetchAPIData,
  postAPIData,
};
