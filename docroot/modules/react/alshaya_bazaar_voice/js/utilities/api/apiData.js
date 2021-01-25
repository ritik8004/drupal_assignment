import Axios from 'axios';

function getBvUrl() {
  return drupalSettings.alshaya_bazaar_voice.endpoint;
}

function getApiVersion() {
  return `apiversion=${drupalSettings.alshaya_bazaar_voice.api_version}`;
}

function getPassKey() {
  return `&passkey=${drupalSettings.alshaya_bazaar_voice.passkey}`;
}

function getLocale() {
  return `&locale=${drupalSettings.alshaya_bazaar_voice.locale}`;
}

export default function fetchAPIData(apiUri, params) {
  const url = `${getBvUrl() + apiUri}?${getApiVersion()}${getPassKey()}${getLocale()}${params}`;

  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
}
